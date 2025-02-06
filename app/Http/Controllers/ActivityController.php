<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ProjectParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ActivityController extends Controller {
    public function index(Request $request) {
        $this->authorize('view-transcript');
        $keyword = $request->input('search');

        return Inertia::render('ActivityIndex', [
            'list' => Activity::searchQuery($keyword)->withCount('participants')->paginate(50)->withQueryString(),
            'keyword' => $keyword,
            'can_create' => $request->user()->can('create-activity'),
        ]);
    }

    public function create(Request $request): Response {
        return $this->edit($request, new Activity());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Activity $activity): Response {
        $this->authorize('create-activity');

        return $this->renderCreateView($request, $activity, false);
    }

    public function show(Request $request, Activity $activity): Response {
        $this->authorize('view-transcript');

        return $this->renderCreateView($request, $activity, true);
    }

    protected function renderCreateView(Request $request, Activity $activity, bool $isViewOnly): Response {
        if ($activity->participants) {
            $activity->load('participants.user');
            $activity->participants = $activity->participants->map(fn(ProjectParticipant $participant) => [
                ...$participant->toArray(), 'name' => $participant->user->name, 'student_id' => $participant->user->student_id,
            ]);
        }

        // Convert to array
        $activityAttr = $activity->makeHidden('participants')->toArray();
        $activityAttr['attachment_path'] = !empty($activityAttr['attachment_path']);
        $activityAttr['period_start'] = $activity->period_start?->format('Y-m-d');
        $activityAttr['period_end'] = $activity->period_end?->format('Y-m-d');

        return Inertia::render('ActivityCreate', [
            'item' => $activityAttr,
            'participants' => $activity->participants ?? [],
            'view_only' => $isViewOnly,
            'can_edit' => $request->user()->can('create-activity'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @throws \Throwable
     */
    public function store(Request $request): \Symfony\Component\HttpFoundation\Response {
        return $this->update($request, new Activity());
    }

    /**
     * Update the specified resource in storage.
     * @throws \Throwable
     */
    public function update(Request $request, Activity $activity): \Symfony\Component\HttpFoundation\Response {
        $this->authorize('create-activity');
        $this->validate($request, [
            'name' => 'required|filled|string|min:5|max:255',
            'organization' => 'required|filled|string|max:255',
            'period_start' => 'required|date',
            'period_end' => 'required|date',
            'duration' => 'required|numeric|max:999|min:1',
            'description' => 'nullable|string|max:4000',
            'attachment' => 'nullable|file|mimes:pdf,docx,doc|max:20000', // File size limit: 20 MB
            'participants' => 'nullable|array',
            'participants.*.student_id' => 'required|numeric',
            'participants.*.type' => 'required|string|in:organizer,staff,attendee',
            'participants.*.title' => 'nullable|string|max:100',
        ]);
        $activity->fill($request->all());

        if ($request->hasFile('attachment')) {
            $uploadedFile = $request->file('attachment');
            $cleanedFileName = substr(preg_replace("/[^a-zA-Z0-9]+/", "", $uploadedFile->getClientOriginalName()), 0, 20);
            $path = $uploadedFile->storeAs(
                'documents/activity-'.$cleanedFileName.Str::random(3).'.'.$uploadedFile->guessExtension()
            );
            if ($activity->attachment_path and $activity->attachment_path != $path) {
                Storage::delete($activity->attachment_path);
            }
            $activity->attachment_path = $path;
        }
        $activity->saveOrFail();

        // Save participants
        $existingParticipants = $activity->participants ?? new Collection();
        if ($request->filled('participants')) {
            $inputParticipants = new Collection($request->input('participants', []));
            $newParticipants = new Collection();
            $users = User::whereIn('student_id', [...$inputParticipants->pluck('student_id'), ...$existingParticipants->pluck('student_id')])
                ->get();
            foreach ($inputParticipants as $student) {
                // Add / edit existing
                $user = $users->where('student_id', $student['student_id'])->first();
                /** @var \App\Models\ProjectParticipant|null $participant */
                if ($participant = $existingParticipants->where('user_id', $user->id)->first()) {
                    // Existing
                    $participant->type = $student['type'];
                    $participant->title = $student['title'] ?? '';
                    $participant->save();
                } else {
                    $newParticipants->push(['user_id' => $user->id, 'type' => $student['type'], 'title' => $student['title'] ?? '']);
                }
            }
            if ($newParticipants->isNotEmpty()) {
                $activity->participants()->createMany($newParticipants);
            }
            // Delete unused existing
            $deleteParticipantIds = $existingParticipants->whereNotIn('user_id',
                $users->whereIn('student_id', $inputParticipants->pluck('student_id'))->pluck('id'))->pluck('id');
            if ($deleteParticipantIds->isNotEmpty()) {
                $activity->participants()->whereIn('id', $deleteParticipantIds)->delete();
            }
        } elseif ($existingParticipants->isNotEmpty()) {
            $activity->participants()->delete();
        }

        activity()->causedBy($request->user())->performedOn($activity)
            ->event('update_activity');

        return redirect()
            ->route('activities.show', ['activity' => $activity->id])
            ->with('flash.banner', "บันทึกประวัติกิจกรรม เลขที่ $activity->id แล้ว")
            ->with('flash.bannerStyle', 'success');
    }

    public function downloadAttachment(Activity $activity): StreamedResponse {
        $this->authorize('view-transcript');
        abort_if(empty($activity->attachment_path) or Storage::missing($activity->attachment_path), 404);

        return Storage::download(
            $activity->attachment_path,
            'Activity '.$activity->id.' Attachment.'.Str::after($activity->attachment_path, '.')
        );
    }
}
