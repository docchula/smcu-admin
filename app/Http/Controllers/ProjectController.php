<?php

namespace App\Http\Controllers;

use App\Helper;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\Models\User;
use App\VestaService;
use Carbon\Carbon;
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
use Throwable;

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
            $names = explode(' ', $p->user->name, 2);
            return ['first_name' => $names[0], 'last_name' => $names[1], 'student_id' => $p->user->student_id];
        });

        return Inertia::render('ProjectCreate', [
            'item' => $project,
            'static_departments' => Department::optionList(),
            'vesta_token' => VestaService::generateProxyIdToken($user->google_id, $user->email, $user->name)
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
            'organizers' => 'nullable|array',
        ]);
        $this->authorize('update-project', $project);
        $project->fill($request->all());
        if (empty($project->user_id)) {
            $project->user_id = Auth::id();
        }
        if (!$project->id) {
            $project->year = Helper::buddhistYear();
            $previousRecord = Project::latestOfYear($project->year);
            $project->number = $previousRecord ? ($previousRecord->number + 1) : 1;
        }
        $existingParticipants = $project->participants;
        $project->saveOrFail();
        if ($request->filled('organizers')) {
            // Note: these lines of code suffers from n+1 performance issue
            $inputParticipants = new Collection($request->input('organizers', []));
            $newParticipants = new Collection();
            $users = User::whereIn('student_id', [...$inputParticipants->pluck('student_id'), ...$existingParticipants->pluck('student_id')])->get();
            foreach ($inputParticipants as $student) {
                // Add / edit existing
                $user = User::firstOrCreate(['email' => $student['email']], [
                        'name' => ($student['title'] ?? '') . $student['first_name'] . ' ' . $student['last_name'],
                        'student_id' => $student['student_id'],
                    ]);
                if ($participant = $existingParticipants->where('user_id', $user->id)->first()) {
                    // Existing
                    if ($participant->type != 'organizer') {
                        $participant->type = 'organizer';
                        $participant->save();
                    }
                } else {
                    $newParticipants->push(['user_id' => $user->id, 'type' => 'organizer']);
                }
            }
            if ($newParticipants->isNotEmpty()) {
                $project->participants()->createMany($newParticipants);
            }
            // Delete unused existing
            $existingParticipants->whereNotIn('user_id', $users->whereIn('student_id', $inputParticipants->pluck('student_id'))->pluck('id'))->where('type', 'organizer')->each->delete();
        } elseif ($existingParticipants->isNotEmpty()) {
            $project->participants()->where('type', 'organizer')->delete();
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
            'doc_number' => '...../'.Helper::buddhistYear(),
            'date' => Carbon::now()->locale('th')->isoFormat('D MMMM ').Helper::buddhistYear(),
            'name' => $project->name,
            'number' => $project->getNumber(),
            'department' => ($project->department_id == 33) ? 'สโมสรนิสิตคณะแพทยศาสตร์ จุฬาลงกรณ์มหาวิทยาลัย' : $project->department->name,
            'period' => (($project->period_start == $project->period_end) ? 'ในวันที่ ' : 'ระหว่างวันที่ ').$ranger->format($project->period_start, $project->period_end),
            'aims' => call_user_func(function ($text) {
                $aims = explode("\n", $text);
                $aims[count($aims)-1] = 'และ'.$aims[count($aims)-1];
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
                'signer_s1_name' => '('.$organizer->user->name.')',
                'signer_s1_title' => 'นิสิตผู้รับผิดชอบโครงการ'
            ]);
        }
        $template->cloneRowAndSetValues('organizers_number', $project->participants->where('type', 'organizer')->map(function (ProjectParticipant $participant, $i) {
            return ['organizers_number' => $i+1, 'organizers_name' => $participant->user->name, 'organizers_id' => $participant->user->student_id];
        }));
        $template->cloneRowAndSetValues('expense_name', array_map(function (array $ex) {
            return ['expense_name' => $ex['name'], 'expense_type' => $ex['type'], 'expense_source' => $ex['source'], 'expense_amount' => number_format($ex['amount'], 2)];
        }, $project->expense));
        $template->cloneRowAndSetValues('objectives_goal', array_map(function (array $o) {
            return ['objectives_goal' => $o['goal'], 'objectives_method' => $o['method']];
        }, $project->objectives));
        $template->cloneRowAndSetValues('aims_number', call_user_func(function ($text) {
            $aims = explode("\n", $text);
            $return = [];
            foreach ($aims as $i => $aim) {
                $return[] = ['aims_number' => $i+1, 'aims_text' => $aim];
            }
            return $return;
        }, $project->aims));
        $template->cloneRowAndSetValues('outcomes_number', call_user_func(function ($text) {
            $outcomes = explode("\n", $text);
            $return = [];
            foreach ($outcomes as $i => $outcome) {
                $return[] = ['outcomes_number' => $i+1, 'outcomes_text' => $outcome];
            }
            return $return;
        }, $project->outcomes));

        $tmpPath = tempnam(storage_path(), 'tmp-projectapproval-');
        $template->saveAs($tmpPath);

        return response()->download($tmpPath, $project->getNumber().' Project Approval.docx')->deleteFileAfterSend(true);
    }
}
