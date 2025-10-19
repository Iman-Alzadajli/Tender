<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\InternalTender\InternalTender;
use App\Livewire\ETender\ETender;
use App\Livewire\OtherTenderPlatform\OtherTenderPlatform;
use App\Livewire\Users\Users;
use App\Livewire\ContactList\ContactList;
use App\Livewire\ContactList\FocalPointsList;
use App\Livewire\ContactList\PartnershipsList;
use App\Livewire\Role\RoleManager;

// صفحة تسجيل الدخول
Route::get('/', function () {
    return view('auth.login');
});

// كل الصفحات التي تتطلب تسجيل الدخول يجب أن تكون داخل هذه المجموعة الواحدة
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth', 'can:dashboard.view'])->name('dashboard');

    // Tender Pages
    Route::get('/internal-tender', InternalTender::class)
        ->middleware('can:internal-tenders.view')
        ->name('internal-tender');

    Route::get('/e-tender', ETender::class)
        ->middleware('can:e-tenders.view')
        ->name('e-tender');

    Route::get('/other-tender-platform', OtherTenderPlatform::class)
        ->middleware('can:other-tenders.view')
        ->name('other-tender-platform');

    // Users Page
    Route::get('/users', Users::class)
        ->middleware('can:users.view')
        ->name('users');

    // ✅✅✅ تحديث Contact List Routes لاستخدام الصلاحيات الجديدة ✅✅✅
    Route::get('/contact-list/focal-points', FocalPointsList::class)
        ->name('contact-list.focal-points')
        ->middleware('can:focal-points.view'); // ✅ استخدام الصلاحية الجديدة

    Route::get('/contact-list/partnerships', PartnershipsList::class)
        ->name('contact-list.partnerships')
        ->middleware('can:partnerships.view'); // ✅ استخدام الصلاحية الجديدة

    // Role Management Page
    Route::get('/roles', RoleManager::class)
        ->middleware('can:roles.view')
        ->name('roles');

    // Profile Page
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';