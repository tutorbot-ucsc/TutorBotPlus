<?php

use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;
use App\Http\Controllers\RoleController;

Route::get('/', function () {return redirect('/dashboard');})->middleware('auth');
	Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
	Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
	Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
	Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
	Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
	Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
	Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
	Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');
	Route::get('/dashboard', [HomeController::class, 'index'])->name('home')->middleware('auth');
Route::group(['middleware' => 'auth'], function () {
	Route::get('/profile', [UserProfileController::class, 'show'])->name('profile');
	Route::post('/profile', [UserProfileController::class, 'update'])->name('profile.update');
	Route::get('/profile-static', [PageController::class, 'profile'])->name('profile-static'); 
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');
	Route::prefix('usuarios')->group(function () {
		Route::get('/index', [UserController::class, 'index'])->name('usuarios.index')->middleware('can:ver usuario'); 
		Route::get('/crear', [UserController::class, 'crear'])->name('usuarios.crear')->middleware('can:crear usuario'); 
		Route::get('/editar', [UserController::class, 'editar'])->name('usuarios.editar')->middleware('can:editar usuario'); 
		Route::post('/eliminar', [UserController::class, 'eliminar'])->name('usuarios.eliminar')->middleware('can:eliminar usuario'); 
		Route::post('/store', [UserController::class, 'store'])->name('usuarios.store')->middleware('can:crear usuario'); 
		Route::post('/update', [UserController::class, 'update'])->name('usuarios.update')->middleware('can:editar usuario'); 
	});
	Route::prefix('roles')->group(function () {
		Route::get('/index', [RoleController::class, 'index'])->name('roles.index')->middleware('can:ver rol'); 
		Route::get('/crear', [RoleController::class, 'crear'])->name('roles.crear')->middleware('can:crear rol'); 
		Route::get('/editar', [RoleController::class, 'editar'])->name('roles.editar')->middleware('can:editar rol'); 
		Route::post('/eliminar', [RoleController::class, 'eliminar'])->name('roles.eliminar')->middleware('can:eliminar rol'); 
		Route::post('/store', [RoleController::class, 'store'])->name('roles.store')->middleware('can:crear rol'); 
		Route::post('/update', [RoleController::class, 'update'])->name('roles.update')->middleware('can:editar rol'); 
	});
});