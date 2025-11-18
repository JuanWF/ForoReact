<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\VoteController;
use App\Http\Controllers\TrendController;

/**
 * RUTAS DEL FORO - ForoDB
 * 
 * EXPLICACIÓN RUTAS INERTIA:
 * - Todas las rutas retornan Inertia::render()
 * - No hay rutas /api/* porque NO es una API REST
 * - Laravel maneja las rutas, Inertia pasa datos a React
 * - React Router NO se usa, todo es manejado por Laravel
 */

// Página principal del foro (feed de posts)
Route::get('/', [PostController::class, 'index'])->name('home');

// Rutas públicas del foro (pueden verse sin login)
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::get('/posts/{id}', [PostController::class, 'show'])->name('posts.show');

// Tendencias
Route::get('/trends', [TrendController::class, 'index'])->name('trends.index');
Route::get('/trends/{slug}', [TrendController::class, 'show'])->name('trends.show');

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    
    // Crear post
    Route::get('/posts/create/new', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    
    // Editar y eliminar post
    Route::put('/posts/{id}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{id}', [PostController::class, 'destroy'])->name('posts.destroy');
    
    // Comentarios
    Route::post('/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{id}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{id}', [CommentController::class, 'destroy'])->name('comments.destroy');
    
    // Votos
    Route::post('/votes', [VoteController::class, 'store'])->name('votes.store');
    Route::delete('/votes/{id}', [VoteController::class, 'destroy'])->name('votes.destroy');
    
    // Dashboard (opcional)
    Route::get('/dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

// Ruta para actualizar tendencias (puede ser un Artisan command también)
Route::post('/trends/refresh', [TrendController::class, 'refresh'])
    ->middleware(['auth'])
    ->name('trends.refresh');

require __DIR__.'/settings.php';
