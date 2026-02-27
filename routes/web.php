<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\AIFormController;
use App\Models\GeneratedForm; // <--- Add this import
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// The AI Generation Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/ai/generate-code', [AIFormController::class, 'generateCode'])->name('ai.generate');
    Route::get('/api/previous-forms', function () {
        return \App\Models\GeneratedForm::where('user_id', Auth::id())
            ->whereNotNull('generated_code')
            ->orderBy('created_at', 'desc')
            ->get();
    })->name('api.previous-forms');
});

// FIXED: The Dashboard Route now passes your form history to Vue
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'initialForms' => \App\Models\GeneratedForm::all()
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
