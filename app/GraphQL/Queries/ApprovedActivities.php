<?php

namespace App\GraphQL\Queries;

use App\Helper;
use App\Models\Activity;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

final class ApprovedActivities {
    public function __invoke($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo): LengthAwarePaginator {
        $perPage = $args['first'] ?? 25;
        $currentPage = $args['page'] ?? 1;
        $queryLimit = $perPage * $currentPage;
        $betweenParam = [$args['from'], $args['until'] ?? now()->addDay()];

        $projectsQuery = Project::where('closure_approved_status', 1)
            ->whereBetween('closure_approved_at', $betweenParam);
        $projects = $projectsQuery->with([
            'participants' => fn($query) => $query->where('approve_status', '>=', 1),
            'participants.user',
            'department',
        ])
            ->select([
                'id', 'year', 'number', 'name', 'department_id', 'period_start', 'period_end', 'duration', 'closure_approved_at',
                'closure_approved_status', 'advisor',
            ])
            ->orderBy('closure_approved_at')
            ->take($queryLimit)->get()
            ->map(fn($project) => [
                'identifier' => $project->year.'-'.$project->number,
                'project_id' => $project->id,
                'name' => $project->name,
                'organization' => Helper::formatDepartmentName($project->department?->name ?? ''),
                'period_start' => $project->period_start?->toDateTimeString(),
                'period_end' => $project->period_end?->toDateTimeString(),
                'duration' => $project->duration,
                'approved_status' => $project->closure_approved_status,
                'approved_at' => $project->closure_approved_at,
                'advisor' => $project->advisor,
                'participants' => $project->participants,
            ])->toBase();
        // Note that participant approve_status is not set in Activity
        $activitiesQuery = Activity::with(['participants', 'participants.user'])
            ->whereBetween('updated_at', $betweenParam);
        $activities = $activitiesQuery->orderBy('updated_at')
            ->take($queryLimit)->get()
            ->map(fn($activity) => [
                'identifier' => 'A'.$activity->id,
                'activity_id' => $activity->id,
                'name' => $activity->name,
                'organization' => $activity->organization,
                'period_start' => $activity->period_start?->toDateTimeString(),
                'period_end' => $activity->period_end?->toDateTimeString(),
                'duration' => $activity->duration,
                'approved_at' => $activity->updated_at,
                'participants' => $activity->participants,
            ]);
        $merged = $projects->merge($activities)->sortBy('approved_at');

        return new LengthAwarePaginator(
            items: $merged->chunk($perPage)[$currentPage - 1] ?? [],
            total: ($projects->count() == $queryLimit or $activities->count() == $queryLimit)
                ? ($projectsQuery->count() + $activitiesQuery->count()) // count directly from database
                : $merged->count(),
            perPage: $perPage,
            currentPage: $currentPage
        );
    }
}
