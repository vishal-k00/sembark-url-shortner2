<?php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ShortUrlController;
use Illuminate\Support\Facades\Route;




Route::get('/login',[AuthenticatedSessionController::class,'create'])->name('login')->middleware('guest');
Route::post('/login',[AuthenticatedSessionController::class,'store'])->name('login.store')->middleware(['guest','throttle:5,1']);
Route::post('/logout',[AuthenticatedSessionController::class,'destroy'])->name('logout')->middleware('auth');
Route::get('/invite/{token}',[InvitationController::class,'acceptForm'])->name('invitations.accept.form');
Route::post('/invite/{token}',[InvitationController::class,'accept'])->name('invitations.accept');
Route::middleware('auth')->group(function(){
 Route::get('/',DashboardController::class)->name('dashboard');
 Route::post('/short-urls',[ShortUrlController::class,'store'])->middleware('throttle:30,1')->name('short-urls.store');
 Route::post('/invitations',[InvitationController::class,'store'])->middleware('throttle:10,1')->name('invitations.store');
  Route::get('/short-urls/export', [
        ShortUrlController::class,
        'export',
    ])->name('short-urls.export');

 });
// Keep the catch-all route last so it cannot shadow application routes.
Route::get('/{code}',[ShortUrlController::class,'redirect'])->where('code','[A-Za-z0-9]{8}')->name('short-urls.redirect');
