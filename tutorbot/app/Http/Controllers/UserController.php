<?php

namespace App\Http\Controllers;

use App\Models\Cursos;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::all()->map(function ($user) {
            $user->fecha = Carbon::parse($user->created_at)->locale('es_ES')->isoFormat('lll');
            return $user;
        });
        return view('usuarios.index', compact('users'));
    }

    public function crear()
    {
        $roles = Role::all();
        $cursos = Cursos::all();
        return view('usuarios.crear', compact('roles', 'cursos'))->with('accion', "crear");
        ;
    }

    public function bulk_insertion_form(Request $request)
    {
        return view('usuarios.bulk_insert');
    }
    public function bulk_insertion_example(Request $request)
    {
        dd(Storage::exists('public/examples/ejemplo_bulk_usuarios.csv'));
        return Storage::download("public/examples/ejemplo_bulk_usuarios.csv");
    }
    public function bulk_insertion(Request $request)
    {
        $validated = $request->validate([
            'csvFile' => 'required|mimes:csv,txt',
        ]);
        $contenido = file_get_contents($request->file('csvFile')->getRealPath());
        //remover caracteres especiales
        $contenido = str_replace("\u{FEFF}", "", $contenido);
        //dividir el contenido por el separador de break space. Lo que transforma en un array de strings, donde cada elemento es un usuario.
        $string_arrays = explode("\r\n", $contenido);
        try {
            DB::beginTransaction();
            foreach ($string_arrays as $string_info) {
                if($string_info == ""){
                    continue;
                }
                $usuario_data = explode(";", $string_info);
                $usuario_nuevo = new User;
                $usuario_nuevo->username = $usuario_data[0];
                $usuario_nuevo->firstname = $usuario_data[1];
                $usuario_nuevo->lastname = $usuario_data[2];
                $usuario_nuevo->email = $usuario_data[3];
                $usuario_nuevo->rut = $usuario_data[4];
                $usuario_nuevo->fecha_nacimiento = Carbon::parse($usuario_data[5])->toDateTimeString();
                $usuario_nuevo->password = str_replace("-", "", $usuario_data[4]);
                $usuario_nuevo->save();
                $cursos = explode(",",str_replace(["[","]"], "", $usuario_data[6]));
                $roles = explode(",",str_replace(["[","]"], "", strtolower($usuario_data[7])));
                $cursos_modelos = Cursos::whereIn("codigo", $cursos)->get();
                $roles_modelos = Role::whereIn("name", $roles)->get();
                $usuario_nuevo->cursos()->sync($cursos_modelos);
                $usuario_nuevo->roles()->sync($roles_modelos);
                $usuario_nuevo->save();
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return back()->with("error", $e->getMessage());
        }
        return redirect()->route('usuarios.index')->with("success", "Los usuarios han sido creado de manera correcta.");
    }
    public function editar(Request $request)
    {
        $user = User::find($request->id);
        $cursos = Cursos::all();
        $roles = Role::all();
        return view('usuarios.editar', compact('user', 'roles', 'cursos'))->with('accion', "editar");
    }

    public function store(Request $request)
    {
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
        try {
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
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
        $usuario->syncRoles($request->roles);
        return redirect()->route('usuarios.index')->with('success', 'El usuario ha sido creado');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', Rule::unique('users', 'rut')->ignore($request->id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($request->id)],
            'firstname' => ['string'],
            'lastname' => ['string'],
            'fecha_nacimiento' => ['date'],
        ]);
        try {
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
        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
        $usuario->syncRoles($request->roles);
        return redirect()->route('usuarios.index')->with('success', 'El usuario ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        try {
            DB::beginTransaction();
            $user = User::find($request->id);
            $user->delete();
            DB::commit();
        } catch (\PDOException $e) {
            db::rollBack();
            return redirect()->route('usuarios.index')->with('error', $e->getMessage());
        }
        return redirect()->route('usuarios.index')->with('success', 'El usuario ' . $user->username . ' ha sido eliminado');
    }
}
