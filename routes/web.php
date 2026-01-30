<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ClientController;

Route::get('/', function () {
    return view('login');
});

Route::get('/dashboard', [ClientController::class, 'index'])->name('dashboard');
Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
Route::get('/clients/{id}/edit', [ClientController::class, 'edit'])->name('clients.edit');
