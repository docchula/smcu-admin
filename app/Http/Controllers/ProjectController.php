<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\User;
use Carbon\Carbon;
use Crypt;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use IntlDateFormatter;
use OpenPsa\Ranger\Ranger;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
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
            'list' => Project::searchQuery($keyword)->orderByDesc('year')->orderByDesc('number')->with([
                'documents' => function ($query) {
                    $query->select('id', 'tag', 'project_id');
                    $query->whereNotNull('tag');
                },
            ])->paginate(15)->withQueryString(),
            'keyword' => $keyword,
            'is_admin' => $request->user()->can('admin-action'),
        ]);
    }

    public function indexOfYear(Request $request): Response {
        $keyword = $request->input('search');

        return Inertia::render('ProjectYearIndex', [
            'list' => Project::searchQuery($keyword)->addSelect(['advisor'])->with([
                'documents' => function ($query) {
                    $query->select('id', 'year', 'number', 'number_to', 'title', 'tag', 'project_id');
                    $query->whereNotNull('tag');
                }
            ])->withCount('participants')->orderBy('department_id')->orderByDesc('id')->limit(500)->get()->groupBy('department_id'),
            'keyword' => $keyword,
            'static_departments' => Department::optionList(),
        ]);
    }

    public function indexBudget(Request $request): Response {
        $this->authorize('admin-action');
        $keyword = $request->input('search', Helper::termYear());

        return Inertia::render('ProjectYearBudget', [
            'list' => Project::searchQuery($keyword)->addSelect(['expense'])
                ->withCount('participants')
                ->orderBy('department_id')->orderByDesc('id')
                ->limit(500)->get()->groupBy('department_id'),
            'keyword' => $keyword,
            'static_departments' => Department::optionList(),
        ]);
    }

    public function indexAgenda(): Response
    {
        return Inertia::render('ProjectAgenda', [
            'list' => Project::query()
                ->select('id', 'year', 'number', 'name', 'department_id', 'created_at', 'period_start', 'period_end')
                ->with(['department'])
                ->with([
                    'documents' => function ($query) {
                        $query->select('id', 'tag', 'project_id');
                        $query->whereNotNull('tag');
                    },
                ])
                ->withCount('participants')
                ->orderBy('period_start')
                ->orderBy('period_end')
                ->whereBetween('period_start', [now()->subMonths(3), now()->addYear()])
                ->orWhereBetween('period_end', [now()->subMonths(3), now()->addYear()])
                ->get(),
        ]);
    }

    public function search(string $keyword): JsonResponse {
        return response()->json(Project::searchQuery($keyword, ['objectives', 'expense'])->take(5)->get());
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
        $project->shouldBeClosed = ($canUpdateProject and $project->created_at->isBetween(now()->subYear(),
            now()->subWeeks(3)) and $project->documents->isNotEmpty() and $project->documents->where('tag', 'summary')->isEmpty() and !in_array($project->department_id, [32, 38, 39]));

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
            'item' => $project->castDateAsDateString(),
            'static_departments' => Department::optionList(),
            'static_advisors' => Project::advisorList(),
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
            'duration' => 'required|filled|numeric|max:999|min:1',
            'estimated_attendees' => 'nullable|string|max:40',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'department_id' => 'required|integer|min:1',
            'background' => 'required|string',
            'aims' => 'required|string',
            'outcomes' => 'nullable|string',
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
            $project->year = $request->input('year', Helper::buddhistYear());
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
            'department' => in_array($project->department->sequence, [2, 3, 4]) ? $project->department->name : 'สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย',
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
            'signer_s2_title' => 'ประธานฝ่าย/ประธานนิสิตแพทย์ชั้นปีที่.....',
            'signer_s3_title' => 'อุปนายกสโมสรฝ่าย .....',
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

    /**
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function generateSummaryDocument(Request $request, Project $project) {
        $project->load('department', 'participants', 'participants.user');
        $ranger = (new Ranger('th'))->setDateType(IntlDateFormatter::LONG);
        $template = new TemplateProcessor(storage_path('project_summary_template.docx'));
        $template->setValues([
            'doc_number' => '...../' . Helper::buddhistYear(),
            'date' => Carbon::now()->locale('th')->isoFormat('D MMMM ') . Helper::buddhistYear(),
            'name' => $project->name,
            'number' => $project->getNumber(),
            'department' => in_array($project->department->sequence, [2, 3, 4]) ? $project->department->name : 'สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย',
            'period' => (($project->period_start == $project->period_end) ? 'ในวันที่ ' : 'ระหว่างวันที่ ') . $ranger->format($project->period_start, $project->period_end),
            'is_budget_required_txt' => (array_filter($project->expense, function ($e) {
                return in_array($e['source'], ['ฝ่ายกิจการนิสิต', 'กองทุนอื่นของคณะ']);
            })) ? 'พร้อมงบประมาณสนับสนุน' : '',
            'contact_name' => $request->user()->name,
            'contact_phone' => '.............',
            'signer_advisor_name' => $project->advisor,
        ]);
        if ($organizer = $project->participants->where('type', 'organizer')->first()) {
            $template->setValues([
                'signer_s1_name' => $organizer->user->name,
            ]);
        }
        $template->cloneRowAndSetValues('organizers_number', $project->participants->where('type', 'organizer')->map(fn(ProjectParticipant $participant, int $i) => [
            'organizers_number' => $i + 1,
            'organizers_name' => $participant->user->name,
            'organizers_id' => $participant->user->student_id
        ]));
        $template->cloneRowAndSetValues('exp_name', $project->expense ? array_map(function (array $ex) {
            return [
                'exp_name' => $ex['name'] ?? '',
                'exp_type' => $ex['type'] ?? '',
                'exp_source' => $ex['source'] ?? '',
                'exp_amount' => number_format($ex['amount'] ?? 0, 2),
                'exp_paid' => number_format($ex['paid'] ?? 0, 2),
            ];
        }, $project->expense) : [
            [
                'exp_name' => 'ไม่ได้ใช้งบประมาณ',
                'exp_type' => '',
                'exp_source' => '',
                'exp_amount' => '',
                'exp_paid' => '',
            ],
        ]);
        // Set total expense
        $template->setValues([
            'exp_t_amnt' => number_format(array_reduce($project->expense, function ($carry, $item) {
                return $carry + ($item['amount'] ?? 0);
            }, 0), 2),
            'exp_t_paid' => number_format(array_reduce($project->expense, function ($carry, $item) {
                return $carry + ($item['paid'] ?? 0);
            }, 0), 2),
        ]);
        $aims = explode("\n", $project->aims);
        $objectiveCount = max(count($aims), count($project->objectives));
        $objectiveSum = [];
        for ($i = 0; $i < $objectiveCount; $i++) {
            $objectiveSum []= [
                'objectives_goal' => $project->objectives[$i]['goal'] ?? '...',
                'objectives_result' => isset($project->objectives[$i]['result']) ? ($project->objectives[$i]['result'].' ('.$project->objectives[$i]['percentage'].'%)') : '...',
                'aims_text' => $aims[$i] ?? '...',
            ];
        }
        $template->cloneRowAndSetValues('objectives_goal', $objectiveSum);

        // Calculate average percentage
        $totalPercentage = $countPercentage = 0;
        foreach ($project->objectives as $objective) {
            if (isset($objective['percentage'])) {
                $totalPercentage += $objective['percentage'];
                $countPercentage++;
            }
        }
        $template->setValues([
            'total_percent' => $countPercentage ? number_format($totalPercentage / $countPercentage, 2) : '...',
        ]);

        // Set participant list
        $template->cloneRowAndSetValues('ptcp_no', $project->participants->map(fn(ProjectParticipant $participant, int $i) => [
            'ptcp_no' => $i + 1,
            'ptcp_name' => $participant->user->name,
            'ptcp_id' => $participant->user->student_id,
            'ptcp_type' => ProjectParticipant::TYPES_OPTIONS[$participant->type] ?? $participant->type,
            'ptcp_title' => $participant->title ?? '-',
        ]));

        $tmpPath = tempnam(storage_path(), 'tmp-projectsummary-');
        $template->saveAs($tmpPath);

        return response()->download($tmpPath, $project->getNumber() . ' Project Summary.docx')->deleteFileAfterSend();
    }

    public function searchNewParticipant(Request $request) {
        $this->validate($request, [
            'q' => 'required|string|min:7',
        ]);
        $q = $request->input('q');
        $studentQuery = User::query();
        if (is_numeric($q) and strlen($q) == 10) {
            $studentQuery = $studentQuery->where('student_id', $q);
        } elseif (is_numeric($q) and strlen($q) >= 7 and strlen($q) <= 10) {
            $studentQuery = $studentQuery->where('student_id', 'LIKE', $q.'%');
        } else {
            if (!str_contains($q, '@')) {
                $q = $q.'@docchula.com';
            }
            $studentQuery = $studentQuery->where('email', $q.'@docchula.com');
        }
        if (!$student = $studentQuery->first()) {
            $vestaResponse = VestaClient::retrieveStudent($q, $request->user()->email, ['student_id', 'title', 'first_name', 'last_name', 'nickname', 'email']);
            if ($vestaResponse->successful()) {
                $data = $vestaResponse->json();
                $student = User::updateOrCreate([
                    'email' => $data['email'],
                ], [
                    'name' => ($data['title'] ?? '').$data['first_name'].' '.$data['last_name'],
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
                    $student = User::updateOrCreate([
                        'email' => $data['email'],
                    ], [
                        'name' => ($data['title'] ?? '').$data['first_name'].' '.$data['last_name'],
                        'student_id' => $data['student_id'],
                    ]);
                } else {
                    $messages [] = 'WARNING: ' . $row['student_id'] . ' ไม่พบนิสิต';
                    break;
                }
            }
            if ($existingParticipant = $project->participants->where('user_id', $student->id)->first()) {
                if (ProjectParticipant::TYPES_RANK[$existingParticipant->type] < ProjectParticipant::TYPES_RANK[$row['type']]) {
                    $messages [] = 'WARNING: '.$row['student_id'].' มีอยู่แล้วและมีตำแหน่งที่สูงกว่า ไม่บันทึกข้อมูลใหม่';
                }
                if ($existingParticipant->type != $row['type'] or $existingParticipant->title != $row['title']) {
                    $toAdd->add([
                        'user_id' => $student->id,
                        'user_name' => $student->name,
                        'type' => $row['type'],
                        'title' => $row['title'] ?? NULL,
                        'existing' => true,
                    ]);
                    $messages [] = 'WARNING: ' . $row['student_id'] . ' มีอยู่แล้วแต่ข้อมูลไม่ตรงกัน ระบบจะบันทึกข้อมูลใหม่';
                } else {
                    $messages [] = 'WARNING: ' . $row['student_id'] . ' มีอยู่แล้ว';
                }
            } else {
                $toAdd->add([
                    'user_id' => $student->id,
                    'user_name' => $student->name,
                    'type' => $row['type'],
                    'title' => $row['title'] ?? NULL,
                    'existing' => false,
                ]);
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
            $messages [] = 'ERROR: ไม่มีข้อมูลที่สามารถบันทึกได้';

            return response()->json([
                'status' => 'fail',
                'data' => [
                    'preview' => NULL,
                    'import' => NULL,
                    'messages' => $messages,
                ],
            ]);
        }
    }

    public function importParticipantCommit(Request $request, Project $project) {
        $this->authorize('update-project', $project);
        try {
            $toAdd = Crypt::decrypt($request->input('import'));
        } catch (DecryptException) {
            return back()->with('flash.banner', 'Unable to parse participant data.')->with('flash.bannerStyle', 'danger');
        }
        foreach ($toAdd as $item) {
            $project->participants()->updateOrCreate(['user_id' => $item['user_id']], [
                'type' => $item['type'],
                'title' => $item['title'] ?? NULL,
            ]);
        }

        return back()->with('flash.banner', 'เพิ่ม/แก้ไขนิสิตผู้เกี่ยวข้อง ' . count($toAdd) . ' คนแล้ว')->with('flash.bannerStyle', 'success');
    }

    /**
     * @throws AuthorizationException
     * @throws Exception
     */
    public function exportParticipant(Project $project) {
        $this->authorize('update-project', $project);
        $participants = $project->participants()->with('user')->get()->map(function (ProjectParticipant $participant, int $i) {
            return [
                $i + 1,
                $participant->user->name,
                ProjectParticipant::TYPES_OPTIONS[$participant->type] ?? $participant->type,
                $participant->title,
            ];
        });

        $spreadsheet = IOFactory::load(storage_path('export_participant_template.xlsx'));
        $worksheet = $spreadsheet->getActiveSheet();
        $worksheet->setCellValue('A1', 'รายชื่อนิสิตผู้เกี่ยวข้อง โครงการ' . $project->name);
        $worksheet->setCellValue('A2', $project->period_start->format('j F Y') . ' - ' . $project->period_end->format('j F Y'));
        $worksheet->fromArray($participants->toArray(), NULL, 'A4');
        $tmpPath = tempnam(storage_path(), 'tmp-participant-');
        IOFactory::createWriter($spreadsheet, 'Xlsx')->save($tmpPath);

        return response()->download($tmpPath, $project->getNumber() . ' Project Participants.xlsx')->deleteFileAfterSend(true);
    }

    public function advisorList()
    {
        return response()->json(Project::advisorList());
    }
}
