<?php

namespace App\Http\Controllers;

use App\Models\Cursos;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Validator;
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
        $keys_array = array('username', 'firstname', 'lastname', 'email', 'rut','fecha_nacimiento', 'cursos', 'roles');
        try {
            DB::beginTransaction();
            foreach ($string_arrays as $key=>$string_info) {
                if($string_info == ""){
                    continue;
                }
                $usuario_data = array_filter(explode(";", $string_info));

                if(sizeof($usuario_data)<8){
                    throw new \Exception("Faltan datos en el usuario de la columna ".($key+1).": [".$string_info."]. Ingrese nuevamente el archivo corregido y asegurese de que todos los datos necesarios estén presentes.");
                }
                $usuario_data = array_combine($keys_array, $usuario_data);
                $usuario_data["cursos"] = array_filter(explode(",",str_replace(["[","]"], "", $usuario_data["cursos"])));
                $usuario_data["roles"] = array_filter(explode(",",str_replace(["[","]"], "", strtolower($usuario_data["roles"]))));
                $validator = Validator::make($usuario_data, [
                    'username' => 'required|string|max:255',
                    'rut' => 'required|string|unique:App\Models\User,rut',
                    'email' => 'required|email|unique:App\Models\User,email',
                    'firstname' => 'required|string',
                    'lastname' => 'required|string',
                    'fecha_nacimiento' => 'date',
                    'cursos'=> "array|min:1",
                    'roles' => 'array|min:1',
                ]);
                if ($validator->fails()) {
                    throw new \Exception("Error en la validación del usuario de la columna ".($key+1).":\n[".$string_info."].\n ".implode(",",$validator->messages()->all()));
                }
                $usuario_nuevo = new User;
                $usuario_nuevo->username = $usuario_data["username"];
                $usuario_nuevo->firstname = $usuario_data["firstname"];
                $usuario_nuevo->lastname = $usuario_data["lastname"];
                $usuario_nuevo->email = $usuario_data["email"];
                $usuario_nuevo->rut = $usuario_data["rut"];
                $usuario_nuevo->fecha_nacimiento = Carbon::parse($usuario_data["fecha_nacimiento"])->toDateTimeString();
                $usuario_nuevo->password = str_replace("-", "", $usuario_data["rut"]);
                $usuario_nuevo->save();
                $cursos_modelos = Cursos::whereIn("codigo", $usuario_data["cursos"])->get();
                $roles_modelos = Role::whereIn("name", $usuario_data["roles"])->get();
                $usuario_nuevo->cursos()->sync($cursos_modelos);
                $usuario_nuevo->roles()->sync($roles_modelos);
                $usuario_nuevo->save();
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return back()->with("error", $e->getMessage());
        }catch(Exception $e){
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
