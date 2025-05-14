<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::post('/admin/contest', [AdminController::class, 'createContest'])->name('admin.contest.create');
Route::get('/admin/contest/{contest}/edit', [AdminController::class, 'editContest'])->name('admin.contest.edit');
Route::put('/admin/contest/{contest}', [AdminController::class, 'updateContest'])->name('admin.contest.update');
Route::post('/admin/contest/{contest}/channel', [AdminController::class, 'addChannel'])->name('admin.channel.add');
Route::delete('/admin/contest/{contest}/channel/{channel}', [AdminController::class, 'removeChannel'])->name('admin.channel.remove');
Route::post('/admin/contest/{contest}/prize', [AdminController::class, 'addPrize'])->name('admin.prize.add');
Route::delete('/admin/contest/{contest}/prize/{prize}', [AdminController::class, 'removePrize'])->name('admin.prize.remove');
Route::get('/admin/contest/{contest}/statistics', [AdminController::class, 'statistics'])->name('admin.statistics');
Route::get('/admin/contest/{contest}/winners', [AdminController::class, 'winners'])->name('admin.winners');
