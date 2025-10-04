<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\NotificationController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Webhook route (no auth)
Route::post('/payment/webhook', [PaymentController::class, 'webhook']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/switch-organization', [AuthController::class, 'switchOrganization']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Organizations
    Route::get('/organizations', [OrganizationController::class, 'index']);
    Route::get('/organizations/{organization}', [OrganizationController::class, 'show']);
    Route::put('/organizations/{organization}', [OrganizationController::class, 'update']);
    Route::delete('/organizations/{organization}', [OrganizationController::class, 'destroy']);
    Route::post('/organizations/{organization}/update-plan', [OrganizationController::class, 'updatePlan']);
    Route::get('/organizations/{organization}/usage', [OrganizationController::class, 'getUsage']);

    // Projects
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);
    Route::put('/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy']);
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember']);
    Route::delete('/projects/{project}/members/{user}', [ProjectController::class, 'removeMember']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{task}', [TaskController::class, 'show']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);
    Route::post('/tasks/{task}/comments', [TaskController::class, 'addComment']);
    Route::post('/tasks/update-order', [TaskController::class, 'updateOrder']);

    // Team
    Route::get('/team', [TeamController::class, 'index']);
    Route::post('/team/invite', [TeamController::class, 'invite']);
    Route::put('/team/{user}/role', [TeamController::class, 'updateRole']);
    Route::delete('/team/{user}', [TeamController::class, 'remove']);

    // Payment routes
    Route::post('/payment/create-checkout', [PaymentController::class, 'createCheckoutSession']);
    Route::get('/payment/subscription', [PaymentController::class, 'getSubscription']);
    Route::post('/payment/cancel-subscription', [PaymentController::class, 'cancelSubscription']);
    Route::post('/payment/resume-subscription', [PaymentController::class, 'resumeSubscription']);

    // File upload routes
    Route::post('/tasks/{task}/attachments', [FileController::class, 'uploadTaskAttachment']);
    Route::delete('/attachments/{attachment}', [FileController::class, 'deleteTaskAttachment']);
    Route::get('/attachments/{attachment}/download', [FileController::class, 'downloadTaskAttachment']);
    Route::post('/upload/logo', [FileController::class, 'uploadOrganizationLogo']);
    Route::post('/upload/avatar', [FileController::class, 'uploadUserAvatar']);

    // Search
    Route::get('/search', [SearchController::class, 'search']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

});
