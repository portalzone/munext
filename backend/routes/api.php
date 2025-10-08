<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmployerProfileController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\StudentProfileController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});

// Public job routes
Route::prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'index']);
    Route::get('/{id}', [JobController::class, 'show']);
});

// Public profile routes
Route::get('profiles/student/{id}', [StudentProfileController::class, 'showPublic']);
Route::get('profiles/employer/{id}', [EmployerProfileController::class, 'showPublic']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('verify', [AuthController::class, 'verify']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Student/Alumni profile routes
    Route::middleware('role:student,alumni')->prefix('student/profile')->group(function () {
        Route::get('/', [StudentProfileController::class, 'show']);
        Route::post('/', [StudentProfileController::class, 'store']);
        Route::post('resume', [StudentProfileController::class, 'uploadResume']);
        Route::delete('resume', [StudentProfileController::class, 'deleteResume']);
    });

    // Employer profile routes
    Route::middleware('role:employer')->prefix('employer/profile')->group(function () {
        Route::get('/', [EmployerProfileController::class, 'show']);
        Route::post('/', [EmployerProfileController::class, 'store']);
        Route::post('logo', [EmployerProfileController::class, 'uploadLogo']);
        Route::delete('logo', [EmployerProfileController::class, 'deleteLogo']);
    });

    // Job routes (employer)
    Route::middleware('role:employer')->prefix('employer/jobs')->group(function () {
        Route::get('/', [JobController::class, 'myJobs']);
        Route::post('/', [JobController::class, 'store']);
        Route::put('/{id}', [JobController::class, 'update']);
        Route::delete('/{id}', [JobController::class, 'destroy']);
        Route::get('/{id}/applications', [ApplicationController::class, 'jobApplications']);
    });

    // Job routes (student)
    Route::middleware('role:student,alumni')->prefix('student/jobs')->group(function () {
        Route::post('/{id}/save', [JobController::class, 'toggleSave']);
        Route::get('/saved', [JobController::class, 'savedJobs']);
    });

    // Application routes (student)
    Route::middleware('role:student,alumni')->prefix('student/applications')->group(function () {
        Route::get('/', [ApplicationController::class, 'myApplications']);
        Route::post('/{jobId}', [ApplicationController::class, 'store']);
        Route::get('/{id}', [ApplicationController::class, 'show']);
        Route::delete('/{id}/withdraw', [ApplicationController::class, 'withdraw']);
    });

    // Application routes (employer)
    Route::middleware('role:employer')->prefix('employer/applications')->group(function () {
        Route::get('/{id}', [ApplicationController::class, 'show']);
        Route::put('/{id}/status', [ApplicationController::class, 'updateStatus']);
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'destroyAll']);
    });

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('dashboard', [AdminController::class, 'dashboard']);
        Route::get('analytics', [AdminController::class, 'analytics']);

        // User management
        Route::get('users', [AdminController::class, 'users']);
        Route::get('users/{id}', [AdminController::class, 'showUser']);
        Route::put('users/{id}/verify', [AdminController::class, 'verifyUser']);
        Route::put('users/{id}/unverify', [AdminController::class, 'unverifyUser']);
        Route::delete('users/{id}', [AdminController::class, 'deleteUser']);

        // Job management
        Route::get('jobs', [AdminController::class, 'jobs']);
        Route::delete('jobs/{id}', [AdminController::class, 'deleteJob']);
        Route::put('jobs/{id}/status', [AdminController::class, 'updateJobStatus']);

        // Application management
        Route::get('applications', [AdminController::class, 'applications']);

        // Reports
        Route::get('reports', [AdminController::class, 'reports']);
    });
});
