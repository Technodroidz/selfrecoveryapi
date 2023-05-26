<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RegisterController;


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
// #admin routes
Route::post('login', [RegisterController::class, 'login']);
Route::post('add-quiz', [RegisterController::class, 'addQuiz']);
Route::post('update-quiz', [RegisterController::class, 'updateQuiz']);
Route::post('delete-quiz', [RegisterController::class, 'deleteQuiz']);
Route::get('fetch-all-quiz', [RegisterController::class, 'fetchQuiz']);
Route::get('fetch-all-users', [RegisterController::class, 'fetchAllUsers']);


//#user routes
Route::post('user-login', [RegisterController::class, 'userLogin']);
Route::post('add-user', [RegisterController::class, 'addUser']);
Route::post('fetch-user-quiz', [RegisterController::class, 'fetchUserQuiz']);
Route::post('add-questions', [RegisterController::class, 'addQuestions']);
Route::get('share-quiz/{id}', [RegisterController::class, 'shareQuiz']);

//#common routes
Route::post('submit-query', [RegisterController::class, 'submitQuery']);