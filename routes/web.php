<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\Dashboard;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('login.google');
Route::get('auth/google/itdivision', [GoogleController::class, 'redirectToGoogleWithGmailAccess']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/manual', function () {
        return redirect('https://tasteful-silk-0e3.notion.site/SMCU-Activity-Manual-46105744f57645a2afaa7a30f8ce1d06');
    })->name('manual');

    Route::get('budget', [BudgetController::class, 'index'])->name('budget.index');
    Route::get('plan', [PlanController::class, 'index'])->name('plan.index');

    // Resource controller : https://laravel.com/docs/8.x/controllers#resource-controllers
    Route::get('projects/by_year', [ProjectController::class, 'indexOfYear'])->name('projects.indexOfYear');
    Route::resources([
        'documents' => DocumentController::class,
        'projects' => ProjectController::class,
    ]);
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('documents/{document}/downloadApproved', [DocumentController::class, 'downloadApproved'])->name('documents.downloadApproved');
    Route::get('projects/{project}/generateApprovalDocument', [ProjectController::class, 'generateApprovalDocument'])->name('projects.generateApprovalDocument');
    Route::get('projects/{project}/generateSummaryDocument', [ProjectController::class, 'generateSummaryDocument'])->name('projects.generateSummaryDocument');
    Route::get('projects/search/{keyword}', [ProjectController::class, 'search'])->name('projects.search');
    Route::post('projects/{project}/addParticipant', [ProjectController::class, 'addParticipant'])->name('projects.addParticipant');
    Route::post('projects/{project}/importParticipantUpload', [ProjectController::class, 'importParticipantUpload'])->name('projects.importParticipantUpload');
    Route::post('projects/{project}/importParticipantCommit', [ProjectController::class, 'importParticipantCommit'])->name('projects.importParticipantCommit');
    Route::post('projects/removeParticipant/{participant}', [ProjectController::class, 'removeParticipant'])->name('projects.removeParticipant');
    Route::get('projects/{project}/exportParticipant', [ProjectController::class, 'exportParticipant'])->name('projects.exportParticipant');
    Route::get('search-participants', [ProjectController::class, 'searchNewParticipant'])->name('projects.searchNewParticipant');

    Route::get('/user/profile', [UserProfileController::class, 'show'])->name('profile.show');
    Route::get('/user/profile/printMyProjects', [UserProfileController::class, 'printMyProjects'])->name('profile.printMyProjects');
});
