<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\RecordController;

Route::get('/', [CategoryController::class, 'index']);

Route::resource('categories', CategoryController::class);
Route::get('categories/{category}/items/create', [ItemController::class, 'create'])->name('items.create');
Route::post('categories/{category}/items', [ItemController::class, 'store'])->name('items.store');
Route::get('categories/{category}/records/create', [RecordController::class, 'create'])->name('records.create');
Route::post('categories/{category}/records', [RecordController::class, 'store'])->name('records.store');
