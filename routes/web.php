<?php

use App\Helper;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProjectClosureController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TranscriptController;
use App\Http\Controllers\UserProfileController;
use App\Models\Personnel;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;
use Spatie\Health\Http\Controllers\SimpleHealthCheckController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::get('/board/{year?}', function (?string $year = null) {
    abort_if($year && (!is_numeric($year) or !in_array($year, Personnel::getYearList())), 404);
    return view('personnel', ['year' => $year ?? Helper::buddhistYear()]);
});

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('login.google');
Route::get('auth/google/faculty', [GoogleController::class, 'redirectWithoutHd'])->name('login.googleWithoutHd');
Route::get('auth/google/itdivision', [GoogleController::class, 'redirectToGoogleWithGmailAccess']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('health', SimpleHealthCheckController::class);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/manual', function () {
        return redirect('https://smcu.notion.site/SMCU2568-14108afa9ec480ea9cb5c1eb25582d49');
    })->name('manual');

    Route::get('plan', [PlanController::class, 'index'])->name('plan.index');

    // Resource controller : https://laravel.com/docs/8.x/controllers#resource-controllers
    Route::get('projects/agenda', [ProjectController::class, 'indexAgenda'])->name('projects.indexAgenda');
    Route::get('projects/by_year', [ProjectController::class, 'indexOfYear'])->name('projects.indexOfYear');
    Route::get('projects/budget', [ProjectController::class, 'indexBudget'])->name('projects.budget');
    Route::resources([
        'documents' => DocumentController::class,
        'projects' => ProjectController::class,
        'personnels' => PersonnelController::class,
        'activities' => ActivityController::class,
    ]);
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('documents/{document}/downloadApproved', [DocumentController::class, 'downloadApproved'])->name('documents.downloadApproved');

    Route::get('projects/{project}/closure', [ProjectClosureController::class, 'closureForm'])->name('projects.closureForm');
    Route::post('projects/{project}/closure', [ProjectClosureController::class, 'closureSubmit'])->name('projects.closureSubmit');
    Route::get('projects/{project}/closure/verify', [ProjectClosureController::class, 'closureVerifyForm'])->name('projects.closureVerifyForm');
    Route::post('projects/{project}/closure/verify', [ProjectClosureController::class, 'closureVerifySubmit'])->name('projects.closureVerifySubmit');
    Route::post('projects/{project}/closure/cancel', [ProjectClosureController::class, 'closureCancel'])->name('projects.closureCancel');
    Route::get('projects-approval', [ProjectClosureController::class, 'approvalIndex'])->name('projects.approvalIndex');
    Route::get('projects/{project}/approval', [ProjectClosureController::class, 'approvalForm'])->name('projects.approvalForm');
    Route::post('projects/{project}/approval', [ProjectClosureController::class, 'approvalSubmit'])->name('projects.approvalSubmit');
    Route::post('projects/{project}/remark', [ProjectClosureController::class, 'updateRemark'])->name('projects.updateRemark');
    Route::get('projects/{project}/logs', [ProjectClosureController::class, 'viewLogs'])->name('projects.logs');

    Route::get('projects/{project}/generateApprovalDocument', [ProjectController::class, 'generateApprovalDocument'])->name('projects.generateApprovalDocument');
    Route::get('projects/{project}/generateSummaryDocument', [ProjectController::class, 'generateSummaryDocument'])->name('projects.generateSummaryDocument');
    Route::get('projects/search/{keyword}', [ProjectController::class, 'search'])->name('projects.search');
    Route::post('projects/{project}/addParticipant', [ProjectController::class, 'addParticipant'])->name('projects.addParticipant');
    Route::post('projects/importParticipantUpload', [ProjectController::class, 'importParticipantUpload'])->name('projects.importParticipantUpload');
    Route::post('projects/importParticipantCommit', [ProjectController::class, 'importParticipantCommit'])->name('projects.importParticipantCommit');
    Route::post('projects/removeParticipant/{participant}', [ProjectController::class, 'removeParticipant'])->name('projects.removeParticipant');
    Route::post('projects/editParticipant/{participant}', [ProjectController::class, 'editParticipant'])->name('projects.editParticipant');
    Route::get('projects/{project}/exportParticipant', [ProjectController::class, 'exportParticipant'])->name('projects.exportParticipant');
    Route::get('search-participants', [ProjectController::class, 'searchNewParticipant'])->name('projects.searchNewParticipant');
    Route::get('search-student', [PersonnelController::class, 'searchStudent'])->name('personnels.searchStudent');
    Route::get('advisor-list', [ProjectController::class, 'advisorList'])->name('advisor-list');

    Route::get('activities/{activity}/download', [ActivityController::class, 'downloadAttachment'])->name('activities.download');

    Route::get('/user/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/user/profile/printMyProjects', [UserProfileController::class, 'printMyProjects'])->name('profile.printMyProjects');

    Route::get('transcript', [TranscriptController::class, 'index'])->name('transcript.index');
    Route::get('transcript/{user}/print', [TranscriptController::class, 'print'])->name('transcript.print');

    Route::get('health/board', HealthCheckResultsController::class);
});
