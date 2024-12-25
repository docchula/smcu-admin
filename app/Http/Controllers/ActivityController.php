<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ActivityController extends Controller {
    public function index(Request $request) {
        $this->authorize('create-activity');
        $keyword = $request->input('search');

        return Inertia::render('ActivityIndex', [
            'list' => Activity::searchQuery($keyword)->paginate(50)->withQueryString(),
            'keyword' => $keyword,
        ]);
    }

    public function create(Request $request): Response {
        return $this->edit($request, new Activity());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Activity $activity): Response {
        return $this->renderCreateView($activity, false);
    }

    public function show(Activity $activity): Response {
        return $this->renderCreateView($activity, true);
    }

    protected function renderCreateView(Activity $activity, bool $isViewOnly): Response {
        $this->authorize('create-activity');
        if ($activity->participants) {
            $participantUsers = User::whereIn('id', $activity->participants)->get()->keyBy('id');
            $activity->participants = $activity->participants->map(function (string $p) use ($participantUsers) {
                $user = $participantUsers->get($p);

                return $user ? ['name' => $user->name, 'student_id' => $user->student_id, 'id' => $user->id] : null;
            })->filter();
        }

        return Inertia::render('ActivityCreate', [
            'item' => $activity,
            'view_only' => $isViewOnly,
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
            'role' => 'required|filled|string|in:organizer,staff,attendee',
            'description' => 'nullable|string|max:4000',
            'attachment' => 'nullable|file|mimes:pdf,docx,doc|max:20000', // File size limit: 20 MB
            'participants' => 'required|array',
            'participants.*.student_id' => 'required|numeric',
        ]);
        $activity->fill($request->all());

        // Save participants as user IDs
        $participantIds = collect($request->input('participants'))->map(function ($participant) {
            return $participant['student_id'];
        });
        $participantUsers = User::whereIn('student_id', $participantIds)->get();
        $activity->participants = $participantUsers->map(function ($user) {
            return $user->id;
        });

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

        activity()->causedBy($request->user())->performedOn($activity)
            ->event('update_activity');

        return redirect()
            ->route('activities.show', ['activity' => $activity->id])
            ->with('flash.banner', "บันทึกประวัติกิจกรรม เลขที่ {$activity->id} แล้ว")
            ->with('flash.bannerStyle', 'success');
    }
}
