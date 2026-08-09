<?php

use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisplaySettingController;
use App\Http\Controllers\OdontogramController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientPortalController;
use App\Http\Controllers\PatientRegistrationController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/register-patient', [PatientRegistrationController::class, 'choose'])->name('register-patient.choose');

    Route::get('/register-patient/existing', [PatientRegistrationController::class, 'showExisting'])->name('register-patient.existing');
    Route::post('/register-patient/existing/verify', [PatientRegistrationController::class, 'verifyExisting'])->name('register-patient.existing.verify');
    Route::post('/register-patient/existing/cancel', [PatientRegistrationController::class, 'cancelExisting'])->name('register-patient.existing.cancel');
    Route::post('/register-patient/existing', [PatientRegistrationController::class, 'storeExisting'])->name('register-patient.existing.store');

    Route::get('/register-patient/new', [PatientRegistrationController::class, 'showNew'])->name('register-patient.new');
    Route::post('/register-patient/new', [PatientRegistrationController::class, 'storeNew'])->name('register-patient.new.store');
});

// Public TV/monitor display — no login (runs unattended in the waiting room).
Route::get('/queues/display', [QueueController::class, 'display'])->name('queues.display');
Route::get('/queues/display-data', [QueueController::class, 'displayData'])->name('queues.display-data');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('role:admin,dokter')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('patients', PatientController::class)->except(['destroy']);
        Route::put('/patients/{patient}/medical-history', [PatientController::class, 'updateMedicalHistory'])
            ->name('patients.medical-history.update');

        Route::get('/queues', [QueueController::class, 'index'])->name('queues.index');
        Route::post('/queues', [QueueController::class, 'store'])->name('queues.store');
        Route::post('/queues/{queue}/call', [QueueController::class, 'call'])->name('queues.call');
        Route::post('/queues/{queue}/recall', [QueueController::class, 'recall'])->name('queues.recall');
        Route::post('/queues/{queue}/skip', [QueueController::class, 'skip'])->name('queues.skip');
        Route::get('/queues/{queue}/print', [QueueController::class, 'print'])->name('queues.print');
        Route::post('/queues/{queue}/start-examination', [QueueController::class, 'startExamination'])->name('queues.start-examination');

        Route::get('/visits', [VisitController::class, 'index'])->name('visits.index');
        Route::get('/visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
        Route::put('/visits/{visit}', [VisitController::class, 'update'])->name('visits.update');
        Route::post('/visits/{visit}/complete', [VisitController::class, 'complete'])->name('visits.complete');
        Route::post('/visits/{visit}/treatments', [VisitController::class, 'storeTreatment'])->name('visits.treatments.store');
        Route::delete('/visits/{visit}/treatments/{treatment}', [VisitController::class, 'destroyTreatment'])->name('visits.treatments.destroy');

        Route::put('/visits/{visit}/odontogram', [OdontogramController::class, 'update'])->name('visits.odontogram.update');
        Route::post('/visits/{visit}/odontogram/teeth', [OdontogramController::class, 'updateTooth'])->name('visits.odontogram.teeth.update');
        Route::get('/visits/{visit}/odontogram/compare', [OdontogramController::class, 'compare'])->name('visits.odontogram.compare');

        Route::post('/visits/{visit}/attachments', [AttachmentController::class, 'store'])->name('visits.attachments.store');
        Route::delete('/visits/{visit}/attachments/{attachment}', [AttachmentController::class, 'destroy'])->name('visits.attachments.destroy');

        Route::get('/reports/visits', [ReportController::class, 'visits'])->name('reports.visits');
        Route::get('/reports/queues', [ReportController::class, 'queues'])->name('reports.queues');
        Route::get('/reports/patients', [ReportController::class, 'patients'])->name('reports.patients');
        Route::get('/reports/treatments', [ReportController::class, 'treatments'])->name('reports.treatments');
        Route::get('/reports/service-time', [ReportController::class, 'serviceTime'])->name('reports.service-time');

        Route::middleware('role:admin')->group(function () {
            Route::resource('users', UserController::class)->except(['show']);

            Route::get('/queues/display-settings', [DisplaySettingController::class, 'edit'])->name('queues.display-settings.edit');
            Route::put('/queues/display-settings', [DisplaySettingController::class, 'update'])->name('queues.display-settings.update');

            Route::get('/settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
            Route::put('/settings', [SystemSettingController::class, 'update'])->name('settings.update');
        });
    });

    Route::middleware('role:pasien')->group(function () {
        Route::get('/check-in', [PatientPortalController::class, 'checkIn'])->name('check-in');
        Route::post('/check-in', [PatientPortalController::class, 'checkInSubmit'])->name('check-in.submit');

        Route::prefix('patient')->name('patient.')->group(function () {
            Route::get('/dashboard', [PatientPortalController::class, 'dashboard'])->name('dashboard');
            Route::get('/queue', [PatientPortalController::class, 'queue'])->name('queue');
            Route::post('/queue', [PatientPortalController::class, 'takeQueue'])->name('queue.take');
            Route::get('/queue/status', [PatientPortalController::class, 'queueStatus'])->name('queue.status');
            Route::get('/history', [PatientPortalController::class, 'history'])->name('history');
            Route::get('/odontogram', [PatientPortalController::class, 'odontogram'])->name('odontogram');
            Route::get('/odontogram/compare', [PatientPortalController::class, 'odontogramCompare'])->name('odontogram.compare');
            Route::get('/control-schedules', [PatientPortalController::class, 'controlSchedules'])->name('control-schedules');
            Route::get('/notifications', [PatientPortalController::class, 'notifications'])->name('notifications');
            Route::get('/profile', [PatientPortalController::class, 'profile'])->name('profile');
            Route::put('/profile', [PatientPortalController::class, 'updateProfile'])->name('profile.update');
        });
    });
});
