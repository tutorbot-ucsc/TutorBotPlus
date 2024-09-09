<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problemas;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Cursos;
use App\Models\Casos_Pruebas;
use App\Models\LenguajesProgramaciones;
use App\Models\Categoria_Problema;
use Illuminate\Validation\Rule;

class ProblemasController extends Controller
{
    public function index(Request $request)
    {
        $problemas = Problemas::all()->map(function($problema){
            $problema->fecha = Carbon::parse($problema->created_at)->toFormattedDateString();
            return $problema;
        });
        return view('problemas.index', compact('problemas'));
    }

    public function crear(){
        $categorias = Categoria_Problema::all();
        $cursos = Cursos::all();
        $lenguajes = LenguajesProgramaciones::all();
        return view('problemas.crear', compact('categorias', 'cursos', 'lenguajes'))->with('accion', "crear");;
    }

    public function editar(Request $request){
        $problema = Problemas::find($request->id);
        $categorias = Categoria_Problema::all();
        $cursos = Cursos::all();
        $lenguajes = LenguajesProgramaciones::all();
        return view('problemas.editar', compact('problema','categorias','lenguajes', 'cursos'))->with('accion', "editar");
    }

    public function store(Request $request){
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'rut' => 'required|string|unique:App\Models\User,rut',
            'email' => 'required|email|unique:App\Models\User,email',
            'firstname' => 'string',
            'lastname' => 'string',
            'fecha_nacimiento' => 'date',
            'password' => 'required|min:8|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'required|min:8',
        ]);
        
        db::beginTransaction();
        try{
            $usuario = new Problemas;
            $usuario->username = $request->input('username');
            $usuario->rut = $request->input('rut');
            $usuario->email = $request->input('email');
            $usuario->firstname = $request->input('firstname');
            $usuario->lastname = $request->input('lastname');
            $usuario->fecha_nacimiento = $request->input('fecha_nacimiento');
            $usuario->password = $request->input('password');
            $usuario->save();
            $usuario->cursos()->sync($request->input('cursos'));
            db::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
        $usuario->syncRoles($request->roles);
        return redirect()->route('usuarios.index')->with('success','El usuario ha sido creado');
    }

    public function update(Request $request){
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', Rule::unique('users', 'rut')->ignore($request->id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->id)],
            'firstname' => ['string'],
            'lastname' => ['string'],
            'fecha_nacimiento' => ['date'],
        ]);
        try{
            db::beginTransaction();
            $usuario = Problemas::find($request->id);
            $usuario->username = $request->input('username');
            $usuario->rut = $request->input('rut');
            $usuario->email = $request->input('email');
            $usuario->firstname = $request->input('firstname');
            $usuario->lastname = $request->input('lastname');
            $usuario->fecha_nacimiento = $request->input('fecha_nacimiento');
            $usuario->save();
            $usuario->cursos()->sync($request->input('cursos'));
            db::commit();
        }catch(\Exception $e){
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
        $usuario->syncRoles($request->roles);
        return redirect()->route('usuarios.index')->with('success','El usuario ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        try{
            DB::beginTransaction();
            $problema = Problemas::find($request->id);
            $problema->delete();
            DB::commit();
        }catch(\PDOException $e){
            db::rollBack();
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        } 
        return redirect()->route('problemas.index')->with('success', 'El problema "'.$problema->username.'" ha sido eliminado');
    }
}
