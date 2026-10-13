<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DueController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\ToolsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');
Route::get('/locale/{locale}', [LandingController::class, 'locale'])->name('locale');
Route::post('/demo', DemoController::class)->middleware(['guest', 'throttle:10,1'])->name('demo');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/today', DueController::class)->name('due');

    // clients & pipeline
    Route::get('/pipeline', [ClientController::class, 'pipeline'])->name('pipeline');
    Route::get('/clients/import', [ImportController::class, 'create'])->name('clients.import');
    Route::post('/clients/import', [ImportController::class, 'store'])->middleware('throttle:5,1')->name('clients.import.store');
    Route::get('/clients/import/template', [ImportController::class, 'template'])->name('clients.import.template');
    Route::resource('clients', ClientController::class);
    Route::patch('/clients/{client}/stage', [ClientController::class, 'stage'])->name('clients.stage');
    Route::post('/clients/{client}/touch', [ClientController::class, 'touch'])->name('clients.touch');
    Route::get('/clients/{client}/nic', [ClientController::class, 'revealNic'])->middleware('throttle:20,1')->name('clients.nic');
    Route::post('/clients/{client}/documents', [DocumentController::class, 'store'])->middleware('throttle:20,1')->name('documents.store');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // policies & payments
    Route::resource('policies', PolicyController::class)->parameters(['policies' => 'policy']);
    Route::post('/policies/{policy}/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');

    // reports & tools
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    Route::get('/export/{what}', [ReportController::class, 'export'])->middleware('throttle:10,1')->name('export');
    Route::get('/tools', ToolsController::class)->name('tools');

    // team
    Route::get('/team', [TeamController::class, 'index'])->name('team');
    Route::post('/team', [TeamController::class, 'store'])->middleware('not-demo')->name('team.store');
    Route::post('/team/join', [TeamController::class, 'join'])->middleware(['not-demo', 'throttle:10,1'])->name('team.join');
    Route::post('/team/leave', [TeamController::class, 'leave'])->name('team.leave');
    Route::post('/team/code', [TeamController::class, 'regenerate'])->name('team.code');
    Route::delete('/team/members/{member}', [TeamController::class, 'remove'])->name('team.remove');

    // settings
    Route::get('/settings/profile', [SettingsController::class, 'profile'])->name('settings.profile');
    Route::get('/settings/security', [SettingsController::class, 'security'])->name('settings.security');
    Route::get('/settings/messages', [SettingsController::class, 'messages'])->name('settings.messages');
    Route::put('/settings/messages', [SettingsController::class, 'updateMessages'])->name('settings.messages.update');
    Route::post('/settings/sessions/logout', [SettingsController::class, 'logoutOtherSessions'])->middleware('not-demo')->name('settings.sessions.logout');
    Route::delete('/settings/account', [SettingsController::class, 'destroy'])->middleware('not-demo')->name('settings.account.destroy');
});
