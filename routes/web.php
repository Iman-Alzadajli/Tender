<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


use App\Livewire\InternalTender\InternalTender;
use App\Livewire\ETender\ETender;
use App\Livewire\OtherTenderPlatform\OtherTenderPlatform;
use App\Http\Controllers\TenderDashboardController;

use App\Livewire\Dashboard\Dashboard;







Route::get('/', function () { return view('auth.login'); }); 



// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


// Route::get('/dashboard', Dashboard::class)->middleware('auth')->name('dashboard');


Route::get('/dashboard', [Dashboard::class, 'index'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';



// the 3 pages 


Route::middleware(['auth'])->group(function () {
    Route::get('/internal-tender', InternalTender::class)->name('internal-tender');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/e-tender', ETender::class)->name('e-tender');
});






Route::get('/other-tender-platform', OtherTenderPlatform::class)->name('other-tender-platform');

//excel 

// Route::get('/othertenderplatform/ExcelOther', [OtherTenderPlatform::class, 'exportSimpleExcel'])->name('export.other.excel');


