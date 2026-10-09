<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Api;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Contractor;
use App\Http\Controllers\PublicSite;
use App\Http\Controllers\Staff;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Website
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicSite\HomeController::class, 'index'])->name('public.home');
Route::get('/about', [PublicSite\AboutController::class, 'index'])->name('public.about');
Route::get('/tenders', [PublicSite\TenderController::class, 'index'])->name('public.tenders.index');
Route::get('/tenders/{tender}', [PublicSite\TenderController::class, 'show'])->name('public.tenders.show');
Route::get('/contractor/register', [PublicSite\ContractorRegistrationController::class, 'create'])->name('contractor.register');
Route::post('/contractor/register', [PublicSite\ContractorRegistrationController::class, 'store'])->name('contractor.register.store');
Route::get('/infrastructure', [PublicSite\InfrastructureDirectoryController::class, 'index'])->name('public.infrastructure');
Route::get('/bridge/{bridge}', [PublicSite\BridgeShowController::class, 'show'])->name('public.bridge');
Route::get('/report-issue', [PublicSite\ReportIssueController::class, 'create'])->name('public.report.create');
Route::post('/report-issue', [PublicSite\ReportIssueController::class, 'store'])->name('public.report.store');
Route::get('/track', [PublicSite\TrackController::class, 'index'])->name('public.track');
Route::get('/track/{requestNumber}', [PublicSite\TrackController::class, 'show'])->name('public.track.show');

    // AJAX endpoint for public report-issue form
    Route::get('/infrastructure/ajax', [PublicSite\InfrastructureDirectoryController::class, 'ajaxByType'])->name('public.infrastructure.ajax');

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [Auth\LoginController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [Auth\LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Notifications (any authenticated role)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'read'])->name('notifications.read');
    Route::patch('/notifications/{notification}', [\App\Http\Controllers\NotificationController::class, 'update'])->name('notifications.update');
    Route::patch('/notifications/read-all', [\App\Http\Controllers\NotificationController::class, 'markAll'])->name('notifications.mark-all');
});

