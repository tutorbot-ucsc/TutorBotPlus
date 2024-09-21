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
use App\Http\Controllers\LenguajesProgramacionesController;
use App\Http\Controllers\CategoriaProblemaController;
use App\Http\Controllers\CursosController;
use App\Http\Controllers\ProblemasController;
use App\Http\Controllers\CasosPruebasController;
use App\Http\Controllers\EnvioSolucionProblemaController;
use App\Http\Controllers\EvaluacionSolucionController;
use App\Http\Controllers\LlmController;
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

//Plataforma de Juez Online
Route::group(['middleware'=>'auth'], function(){
	Route::get('/', [CursosController::class, 'listado_cursos'])->name('cursos.listado');
	Route::get('/cursos', function () {
		return redirect()->route('cursos.listado');
	});
	Route::get('/cursos/{id}/problemas', [ProblemasController::class, 'listado_problemas'])->name('problemas.listado');
	Route::get('/problema/{id_curso?}/{codigo}', [ProblemasController::class, 'ver_problema'])->name('problemas.ver');
	Route::get('/problema/{codigo}/editorial', [ProblemasController::class, 'ver_editorial'])->name('problemas.ver_editorial');
	Route::get('/problema/{id_curso?}/{codigo}/resolver', [ProblemasController::class, 'resolver_problema'])->name('problemas.resolver');

	Route::post('/problema/enviar', [EnvioSolucionProblemaController::class, 'enviar_solucion'])->name('problemas.enviar');
	Route::get('/envios/{id_problema?}', [EnvioSolucionProblemaController::class, 'ver_envios'])->name('envios.listado');
	Route::get('/envio/{token}', [EvaluacionSolucionController::class, 'ver_evaluacion'])->name('envios.ver');

	Route::get('/envio/{token}/retroalimentacion', [LlmController::class, 'ver_retroalimentacion'])->name('envios.retroalimentacion');
	Route::get('/retroalimentacion/generar', [LlmController::class, 'generar_retroalimentacion'])->name('envios.generar_retroalimentacion');


});
//Administración
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
	Route::prefix('lenguajes_programacion')->group(function () {
		Route::get('/index', [LenguajesProgramacionesController::class, 'index'])->name('lenguaje_programacion.index')->middleware('can:ver lenguaje de programación'); 
		Route::get('/crear', [LenguajesProgramacionesController::class, 'crear'])->name('lenguaje_programacion.crear')->middleware('can:crear lenguaje de programación'); 
		Route::get('/editar', [LenguajesProgramacionesController::class, 'editar'])->name('lenguaje_programacion.editar')->middleware('can:editar lenguaje de programación'); 
		Route::post('/eliminar', [LenguajesProgramacionesController::class, 'eliminar'])->name('lenguaje_programacion.eliminar')->middleware('can:eliminar lenguaje de programación'); 
		Route::post('/store', [LenguajesProgramacionesController::class, 'store'])->name('lenguaje_programacion.store')->middleware('can:crear lenguaje de programación'); 
		Route::post('/update', [LenguajesProgramacionesController::class, 'update'])->name('lenguaje_programacion.update')->middleware('can:editar lenguaje de programación'); 
	});
	Route::prefix('cursos')->group(function () {
		Route::get('/index', [CursosController::class, 'index'])->name('cursos.index')->middleware('can:ver curso'); 
		Route::get('/crear', [CursosController::class, 'crear'])->name('cursos.crear')->middleware('can:crear curso'); 
		Route::get('/editar', [CursosController::class, 'editar'])->name('cursos.editar')->middleware('can:editar curso'); 
		Route::post('/eliminar', [CursosController::class, 'eliminar'])->name('cursos.eliminar')->middleware('can:eliminar curso'); 
		Route::post('/store', [CursosController::class, 'store'])->name('cursos.store')->middleware('can:crear curso'); 
		Route::post('/update', [CursosController::class, 'update'])->name('cursos.update')->middleware('can:editar curso'); 
	});
	Route::prefix('categorias_problemas')->group(function () {
		Route::get('/index', [CategoriaProblemaController::class, 'index'])->name('categorias.index')->middleware('can:ver categoría de problema'); 
		Route::get('/crear', [CategoriaProblemaController::class, 'crear'])->name('categorias.crear')->middleware('can:crear categoría de problema'); 
		Route::get('/editar', [CategoriaProblemaController::class, 'editar'])->name('categorias.editar')->middleware('can:editar categoría de problema'); 
		Route::post('/eliminar', [CategoriaProblemaController::class, 'eliminar'])->name('categorias.eliminar')->middleware('can:eliminar categoría de problema'); 
		Route::post('/store', [CategoriaProblemaController::class, 'store'])->name('categorias.store')->middleware('can:crear categoría de problema'); 
		Route::post('/update', [CategoriaProblemaController::class, 'update'])->name('categorias.update')->middleware('can:editar categoría de problema'); 
	});
	Route::prefix('problemas')->group(function () {
		Route::get('/index', [ProblemasController::class, 'index'])->name('problemas.index')->middleware('can:ver problemas'); 
		Route::get('/crear', [ProblemasController::class, 'crear'])->name('problemas.crear')->middleware('can:crear problemas'); 
		Route::get('/editar', [ProblemasController::class, 'editar'])->name('problemas.editar')->middleware('can:editar problemas'); 
		Route::post('/eliminar', [ProblemasController::class, 'eliminar'])->name('problemas.eliminar')->middleware('can:eliminar problemas'); 
		Route::post('/store', [ProblemasController::class, 'store'])->name('problemas.store')->middleware('can:crear problemas'); 
		Route::post('/update', [ProblemasController::class, 'update'])->name('problemas.update')->middleware('can:editar problemas'); 
		Route::get('/editar_config_llm', [ProblemasController::class, 'editar_config_llm'])->name('problemas.editar_config_llm')->middleware('can:editar problemas'); 
		Route::post('/configurar_llm', [ProblemasController::class, 'configurar_llm'])->name('problemas.configurar_llm')->middleware('can:editar problemas'); 

		Route::get('/{id}/editorial', [ProblemasController::class, 'editar_editorial'])->name('problemas.editorial')->middleware('can:editar problemas'); 
		Route::post('/editorial/update', [ProblemasController::class, 'update_editorial'])->name('problemas.update_editorial')->middleware('can:editar problemas'); 

		Route::get('/{id}/casos', [CasosPruebasController::class, 'asignacion_casos'])->name('casos_pruebas.assign')->middleware('can:editar problemas'); 
		Route::post('/casos/eliminar', [CasosPruebasController::class, 'eliminar_caso'])->name('casos_pruebas.eliminar')->middleware('can:editar problemas'); 
		Route::post('/casos/add', [CasosPruebasController::class, 'add_caso'])->name('casos_pruebas.add')->middleware('can:editar problemas'); 
		Route::post('/casos/sql', [CasosPruebasController::class, 'caso_sql'])->name('casos_pruebas.set_sql')->middleware('can:editar problemas'); 
	});
});