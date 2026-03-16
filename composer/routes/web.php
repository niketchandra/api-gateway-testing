<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminDashboardController;

// Root route - show login/registration on left sidebar, content on right
Route::get('/', function () {
    return view('app');
})->name('home');

Route::get('/login', function () {
    return redirect()->route('home');
})->name('login.form');

// Authentication routes
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/password/email', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
Route::post('/contact', [AuthController::class, 'storeContact'])->name('contact');

// Protected routes - requires web session authentication
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/configuration-backups', [DashboardController::class, 'configurationBackups'])->name('configuration-backups');
    Route::get('/configuration-backups/service-name/{serviceName}/versions', [DashboardController::class, 'viewServiceVersionsByName'])->name('configuration-backups.service-versions-by-name');
    Route::get('/configuration-backups/service/{serviceId}/versions', [DashboardController::class, 'viewServiceVersions'])->name('configuration-backups.service-versions');
    Route::get('/configuration-backups/{id}/view', [DashboardController::class, 'viewConfigurationFile'])->name('configuration-backups.view');
    Route::get('/configuration-backups/{id}/download', [DashboardController::class, 'downloadConfigurationFile'])->name('configuration-backups.download');
    Route::get('/systems-registered', [DashboardController::class, 'systemsRegistered'])->name('systems-registered');
    Route::get('/systems-registered/{systemId}/services', [DashboardController::class, 'systemServices'])->name('systems-registered.services');
    Route::get('/live-service-monitoring', [DashboardController::class, 'liveServiceMonitoring'])->name('live-service-monitoring');
    Route::get('/vulnerabilities-identified', [DashboardController::class, 'vulnerabilitiesIdentified'])->name('vulnerabilities-identified');
    Route::get('/settings', [DashboardController::class, 'settings'])->name('settings');
    Route::post('/settings/update', [DashboardController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/api-keys', [DashboardController::class, 'createApiKey'])->name('settings.api-keys.create');
    Route::post('/settings/api-keys/view', [DashboardController::class, 'viewApiKey'])->name('settings.api-keys.view');
    Route::post('/settings/api-keys/revoke', [DashboardController::class, 'revokeApiKey'])->name('settings.api-keys.revoke');
    Route::post('/password/update', [DashboardController::class, 'updatePassword'])->name('password.update');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::get('/products', [DashboardController::class, 'products'])->name('products');

    Route::middleware('admin.role')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/users', [AdminDashboardController::class, 'usersIndex'])->name('admin.users');
        Route::post('/users', [AdminDashboardController::class, 'createUser'])->name('admin.users.store');
        Route::get('/users/{user}', [AdminDashboardController::class, 'userDashboard'])->name('admin.users.show');
        Route::get('/users/{user}/profile', [AdminDashboardController::class, 'userProfile'])->name('admin.users.profile');
        Route::put('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('admin.users.update');
        Route::get('/users/{user}/systems/{systemId}/services', [AdminDashboardController::class, 'userSystemServices'])->name('admin.users.systems.services');
        Route::get('/users/{user}/services/{serviceId}/versions', [AdminDashboardController::class, 'userServiceVersions'])->name('admin.users.services.versions');
        Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('admin.settings');
        Route::post('/settings/site', [AdminDashboardController::class, 'updateSiteSettings'])->name('admin.settings.site');
        Route::post('/settings/s3', [AdminDashboardController::class, 'updateS3Settings'])->name('admin.settings.s3');
        Route::post('/settings/mail', [AdminDashboardController::class, 'updateMailSettings'])->name('admin.settings.mail');
        Route::post('/settings/sso', [AdminDashboardController::class, 'updateSsoSettings'])->name('admin.settings.sso');
    });
});