/*
|--------------------------------------------------------------------------
| Admin Panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Bridges
    Route::get('/bridges/create', [Admin\BridgeController::class, 'create'])->name('bridges.create');
    Route::post('/bridges', [Admin\BridgeController::class, 'store'])->name('bridges.store');
    Route::get('/bridges/{bridge}', [Admin\BridgeController::class, 'show'])->name('bridges.show');
    Route::get('/bridges/{bridge}/edit', [Admin\BridgeController::class, 'edit'])->name('bridges.edit');
    Route::put('/bridges/{bridge}', [Admin\BridgeController::class, 'update'])->name('bridges.update');
    Route::delete('/bridges/{bridge}', [Admin\BridgeController::class, 'destroy'])->name('bridges.destroy');
    Route::get('/bridges/{bridge}/history', [Admin\BridgeController::class, 'history'])->name('bridges.history');
    Route::get('/bridges', [Admin\BridgeController::class, 'index'])->name('bridges.index');

    // Roads
    Route::get('/roads/create', [Admin\RoadController::class, 'create'])->name('roads.create');
    Route::post('/roads', [Admin\RoadController::class, 'store'])->name('roads.store');
    Route::get('/roads/{road}', [Admin\RoadController::class, 'show'])->name('roads.show');
    Route::get('/roads/{road}/edit', [Admin\RoadController::class, 'edit'])->name('roads.edit');
    Route::put('/roads/{road}', [Admin\RoadController::class, 'update'])->name('roads.update');
    Route::delete('/roads/{road}', [Admin\RoadController::class, 'destroy'])->name('roads.destroy');
    Route::get('/roads', [Admin\RoadController::class, 'index'])->name('roads.index');

    // Buildings
    Route::get('/buildings/create', [Admin\BuildingController::class, 'create'])->name('buildings.create');
    Route::post('/buildings', [Admin\BuildingController::class, 'store'])->name('buildings.store');
    Route::get('/buildings/{building}', [Admin\BuildingController::class, 'show'])->name('buildings.show');
    Route::get('/buildings/{building}/edit', [Admin\BuildingController::class, 'edit'])->name('buildings.edit');
    Route::put('/buildings/{building}', [Admin\BuildingController::class, 'update'])->name('buildings.update');
    Route::delete('/buildings/{building}', [Admin\BuildingController::class, 'destroy'])->name('buildings.destroy');
    Route::get('/buildings', [Admin\BuildingController::class, 'index'])->name('buildings.index');

    // Hospitals
    Route::get('/hospitals/create', [Admin\HospitalController::class, 'create'])->name('hospitals.create');
    Route::post('/hospitals', [Admin\HospitalController::class, 'store'])->name('hospitals.store');
    Route::get('/hospitals/{hospital}', [Admin\HospitalController::class, 'show'])->name('hospitals.show');
    Route::get('/hospitals/{hospital}/edit', [Admin\HospitalController::class, 'edit'])->name('hospitals.edit');
    Route::put('/hospitals/{hospital}', [Admin\HospitalController::class, 'update'])->name('hospitals.update');
    Route::delete('/hospitals/{hospital}', [Admin\HospitalController::class, 'destroy'])->name('hospitals.destroy');
    Route::get('/hospitals', [Admin\HospitalController::class, 'index'])->name('hospitals.index');

    // All infrastructure
    Route::get('/infrastructures', [Admin\InfrastructureController::class, 'index'])->name('infrastructures.index');

    // Maintenance
    Route::get('/maintenance/create', [Admin\MaintenanceRequestController::class, 'create'])->name('maintenance.create');
    Route::get('/maintenance/history', [Admin\MaintenanceRequestController::class, 'index'])->name('maintenance.history');
    Route::post('/maintenance', [Admin\MaintenanceRequestController::class, 'store'])->name('maintenance.store');
    Route::get('/maintenance/{maintenance}', [Admin\MaintenanceRequestController::class, 'show'])->name('maintenance.show');
    Route::get('/maintenance/{maintenance}/edit', [Admin\MaintenanceRequestController::class, 'edit'])->name('maintenance.edit');
    Route::put('/maintenance/{maintenance}', [Admin\MaintenanceRequestController::class, 'update'])->name('maintenance.update');
    Route::delete('/maintenance/{maintenance}', [Admin\MaintenanceRequestController::class, 'destroy'])->name('maintenance.destroy');
    Route::post('/maintenance/{maintenance}/assign', [Admin\MaintenanceRequestController::class, 'assign'])->name('maintenance.assign');
    Route::post('/maintenance/{maintenance}/updates', [Admin\MaintenanceRequestController::class, 'addUpdate'])->name('maintenance.updates');
    Route::get('/maintenance', [Admin\MaintenanceRequestController::class, 'index'])->name('maintenance.index');

    // Contractors (replaces the former Maintenance Staff page)
    Route::get('/contractors', [Admin\ContractorController::class, 'index'])->name('contractors.index');
    Route::get('/contractors/{contractor}', [Admin\ContractorController::class, 'show'])->name('contractors.show');
    Route::post('/contractors/{contractor}/approve', [Admin\ContractorController::class, 'approve'])->name('contractors.approve');
    Route::post('/contractors/{contractor}/reject', [Admin\ContractorController::class, 'reject'])->name('contractors.reject');
    Route::patch('/contractors/{contractor}/toggle-status', [Admin\ContractorController::class, 'toggleStatus'])->name('contractors.toggle-status');
    Route::get('/contractors/{contractor}/documents/{field}', [Admin\ContractorController::class, 'document'])->name('contractors.document');

    // Tenders & bids
    Route::get('/tenders', [Admin\TenderController::class, 'index'])->name('tenders.index');
    Route::get('/tenders/create', [Admin\TenderController::class, 'create'])->name('tenders.create');
    Route::post('/tenders', [Admin\TenderController::class, 'store'])->name('tenders.store');
    Route::get('/tenders/{tender}', [Admin\TenderController::class, 'show'])->name('tenders.show');
    Route::get('/tenders/{tender}/edit', [Admin\TenderController::class, 'edit'])->name('tenders.edit');
    Route::put('/tenders/{tender}', [Admin\TenderController::class, 'update'])->name('tenders.update');
    Route::post('/tenders/{tender}/publish', [Admin\TenderController::class, 'publish'])->name('tenders.publish');
    Route::post('/tenders/{tender}/close', [Admin\TenderController::class, 'close'])->name('tenders.close');
    Route::post('/tenders/{tender}/award', [Admin\TenderController::class, 'award'])->name('tenders.award');
    Route::delete('/tenders/{tender}', [Admin\TenderController::class, 'destroy'])->name('tenders.destroy');
    Route::get('/tenders/{tender}/bids/{bid}/documents/{index}', [Admin\TenderController::class, 'downloadBidDocument'])->name('tenders.bids.document');

    // Cancellation requests
    Route::get('/cancellations', [Admin\CancellationRequestController::class, 'index'])->name('cancellations.index');
    Route::get('/cancellations/create', [Admin\CancellationRequestController::class, 'create'])->name('cancellations.create');
    Route::post('/cancellations', [Admin\CancellationRequestController::class, 'store'])->name('cancellations.store');
    Route::get('/cancellations/{cancellation}', [Admin\CancellationRequestController::class, 'show'])->name('cancellations.show');
    Route::post('/cancellations/{cancellation}/decision', [Admin\CancellationRequestController::class, 'decide'])->name('cancellations.decision');
    Route::post('/cancellations/{cancellation}/withdraw', [Admin\CancellationRequestController::class, 'withdraw'])->name('cancellations.withdraw');

    // Infrastructure categories ("All Infrastructure" → Add More)
    Route::get('/infrastructure-categories', [Admin\InfrastructureTypeController::class, 'index'])->name('types.index');
    Route::post('/infrastructure-categories', [Admin\InfrastructureTypeController::class, 'store'])->name('types.store');
    Route::put('/infrastructure-categories/{type}', [Admin\InfrastructureTypeController::class, 'update'])->name('types.update');
    Route::patch('/infrastructure-categories/{type}/toggle-status', [Admin\InfrastructureTypeController::class, 'toggleStatus'])->name('types.toggle-status');
    Route::delete('/infrastructure-categories/{type}', [Admin\InfrastructureTypeController::class, 'destroy'])->name('types.destroy');

    // Contacts
    Route::get('/contacts/create', [Admin\ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [Admin\ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contacts/{contact}/edit', [Admin\ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{contact}', [Admin\ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [Admin\ContactController::class, 'destroy'])->name('contacts.destroy');
    Route::get('/contacts', [Admin\ContactController::class, 'index'])->name('contacts.index');

    // Reports
    Route::get('/reports/infrastructure', [Admin\ReportController::class, 'infrastructure'])->name('reports.infrastructure');
    Route::get('/reports/maintenance', [Admin\ReportController::class, 'maintenance'])->name('reports.maintenance');
    Route::get('/reports/cost', [Admin\ReportController::class, 'cost'])->name('reports.cost');

    // Users
    Route::get('/users/create', [Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users', [Admin\UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [Admin\UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [Admin\UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::get('/users/admins', [Admin\UserController::class, 'admins'])->name('users.admins');
    Route::get('/users/staff', [Admin\UserController::class, 'staff'])->name('users.staff');
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');

    // System
    Route::get('/settings', [Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    Route::get('/activity-logs', [Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Global search (AJAX)
    Route::get('/search', [Api\SearchController::class, 'global'])->name('search.global');
});

/*
|--------------------------------------------------------------------------
| Staff Panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/tasks/{task}', [Staff\TaskController::class, 'show'])->name('tasks.show');
    Route::put('/tasks/{task}', [Staff\TaskController::class, 'update'])->name('tasks.update');
    Route::get('/tasks', [Staff\TaskController::class, 'index'])->name('tasks.index');
});

/*
|--------------------------------------------------------------------------
| Contractor Panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:contractor'])->prefix('contractor')->name('contractor.')->group(function () {
    Route::get('/dashboard', [Contractor\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [Contractor\ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [Contractor\ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/documents/{field}', [Contractor\ProfileController::class, 'document'])->name('profile.document');

    // Protected features — blocked for pending / rejected contractors on the server side.
    Route::middleware('contractor.approved')->group(function () {
        Route::get('/tenders', [Contractor\TenderController::class, 'index'])->name('tenders.index');
        Route::get('/tenders/{tender}', [Contractor\TenderController::class, 'show'])->name('tenders.show');
        Route::post('/tenders/{tender}/bids', [Contractor\TenderController::class, 'bid'])->name('tenders.bids.store');
        Route::get('/bids', [Contractor\BidController::class, 'index'])->name('bids.index');
        Route::get('/bids/{bid}/documents/{index}', [Contractor\TenderController::class, 'downloadDocument'])->name('bids.document');

        Route::get('/cancellations', [Contractor\CancellationRequestController::class, 'index'])->name('cancellations.index');
        Route::get('/cancellations/create', [Contractor\CancellationRequestController::class, 'create'])->name('cancellations.create');
        Route::post('/cancellations', [Contractor\CancellationRequestController::class, 'store'])->name('cancellations.store');
        Route::get('/cancellations/{cancellation}', [Contractor\CancellationRequestController::class, 'show'])->name('cancellations.show');
        Route::post('/cancellations/{cancellation}/decision', [Contractor\CancellationRequestController::class, 'decide'])->name('cancellations.decision');
        Route::post('/cancellations/{cancellation}/withdraw', [Contractor\CancellationRequestController::class, 'withdraw'])->name('cancellations.withdraw');
    });
});
