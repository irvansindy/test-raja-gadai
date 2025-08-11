<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PublicController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [NoteController::class, 'index'])->name('dashboard');
    
    // Notes CRUD
    Route::resource('notes', NoteController::class);
    
    // Note sharing
    Route::post('notes/{note}/share', [NoteController::class, 'shareToUser'])->name('notes.share');
    Route::patch('notes/{note}/public', [NoteController::class, 'togglePublic'])->name('notes.toggle-public');
    
    // Comments
    Route::post('notes/{note}/comments', [CommentController::class, 'store'])->name('comments.store');
});

// Public Routes
Route::get('public/notes/{note}', [PublicController::class, 'show'])->name('public.notes.show');
