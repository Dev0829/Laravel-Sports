<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SampleDataController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/

// Sample API route
Route::get('/profits', [SampleDataController::class, 'profits'])->name('profits');

Route::post('/register', [RegisteredUserController::class, 'apiStore']);

Route::post('/login', [AuthenticatedSessionController::class, 'apiStore']);

Route::post('/forgot_password', [PasswordResetLinkController::class, 'apiStore']);

Route::post('/verify_token', [AuthenticatedSessionController::class, 'apiVerifyToken']);

// Users
Route::get('/users', [SampleDataController::class, 'getUsers']);
Route::get('/userDetail/{id}', [SampleDataController::class, 'getUserDetail']);
Route::delete('/userDetail/{id}', [SampleDataController::class, 'deleteUserDetail']);
Route::get('/user/{id}', [SampleDataController::class, 'apiUser']);
Route::put('/createUser', [SampleDataController::class, 'apiCreateUser']);
Route::post('/updateUser', [SampleDataController::class, 'apiUpdateUser']);
Route::post('/updateBusinessDetail', [SampleDataController::class, 'updateBusinessDetail']);
Route::post('/change_email', [SampleDataController::class, 'apiUpdateEmail']);
Route::post('/forgot_password', [SampleDataController::class, 'apiUpdatePassword']);
Route::post('/email_preferences', [SampleDataController::class, 'apiEmailPreferences']);
Route::delete('/deactivate_profile/{id}', [SampleDataController::class, 'apiDeactivateProfile']);

// Tasks
Route::get('/tasks', [SampleDataController::class, 'getTasks']);
Route::post('/taskDayInfo', [SampleDataController::class, 'taskDayInfo']);
Route::post('/createTask', [SampleDataController::class, 'createTask']);
Route::get('/taskDetail/{id}', [SampleDataController::class, 'getTaskDetail']);
Route::post('/updateTask', [SampleDataController::class, 'updateTask']);
Route::post('/updateUncommittedTask', [SampleDataController::class, 'updateUncommittedTask']);
Route::post('/updateTaskByKanban', [SampleDataController::class, 'updateTaskByKanban']);
Route::post('/updateTaskByCalendar', [SampleDataController::class, 'updateTaskByCalendar']);

// Milestones
Route::get('/milestones', [SampleDataController::class, 'getMilestones']);
Route::post('/createMilestone', [SampleDataController::class, 'createMilestone']);
Route::get('/milestoneDetail/{id}', [SampleDataController::class, 'getMilestoneDetail']);
Route::post('/updateMilestone', [SampleDataController::class, 'updateMilestone']);
Route::post('/deleteMilestone', [SampleDataController::class, 'deleteMilestone']);

// Goals
Route::get('/goals', [SampleDataController::class, 'getGoals']);
Route::post('/createGoal', [SampleDataController::class, 'createGoal']);
Route::get('/goalDetail/{id}', [SampleDataController::class, 'getGoalDetail']);
Route::post('/updateGoal', [SampleDataController::class, 'updateGoal']);
Route::delete('/deleteGoal/{id}', [SampleDataController::class, 'deleteGoal']);

// Visions
Route::get('/visions', [SampleDataController::class, 'getVisions']);
Route::post('/createVision', [SampleDataController::class, 'createVision']);
Route::get('/visionDetail/{id}', [SampleDataController::class, 'getVisionDetail']);
Route::get('/visionDetailByParam/{type}', [SampleDataController::class, 'visionDetailByParam']);
Route::post('/updateVision', [SampleDataController::class, 'updateVision']);
Route::delete('/deleteVision/{id}', [SampleDataController::class, 'deleteVision']);

// Issues
Route::get('/issues', [SampleDataController::class, 'getIssues']);
Route::post('/createIssue', [SampleDataController::class, 'createIssue']);
Route::get('/issueDetail/{id}', [SampleDataController::class, 'getIssueDetail']);
Route::post('/updateIssue', [SampleDataController::class, 'updateIssue']);
Route::delete('/deleteIssue/{id}', [SampleDataController::class, 'deleteIssue']);

// Questions
Route::post('/questions', [SampleDataController::class, 'getQuestions']);
Route::post('/createQuestion', [SampleDataController::class, 'createQuestion']);
Route::get('/questionDetail/{id}', [SampleDataController::class, 'getQuestionDetail']);
Route::post('/updateQuestion', [SampleDataController::class, 'updateQuestion']);
Route::delete('/deleteQuestion/{id}', [SampleDataController::class, 'deleteQuestion']);
Route::post('/weekyReview', [SampleDataController::class, 'weekyReview']);

// Objectives
Route::get('/objectives', [SampleDataController::class, 'getObjectives']);
Route::post('/searchObjectives', [SampleDataController::class, 'searchObjectives']);
Route::get('/subObjectives/{id}', [SampleDataController::class, 'getSubObjectives']);
Route::post('/searchUnflattenObjectives', [SampleDataController::class, 'searchUnflattenObjectives']);
Route::post('/createObjective', [SampleDataController::class, 'createObjective']);
Route::post('/createParentObjective', [SampleDataController::class, 'createParentObjective']);
Route::get('/objectiveParent/{id}', [SampleDataController::class, 'getObjectiveParent']);
Route::get('/objectiveDetail/{id}', [SampleDataController::class, 'getObjectiveDetail']);
Route::post('/updateObjective', [SampleDataController::class, 'updateObjective']);
Route::delete('/deleteObjective/{id}', [SampleDataController::class, 'deleteObjective']);
Route::delete('/deleteParentObjective/{id}', [SampleDataController::class, 'deleteParentObjective']);

// AccountabilityCall
Route::post('/accountabilityCall', [SampleDataController::class, 'accountabilityCall']);

// Billing
Route::post('/billing', [SampleDataController::class, 'apiBilling']);
Route::get('/getBillingByUser/{id}', [SampleDataController::class, 'getBillingByUser']);

// Activity log
Route::get('/activity', [SampleDataController::class, 'apiActivity']);