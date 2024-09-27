<?php

namespace App\Http\Controllers;

use App\Models\Cursos;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all()->map(function($user){
            $user->fecha = Carbon::parse($user->created_at)->locale('es_ES')->isoFormat('lll');
            return $user;
        });
        return view('usuarios.index', compact('users'));
    }

    public function crear(){
        $roles = Role::all();
        $cursos = Cursos::all();
        return view('usuarios.crear', compact('roles', 'cursos'))->with('accion', "crear");;
    }

    public function editar(Request $request){
        $user = User::find($request->id);
        $cursos = Cursos::all();
        $roles = Role::all();
        return view('usuarios.editar', compact('user', 'roles', 'cursos'))->with('accion', "editar");
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
            $usuario = new User;
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
            $usuario = User::find($request->id);
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
            $user = User::find($request->id);
            $user->delete();
            DB::commit();
        }catch(\PDOException $e){
            db::rollBack();
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        } 
        return redirect()->route('usuarios.index')->with('success', 'El usuario '.$user->username.' ha sido eliminado');
    }
}
