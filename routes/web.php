<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventFormController;
use App\Http\Controllers\JuryFormTemplateController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\JuryScoreController;
use App\Http\Controllers\JuryInvitationController;
use App\Http\Controllers\EventJuryFormAssignmentController;
use App\Http\Controllers\AboutController;
use App\Http\Controllers\OwnerUserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [EventController::class, 'publicIndex'])->name('home');
Route::get('/events', [EventController::class, 'publicIndex'])->name('events.public');
Route::get('/events/{event}/detail', [EventController::class, 'publicShow'])->name('events.detail');

// About pages
Route::get('/about', [AboutController::class, 'about'])->name('about');
Route::get('/company-profile', [AboutController::class, 'companyProfile'])->name('company-profile');

// Authentication required routes
Route::middleware(['auth'])->group(function () {

    // Dashboard (role-based)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifikasi (semua peran)
    Route::post('/notifications/read', [JuryInvitationController::class, 'markNotificationsRead'])->name('jury.notifications.read');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Participant routes
    Route::middleware(['role:participant'])->group(function () {
        Route::post('/events/{event}/register', [RegistrationController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('registrations.store');
        Route::get('/my-registrations', [RegistrationController::class, 'myRegistrations'])->name('registrations.my');

        // Payment upload
        Route::get('/registrations/{registration}/payment', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/registrations/{registration}/payment', [PaymentController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('payments.store');
    });

    // Registration detail: participant owner or event manager
    Route::middleware(['role:participant,admin,admin_owner'])->group(function () {
        Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
        Route::get('/payments/{payment}/proof', [PaymentController::class, 'proof'])->name('payments.proof');
    });

    // Event management routes (admin)
    Route::middleware(['role:admin,admin_owner'])->group(function () {
        Route::resource('events', EventController::class)->except(['index']);
        Route::get('/my-events', [EventController::class, 'index'])->name('events.index');
        Route::post('/events/{event}/update-status', [EventController::class, 'updateStatus'])->name('events.update-status');
        Route::post('/events/{event}/scoring', [EventController::class, 'updateScoring'])->name('events.scoring');
        Route::get('/events/{event}/participants', [RegistrationController::class, 'participants'])->name('events.participants');
        Route::patch('/registrations/{registration}/status', [RegistrationController::class, 'updateStatus'])->name('registrations.update-status');

        // Event Form Management
        Route::get('/events/{event}/form', [EventFormController::class, 'create'])->name('events.form.create');
        Route::get('/events/{event}/form/custom', [EventFormController::class, 'customBuilder'])->name('events.form.custom');
        Route::post('/events/{event}/form', [EventFormController::class, 'store'])->name('events.form.store');
        Route::post('/events/{event}/form/template/{template}', [EventFormController::class, 'useTemplate'])->name('events.form.template');
        Route::post('/events/{event}/form/save-template', [EventFormController::class, 'saveAsTemplate'])->name('events.form.save-template');
        Route::post('/events/{event}/form/custom-template/{customTemplate}', [EventFormController::class, 'useCustomTemplate'])->name('events.form.custom-template');
        Route::put('/custom-event-templates/{customTemplate}', [EventFormController::class, 'updateCustomTemplate'])->name('custom-event-templates.update');
        Route::delete('/custom-event-templates/{customTemplate}', [EventFormController::class, 'deleteCustomTemplate'])->name('custom-event-templates.delete');

        // Payment verification
        Route::get('/payments/pending', [PaymentController::class, 'pendingPayments'])->name('payments.pending');
        Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    });

    // Admin & Admin Owner routes
    Route::middleware(['role:admin,admin_owner'])->group(function () {
        Route::get('/payments/all', [PaymentController::class, 'allPayments'])->name('payments.all');

        // Jury Form Management (Admin only)
        Route::get('/events/{event}/jury-form', [JuryFormTemplateController::class, 'create'])->name('events.jury-form.create');
        Route::get('/events/{event}/jury-form/custom', [JuryFormTemplateController::class, 'customBuilder'])->name('events.jury-form.custom');
        Route::post('/events/{event}/jury-form', [JuryFormTemplateController::class, 'store'])->name('events.jury-form.store');
        Route::post('/events/{event}/jury-form/template/{template}', [JuryFormTemplateController::class, 'useTemplate'])->name('events.jury-form.template');
        Route::post('/events/{event}/jury-form/save-template', [JuryFormTemplateController::class, 'saveAsTemplate'])->name('events.jury-form.save-template');
        Route::post('/events/{event}/jury-form/custom-template/{customTemplate}', [JuryFormTemplateController::class, 'useCustomTemplate'])->name('events.jury-form.custom-template');
        Route::put('/custom-jury-templates/{customTemplate}', [JuryFormTemplateController::class, 'updateCustomTemplate'])->name('custom-jury-templates.update');
        Route::delete('/custom-jury-templates/{customTemplate}', [JuryFormTemplateController::class, 'deleteCustomTemplate'])->name('custom-jury-templates.delete');
    });

    // Admin Owner (super admin) only
    Route::middleware(['role:admin_owner'])->prefix('owner')->name('owner.')->group(function () {
        Route::get('/users', [OwnerUserController::class, 'index'])->name('users.index');
        Route::post('/users', [OwnerUserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/role', [OwnerUserController::class, 'updateRole'])->name('users.role');
        Route::patch('/users/{user}/scope', [OwnerUserController::class, 'updateScope'])->name('users.scope');
        Route::patch('/users/{user}/status', [OwnerUserController::class, 'toggleStatus'])->name('users.status');
    });

    // Jury routes
    Route::middleware(['role:jury,admin,admin_owner'])->prefix('jury')->name('jury.')->group(function () {
        Route::get('/invitations', [JuryInvitationController::class, 'index'])->name('invitations.index');
        Route::post('/invitations/{assignment}/accept', [JuryInvitationController::class, 'accept'])->name('invitations.accept');
        Route::post('/invitations/{assignment}/decline', [JuryInvitationController::class, 'decline'])->name('invitations.decline');
        Route::get('/events', [JuryScoreController::class, 'index'])->name('events');
        Route::get('/events/{event}/participants', [JuryScoreController::class, 'participants'])->name('participants');
        Route::get('/events/{event}/form', [JuryScoreController::class, 'form'])->name('form');
        Route::get('/registrations/{registration}/score', [JuryScoreController::class, 'create'])->name('score');
        Route::post('/registrations/{registration}/score', [JuryScoreController::class, 'store'])
            ->middleware('throttle:30,1')
            ->name('score.store');
        Route::get('/scores/{juryScore}/edit', [JuryScoreController::class, 'edit'])->name('edit-score');
        Route::patch('/scores/{juryScore}', [JuryScoreController::class, 'update'])->name('update-score');
        Route::get('/events/{event}/leaderboard', [JuryScoreController::class, 'leaderboard'])->name('leaderboard');
    });

    // Admin-only jury management routes
    Route::middleware(['role:admin,admin_owner'])->prefix('jury')->name('jury.')->group(function () {
        Route::get('/all-scores', [JuryScoreController::class, 'allScores'])->name('all-scores');
    });

    // Event recap and jury assignment (admin)
    Route::middleware(['role:admin,admin_owner'])->group(function () {
        Route::get('/events/{event}/recap', [JuryScoreController::class, 'recap'])->name('events.recap');
        
        // Jury Form Assignment Management
        Route::get('/events/{event}/jury-assignments', [EventJuryFormAssignmentController::class, 'index'])->name('events.jury-assignments.index');
        Route::post('/events/{event}/jury-accounts', [EventJuryFormAssignmentController::class, 'storeAccount'])->name('events.jury-accounts.store');
        Route::post('/events/{event}/jury-assignments', [EventJuryFormAssignmentController::class, 'store'])->name('events.jury-assignments.store');
        Route::post('/events/{event}/jury-assignments/quick-create', [EventJuryFormAssignmentController::class, 'quickCreate'])->name('events.jury-assignments.quick-create');
        Route::delete('/events/{event}/jury-assignments/{assignment}', [EventJuryFormAssignmentController::class, 'destroy'])->name('events.jury-assignments.destroy');
        Route::post('/events/{event}/jury-assignments/{assignment}/reset-password', [EventJuryFormAssignmentController::class, 'resetJuryPassword'])->name('events.jury-assignments.reset-password');
    });
});

require __DIR__ . '/auth.php';
