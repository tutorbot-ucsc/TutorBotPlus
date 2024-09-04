<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\RolePermission;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::paginate(15)->through(function($role){
            $role->fecha = carbon::parse($role->created_at)->toFormattedDateString();
            return $role;
        });
        return view('roles.index', compact('roles'));
    }

    public function crear(){
        return view('roles.crear', compact('roles'));
    }

    public function editar(Request $request){
        $rol = Role::find($request->id);
        return view('roles.editar', compact('rol'));
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
            $rol = new Role;
            db::commit();
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('roles.index')->with('error', $e->getMessage());
        }
        return redirect()->route('roles.index')->with('success','El rol ha sido creado');
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
            $usuario = Role::find($request->id);
            db::commit();
        }catch(\Exception $e){
            return redirect()->route('roles.index')->with('error', $e->getMessage());
        }
        return redirect()->route('roles.index')->with('success','El rol ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        try{
            DB::beginTransaction();
            $rol = Role::find($request->id);
            $rol->delete();
            DB::commit();
        }catch(\PDOException $e){
            db::rollBack();
            return redirect()->route('roles.index')->with('error', $e->getMessage());
        } 
        return redirect()->route('roles.index')->with('success', 'El rol '.$rol->name.' ha sido eliminado');
    }
}
