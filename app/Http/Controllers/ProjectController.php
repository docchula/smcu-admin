<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\User;
use Carbon\Carbon;
use Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use IntlDateFormatter;
use OpenPsa\Ranger\Ranger;
use PhpOffice\PhpWord\Exception\CopyFileException;
use PhpOffice\PhpWord\Exception\CreateTemporaryFileException;
use PhpOffice\PhpWord\TemplateProcessor;
use Spatie\SimpleExcel\SimpleExcelReader;
use Throwable;
use VestaClient;

class ProjectController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response {
        $keyword = $request->input('search');

        return Inertia::render('ProjectIndex', [
            'list' => Project::searchQuery($keyword)->orderByDesc('year')->orderByDesc('number')->paginate(15)->withQueryString(),
            'keyword' => $keyword
        ]);
    }

    public function search(string $keyword): \Illuminate\Http\JsonResponse {
        return response()->json(Project::searchQuery($keyword)->take(5)->get());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response {
        return $this->edit($request, new Project([]));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $this->validate($request, ['name' => 'required|unique:projects,name']);

        return $this->update($request, new Project());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project) {
        $canUpdateProject = $request->user()->can('update-project', $project);
        $project->can = [
            'update-project' => $canUpdateProject
        ];
        $project->load(['user', 'department', 'documents', 'participants', 'participants.user']);
        $project->user->makeHidden('id', 'student_id', 'profile_photo_url');
        $project->participants->transform(function (ProjectParticipant $participant) use ($canUpdateProject) {
            $participant->user->makeHidden('id', 'profile_photo_url');
            if (!$canUpdateProject) {
                $participant->user->makeHidden('student_id');
            }

            return $participant;
        });

        return Inertia::render('ProjectShow', [
            'item' => $project
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Project $project): Response {
        $this->authorize('update-project', $project);
        /** @var User $user */
        $user = $request->user();
        $project->organizers = $project->participants()->with('user')->where('type', 'organizer')->get()->map(function (ProjectParticipant $p) {
            return ['name' => $p->user->name, 'student_id' => $p->user->student_id];
        });

        return Inertia::render('ProjectCreate', [
            'item' => $project,
            'static_departments' => Department::optionList(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     * @throws Throwable
     */
    public function update(Request $request, Project $project) {
        $this->validate($request, [
            'name' => 'required|filled|string|min:5|max:255',
            'advisor' => 'required|filled|string|min:5|max:255',
            'type' => 'required|filled|string|max:20',
            'recurrence' => 'required|filled|string|max:20',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'department_id' => 'required|integer|min:1',
            'background' => 'required|string',
            'aims' => 'required|string',
            'outcomes' => 'required|string',
            'objectives' => 'required|array',
            'expense' => 'nullable|array',
            'expense.*.name' => 'required|string',
            'expense.*.type' => 'required|string',
            'expense.*.source' => 'required|string',
            'expense.*.amount' => 'required|numeric',
            'organizers' => 'required|array',
            'staff' => 'nullable|array',
            'attendees' => 'nullable|array',
        ]);
        $this->authorize('update-project', $project);
        $project->fill($request->all());
        if (empty($project->user_id)) {
            $project->user_id = Auth::id();
        }
        if ($project->period_start->getTimezone()->toOffsetName() == '+00:00') {
            $project->period_start = $project->period_start->setTimezone('Asia/Bangkok');
            $project->period_end = $project->period_end->setTimezone('Asia/Bangkok');
        }
        if ($project->period_end < $project->period_start) {
            $project->fill(['period_start' => $project->period_end, 'period_end' => $project->period_start]);
        }
        if (!$project->id) {
            $project->year = Helper::buddhistYear();
            $previousRecord = Project::latestOfYear($project->year);
            $project->number = $previousRecord ? ($previousRecord->number + 1) : 1;
        }
        $existingParticipants = $project->participants;
        $project->saveOrFail();
        foreach (['organizers' => 'organizer', 'staff' => 'staff', 'attendees' => 'attendee'] as $roleField => $role) {
            if ($request->filled($roleField)) {
                // Note: these lines of code suffers from n+1 performance issue
                $inputParticipants = new Collection($request->input($roleField, []));
                $newParticipants = new Collection();
                $users = User::whereIn('student_id', [...$inputParticipants->pluck('student_id'), ...$existingParticipants->pluck('student_id')])->get();
                foreach ($inputParticipants as $student) {
                    // Add / edit existing
                    if (!empty($student['student_id'])) {
                        $user = $users->where('student_id', $student['student_id'])->first(); /* ?? User::firstOrCreate(['email' => $student['email']], [
                            'name' => ($student['title'] ?? '') . $student['first_name'] . ' ' . $student['last_name'],
                            'student_id' => $student['student_id'],
                        ]); */
                        if ($participant = $existingParticipants->where('user_id', $user->id)->first()) {
                            // Existing
                            if ($participant->type != $role) {
                                $participant->type = $role;
                                $participant->save();
                            }
                        } else {
                            $newParticipants->push(['user_id' => $user->id, 'type' => $role]);
                        }
                    }
                }
                if ($newParticipants->isNotEmpty()) {
                    $project->participants()->createMany($newParticipants);
                }
                // Delete unused existing
                $existingParticipants->whereNotIn('user_id', $users->whereIn('student_id', $inputParticipants->pluck('student_id'))->pluck('id'))->where('type', $role)->each->delete();
            } elseif ($existingParticipants->isNotEmpty()) {
                $project->participants()->where('type', $role)->delete();
            }
        }

        return redirect()->route('projects.index')->with('flash.banner', 'บันทึกโครงการ เลขที่ ' . $project->year . '-' . $project->number . ' แล้ว')->with('flash.bannerStyle', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {
        //
    }

    /**
     * @throws CopyFileException
     * @throws CreateTemporaryFileException
     */
    public function generateApprovalDocument(Request $request, Project $project) {
        $project->load('department', 'participants', 'participants.user');
        $ranger = (new Ranger('th'))->setDateType(IntlDateFormatter::LONG);
        $template = new TemplateProcessor(storage_path('project_approval_template.docx'));
        $template->setValues([
            'doc_number' => '...../' . Helper::buddhistYear(),
            'date' => Carbon::now()->locale('th')->isoFormat('D MMMM ') . Helper::buddhistYear(),
            'name' => $project->name,
            'number' => $project->getNumber(),
            'department' => ($project->department_id == 33) ? 'สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย' : $project->department->name,
            'period' => (($project->period_start == $project->period_end) ? 'ในวันที่ ' : 'ระหว่างวันที่ ') . $ranger->format($project->period_start, $project->period_end),
            'aims' => call_user_func(function ($text) {
                $aims = explode("\n", $text);
                $aims[count($aims) - 1] = 'และ' . $aims[count($aims) - 1];

                return implode(' ', $aims);
            }, $project->aims),
            'is_budget_required_txt' => (array_filter($project->expense, function ($e) {
                return in_array($e['source'], ['ฝ่ายกิจการนิสิต', 'กองทุนอื่นของคณะ']);
            })) ? 'พร้อมงบประมาณสนับสนุน' : '',
            'contact_name' => $request->user()->name,
            'contact_phone' => '.............',
            'signer_advisor_name' => $project->advisor,
            'signer_s2_title' => 'หัวหน้าฝ่าย/ประธานนิสิตแพทย์ชั้นปีที่.....',
            'signer_s3_title' => 'อุปนายกสโมสรนิสิต คนที่ .....',
            'signer_s4_title' => "นายกสโมสรนิสิต\nคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย",
            'background' => $project->background
        ]);
        if ($organizer = $project->participants->where('type', 'organizer')->first()) {
            $template->setValues([
                'signer_s1_name' => '(' . $organizer->user->name . ')',
                'signer_s1_title' => 'นิสิตผู้รับผิดชอบโครงการ'
            ]);
        }
        $template->cloneRowAndSetValues('organizers_number', $project->participants->where('type', 'organizer')->map(fn(ProjectParticipant $participant, int $i) => [
            'organizers_number' => $i + 1,
            'organizers_name' => $participant->user->name,
            'organizers_id' => $participant->user->student_id
        ]));
        $template->cloneRowAndSetValues('expense_name', array_map(function (array $ex) {
            return ['expense_name' => $ex['name'] ?? '', 'expense_type' => $ex['type'] ?? '', 'expense_source' => $ex['source'] ?? '', 'expense_amount' => number_format($ex['amount'] ?? 0, 2)];
        }, $project->expense));
        $template->cloneRowAndSetValues('objectives_goal', array_map(function (array $o) {
            return ['objectives_goal' => $o['goal'], 'objectives_method' => $o['method']];
        }, $project->objectives));
        $template->cloneRowAndSetValues('aims_number', call_user_func(function ($text) {
            $aims = explode("\n", $text);
            $return = [];
            foreach ($aims as $i => $aim) {
                $return[] = ['aims_number' => $i + 1, 'aims_text' => $aim];
            }

            return $return;
        }, $project->aims));
        $template->cloneRowAndSetValues('outcomes_number', call_user_func(function ($text) {
            $outcomes = explode("\n", $text);
            $return = [];
            foreach ($outcomes as $i => $outcome) {
                $return[] = ['outcomes_number' => $i + 1, 'outcomes_text' => $outcome];
            }

            return $return;
        }, $project->outcomes));

        $tmpPath = tempnam(storage_path(), 'tmp-projectapproval-');
        $template->saveAs($tmpPath);

        return response()->download($tmpPath, $project->getNumber() . ' Project Approval.docx')->deleteFileAfterSend(true);
    }

    public function searchNewParticipant(Request $request) {
        $this->validate($request, [
            'q' => 'required|string|min:10'
        ]);
        $q = $request->input('q');
        if (!$student = User::where('email', $q)->orWhere('student_id', $q)->first()) {
            $vestaResponse = VestaClient::retrieveStudent($q, $request->user()->email, ['student_id', 'title', 'first_name', 'last_name', 'nickname', 'email']);
            if ($vestaResponse->successful()) {
                $data = $vestaResponse->json();
                $student = User::firstOrCreate([
                    'email' => $data['email'],
                ], [
                    'name' => ($data['title'] ?? '') . $data['first_name'] . ' ' . $data['last_name'],
                    'student_id' => $data['student_id'],
                ]);
                $student->nickname = $data['nickname'];
            } else {
                return response()->json(['error' => 'ไม่พบนิสิตที่ค้นหา'], 404);
            }
        }

        return response()->json($student->only(['name', 'student_id', 'nickname']));
    }

    public function addParticipant(Request $request, Project $project) {
        $this->validate($request, [
            'student_ids' => 'required|array',
            'student_ids.*' => 'numeric|digits:10',
            'type' => 'required|string|in:organizer,staff,attendee'
        ]);
        $this->authorize('update-project', $project);
        $toAdd = [];
        foreach ($request->input('student_ids') as $studentId) {
            if (!$user = User::where('student_id', $studentId)->first()) {
                return back()->with('flash.banner', 'ไม่สามารถเพิ่มนิสิตผู้เกี่ยวข้องได้ : ไม่พบเลขประจำตัวนิสิต ' . $studentId)->with('flash.bannerStyle', 'danger');
            } elseif ($project->participants->where('user_id', $user->id)->isEmpty()) {
                $toAdd [] = [
                    'user_id' => $user->id,
                    'type' => $request->input('type')
                ];
            }
        }
        if (count($toAdd) > 0) {
            $project->participants()->createMany($toAdd);

            return back()->with('flash.banner', 'เพิ่มนิสิตผู้เกี่ยวข้อง ' . count($toAdd) . ' คนแล้ว')->with('flash.bannerStyle', 'success');
        } else {
            return back()->with('flash.banner', 'ไม่มีการเพิ่มนิสิตผู้เกี่ยวข้อง')->with('flash.bannerStyle', 'danger');
        }
    }

    public function removeParticipant(ProjectParticipant $participant) {
        $participant->load(['project', 'user']);
        $this->authorize('update-project', $participant->project);
        $participant->delete();

        return back()->with('flash.banner', 'ลบ ' . $participant->user->name . ' แล้ว')->with('flash.bannerStyle', 'success');
    }

    public function importParticipantUpload(Request $request, Project $project) {
        $this->validate($request, [
            'import' => 'required|file|mimes:csv,xlsx,xls'
        ]);
        $this->authorize('update-project', $project);
        $uploadedFile = $request->file('import');
        $import = SimpleExcelReader::create($uploadedFile->path(), $uploadedFile->clientExtension())->getRows();
        $toAdd = collect([]);
        $messages = [];
        foreach ($import as $row) {
            if (empty($row['student_id']) or strlen($row['student_id']) < 10) {
                $messages [] = 'ERROR: student_id ไม่ถูกต้อง';
                break;
            }
            if (empty($row['type']) or !in_array($row['type'], ['organizer', 'staff', 'attendee'])) {
                $messages [] = 'ERROR: type ไม่ถูกต้อง';
                break;
            }
            if (!$student = User::where('email', $row['student_id'])->orWhere('student_id', $row['student_id'])->first()) {
                $vestaResponse = VestaClient::retrieveStudent($row['student_id'], $request->user()->email, ['student_id', 'title', 'first_name', 'last_name', 'nickname', 'email']);
                if ($vestaResponse->successful()) {
                    $data = $vestaResponse->json();
                    $student = User::firstOrCreate([
                        'email' => $data['email'],
                    ], [
                        'name' => ($data['title'] ?? '') . $data['first_name'] . ' ' . $data['last_name'],
                        'student_id' => $data['student_id'],
                    ]);
                } else {
                    $messages [] = 'WARNING: ' . $row['student_id'] . ' ไม่พบนิสิต';
                    break;
                }
            }
            if (isset($student) and $project->participants->where('user_id', $student->id)->isEmpty()) {
                $toAdd->add([
                    'user_id' => $student->id,
                    'user_name' => $student->name,
                    'type' => $row['type'],
                    'title' => $row['title'] ?? NULL,
                ]);
            } else {
                $messages [] = 'WARNING: ' . $row['student_id'] . ' มีอยู่แล้ว';
            }
        }
        if (count($toAdd) > 0) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'preview' => $toAdd->map(function ($item) {
                        unset($item['user_id']);
                        return $item;
                    }),
                    'import' => Crypt::encrypt($toAdd->toArray()),
                    'messages' => $messages,
                ],
            ]);
        } else {
            $messages []= 'ERROR: ไม่มีข้อมูลที่สามารถบันทึกได้';
            return response()->json([
                'status' => 'fail',
                'data' => [
                    'preview' => null,
                    'import' => null,
                    'messages' => $messages,
                ],
            ]);
        }
    }

    public function importParticipantCommit(Request $request, Project $project) {
        try {
            $toAdd = Crypt::decrypt($request->input('import'));
        } catch (DecryptException) {
            return back()->with('flash.banner', 'Unable to parse participant data.')->with('flash.bannerStyle', 'danger');
        }
        $project->participants()->createMany(collect($toAdd)->map(function ($item) {
            return [
                'user_id' => $item['user_id'],
                'type' => $item['type'],
                'title' => $item['title'] ?? null,
            ];
        }));

        return back()->with('flash.banner', 'เพิ่มนิสิตผู้เกี่ยวข้อง ' . count($toAdd) . ' คนแล้ว')->with('flash.bannerStyle', 'success');
    }
}
