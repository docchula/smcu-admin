<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyProjectVerifyJob;
use App\Models\Department;
use App\Models\Project;
use App\Models\ProjectParticipant;
use App\ProjectClosureStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ProjectClosureController extends Controller {
    public function closureForm(Project $project): Response {
        $this->authorize('update-project', $project);
        abort_unless(in_array($project->getClosureStatus(), [ProjectClosureStatus::NOT_SUBMITTED, ProjectClosureStatus::REJECTED_AND_RESUBMIT])
            , 403, 'Closure already submitted');

        $project->load(['department', 'participants', 'participants.user']);
        $project->participants->transform(function (ProjectParticipant $participant) {
            $participant->user->makeHidden('id', 'profile_photo_url');

            return $participant;
        });

        return Inertia::render('ProjectClosure', [
            'item' => $project,
            'can_submit' => $project->canSubmitClosure(),
        ]);
    }

    /**
     * @throws \Throwable
     */
    public function closureSubmit(Request $request, Project $project) {
        $this->validate($request, [
            'objectives' => 'required|array',
            'expense' => 'nullable|array',
            'action' => 'nullable|string',
        ]);
        $this->authorize('update-project', $project);
        abort_if($project->hasSubmittedClosure()
            and $project->getClosureStatus() !== ProjectClosureStatus::REJECTED_AND_RESUBMIT,
            403, 'Closure already submitted.');
        $action = $request->input('action');

        $project->objectives = $request->input('objectives');
        $project->expense = $request->input('expense');
        if ($action == 'yes') {
            if (!$project->canSubmitClosure()) {
                $project->saveOrFail();
                abort(403, 'Not in closure submission timeframe.');
            }
            $project->submitClosure();
            activity()->causedBy($request->user())->performedOn($project)
                ->event('closure_submit')->log('ส่งยืนยันรายงานผลโครงการ และบันทึกใน Activity Transcript');
        }
        $project->saveOrFail();

        if ($action == 'generate_document') {
            return Inertia::location(route('projects.generateSummaryDocument', ['project' => $project->id]));
        } elseif ($project->closure_submitted_at) {
            NotifyProjectVerifyJob::dispatch($project)->delay(now()->addMinutes(30));
        }

        return redirect()
            ->route('projects.show', ['project' => $project->id])
            ->with('flash.banner', 'บันทึกข้อมูลรายงานผลโครงการเลขที่ '.$project->getNumber().' แล้ว')
            ->with('flash.bannerStyle', 'success');
    }

    public function closureCancel(Request $request, Project $project) {
        $this->validate($request, [
            'confirm' => 'required|filled',
        ]);
        $this->authorize('update-project', $project);
        abort_if(!$project->canVerify(), 403, 'Closure expired or hasn\'t been submitted.');

        activity()->causedBy($request->user())->performedOn($project)
            ->event('closure_cancel')->log('ยกเลิกการส่งรายงานผลโครงการ');
        $project->closure_submitted_at = null;
        $project->closure_submitted_by = null;
        $project->save();
        // Cancel all participant's verification status
        $project->participants()->update([
            'verify_status' => 0,
            'reject_reason' => null,
            'reject_participants' => [],
        ]);

        return redirect()
            ->route('projects.show', ['project' => $project->id])
            ->with('flash.banner', 'ยกเลิกการส่งข้อมูล โครงการเลขที่ '.$project->getNumber().' แล้ว')
            ->with('flash.bannerStyle', 'success');
    }

    public function closureVerifyForm(Request $request, Project $project): Response {
        abort_unless($project->hasSubmittedClosure(), 403, 'Closure not submitted');

        $project->load(['department', 'participants', 'participants.user']);
        $project->participants->transform(function (ProjectParticipant $participant) {
            $participant->user->makeHidden('id', 'profile_photo_url');
            // Convert to boolean in order to hide approval/disapproval
            $participant->verify_status = !empty($participant->verify_status);
            $participant->makeVisible('verify_status')->makeHidden('reject_reason', 'reject_participants');

            return $participant;
        });
        $project->can = [
            'update-project' => $request->user()->can('update-project', $project),
        ];

        return Inertia::render('ProjectClosureVerify', [
            'item' => $project,
            'my_participant' => $project->participants->where('user_id', Auth::id())->first(),
        ]);
    }

    public function closureVerifySubmit(Request $request, Project $project) {
        $this->validate($request, [
            'approve' => 'required|in:yes,no',
            'reason' => 'nullable|required_if:approve,no|string',
            'reason_participants' => 'nullable|required_if:approve,no|array',
        ]);
        abort_if(!$project->canVerify(), 403, 'Closure expired or hasn\'t been submitted.');
        $participant = $project->participants()->where('user_id', $request->user()->id)->first();
        abort_unless($participant and in_array($participant->type, ['organizer', 'staff']), 403, 'Only organizer/staff can verify closure.');

        if ($request->input('approve') == 'yes') {
            $participant->verify_status = 1;
        } else {
            $participant->verify_status = -1;
            $participant->reject_reason = $request->input('reason');
            $participant->reject_participants = $request->input('reason_participants');
        }
        activity()->causedBy($request->user())->performedOn($project)
            ->event('closure_verify')->log('ตรวจสอบรายชื่อนิสิตผู้เกี่ยวข้องของโครงการ');
        $participant->saveOrFail();

        return redirect()
            ->route('projects.closureVerifyForm', ['project' => $project->id])
            ->with('flash.banner', 'บันทึกข้อมูลการรับรองรายชื่อนิสิตผู้เกี่ยวข้อง โครงการเลขที่ '.$project->getNumber().' แล้ว')
            ->with('flash.bannerStyle', 'success');
    }

    public function approvalIndex(Request $request) {
        $this->authorize('faculty-action');
        $keyword = $request->input('search');

        return Inertia::render('ProjectApprovalIndex', [
            'list' => Project::searchQuery($keyword)->withCount('participants')
                ->addSelect(['closure_submitted_at', 'closure_approved_status'])
                ->whereNotNull('closure_submitted_at')
                ->orderByDesc('closure_submitted_at')->limit(500)->get()
                ->map(function (Project $project) {
                    $project->status = $project->getClosureStatus();

                    return $project;
                }),
            'keyword' => $keyword ?? '',
            'static_departments' => Department::optionList(),
        ]);
    }

    public function approvalForm(Request $request, Project $project): Response {
        $this->authorize('faculty-action');

        $project->load(['department', 'participants', 'participants.user', 'summaryDocument']);
        $project->closure_status = $project->getClosureStatus();
        $project->participants->transform(function (ProjectParticipant $participant) {
            $participant->user->makeHidden('id', 'profile_photo_url');
            $participant->makeVisible('verify_status', 'reject_reason', 'reject_participants');

            return $participant;
        });

        return Inertia::render('ProjectApproval', [
            'item' => $project,
        ]);
    }

    public function approvalSubmit(Request $request, Project $project) {
        $this->validate($request, [
            'approve' => 'required|in:yes,no',
            'reason' => 'nullable|required_if:approve,no|string',
            'approve_participants' => 'nullable|required_if:approve,yes|array',
        ]);
        $closureStatus = $project->getClosureStatus();
        abort_unless(in_array($closureStatus,
            [ProjectClosureStatus::SUBMITTED, ProjectClosureStatus::REVIEWING, ProjectClosureStatus::REJECTED_AND_RESUBMIT]), 403,
            'Closure approved or hasn\'t been submitted.');

        $project->closure_approved_by = $request->user()->id;
        $project->closure_approved_at = now();
        DB::beginTransaction();
        if ($request->input('approve') == 'yes') {
            $project->closure_approved_status = 1;
            $project->closure_approved_message = null;
            $project->participants()->whereIn('user_id', $request->input('approve_participants'))->update(['approve_status' => 1]);
            $project->participants()->whereNotIn('user_id', $request->input('approve_participants'))->update(['approve_status' => -1]);
        } elseif ($request->input('allow_resubmit', false)) {
            $project->closure_approved_status = -2;
            $project->closure_approved_message = $request->input('reason');
        } else {
            $project->closure_approved_status = -1;
            $project->closure_approved_message = $request->input('reason');
            $project->participants()->update(['approve_status' => -1]);
        }
        activity()->causedBy($request->user())->performedOn($project)
            ->event('closure_approve')->log('บันทึกผลการอนุมัติรายงานผลโครงการ');
        $project->save();
        DB::commit();

        return redirect()
            ->route('projects.approvalForm', ['project' => $project->id])
            ->with('flash.banner', 'บันทึกผลการอนุมัติรายงานผลโครงการ เลขที่ '.$project->getNumber().' แล้ว')
            ->with('flash.bannerStyle', 'success');
    }

    public function viewLogs(Request $request, Project $project): \Illuminate\Http\JsonResponse {
        abort_unless($request->user()->can('update-project', $project) or $request->user()->can('faculty-action'), 403);

        return response()->json([
            'logs' => $project->activities()->with('causer')->limit(100)->get()->map(fn(Activity $activity) => [
                'id' => $activity->id,
                'description' => $activity->description,
                'created_at' => $activity->created_at->format('j M Y H:i'),
                'causer' => $activity->causer?->name,
            ]),
        ]);
    }
}
