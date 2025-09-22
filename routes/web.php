<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Livewire\Dashboard\Dashboard;
use App\Livewire\InternalTender\InternalTender;
use App\Livewire\ETender\ETender;
use App\Livewire\OtherTenderPlatform\OtherTenderPlatform;
use App\Livewire\Users\Users;
use App\Livewire\ContactList\ContactList;
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
})->middleware('auth')->name('dashboard');

    // Tender Pages
    Route::get('/internal-tender', InternalTender::class)->name('internal-tender');
    Route::get('/e-tender', ETender::class)->name('e-tender');
    Route::get('/other-tender-platform', OtherTenderPlatform::class)->name('other-tender-platform');

    // Users Page
    Route::get('/users', Users::class)->name('users');

    // Contact List Page
    Route::get('/contact-list', ContactList::class)->name('contact-list');

    // Role Management Page
    Route::get('/roles', RoleManager::class)->name('roles');

    


    // Profile Page
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ✅✅✅ تأكد من وجود هذا السطر في نهاية الملف ✅✅✅
require __DIR__ . '/auth.php';
