<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::group(['namespace' => 'Api'], function () {
    Route::post('/login', 'UserController@login');
    Route::post('/admin/login', [AuthController::class, 'signInAdmin']);
    Route::middleware(['auth.admin'])->group(function () {
        Route::post('/bind-fcm-token', [ChatController::class, 'bindFcmToken']);
        Route::post('/send-notice', [ChatController::class, 'sendNotification']);
        Route::post('/get-rtc-token', [ChatController::class, 'getRtcToken']);
        Route::post('/send-message', [ChatController::class, 'sendMessage']);
        Route::post('/upload-photo', [ChatController::class, 'uploadPhoto']);
        Route::post('/sync-message', [ChatController::class, 'syncMessage']);
    });
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::any('/courseList', 'CourseController@courseList');
        Route::any('/courseDetail', 'CourseController@courseDetail');
        Route::any('/coursesBought', 'CourseController@coursesBought');
        Route::any('/coursesSearchDefault', 'CourseController@coursesSearchDefault');
        Route::any('/coursesSearch', 'CourseController@coursesSearch');
        Route::any('/coursesFavorite', 'CourseController@coursesFavorite');
        Route::any('/coursesMostFavorite', 'CourseController@mostFavoritedCourses');
        Route::any('/coursesNewest', 'CourseController@newestCourses');

        Route::any('/lessonList', 'LessonController@lessonList');
        Route::any('/lessonDetail', 'LessonController@lessonDetail');
        Route::any('/checkout', 'PaymentController@checkout');

        Route::post('/favorites/add', 'FavoriteController@addFavorite');
        Route::post('/favorites/remove', 'FavoriteController@removeFavorite');

        Route::any('/authorCourseList', 'CourseController@authorCourseList');
        Route::any('/courseAuthor', 'CourseController@courseAuthor');

        Route::post('/user/update-avatar', 'UserController@updateAvatar');
        Route::post('/user/update', 'UserController@updatePassword');
       
        Route::post('/bind-fcm-token', [ChatController::class, 'bindFcmToken']);
        Route::post('/send-notice', [ChatController::class, 'sendNotification']);
        Route::post('/get-rtc-token', [ChatController::class, 'getRtcToken']);
        Route::post('/send-message', [ChatController::class, 'sendMessage']);
        Route::post('/upload-photo', [ChatController::class, 'uploadPhoto']);
        Route::post('/sync-message', [ChatController::class, 'syncMessage']);
    });

    Route::any('/webGoHooks', 'PaymentController@webGoHooks');
});



