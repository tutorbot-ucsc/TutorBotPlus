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
use App\Models\JuecesVirtuales;
use App\Models\EnvioSolucionProblema;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProblemasController extends Controller
{
    public function index(Request $request)
    {
        $problemas = Problemas::whereHas('cursos', function (Builder $query) {
            $cursos = auth()->user()->cursos()->select('cursos.id')->pluck('id')->toArray();
            $query->whereIn('cursos.id', $cursos);
        })->get();
        return view('problemas.index', compact('problemas'));
    }

    public function crear()
    {
        $categorias = Categoria_Problema::all();
        $cursos = Cursos::all();
        $lenguajes = LenguajesProgramaciones::where('abreviatura', 'NOT LIKE', '%sql%')->get();
        return view('problemas.crear', compact('categorias', 'cursos', 'lenguajes'))->with('accion', "crear");;
    }

    public function editar(Request $request)
    {
        $problema = Problemas::find($request->id);
        $categorias = Categoria_Problema::all();
        $cursos = Cursos::all();
        $lenguajes = LenguajesProgramaciones::where('abreviatura', 'NOT LIKE', '%sql%')->get();
        return view('problemas.editar', compact('problema', 'categorias', 'lenguajes', 'cursos'))->with('accion', "editar");
    }
    public function editar_config_llm(Request $request)
    {
        $problema = Problemas::find($request->id);
        return view('problemas.llm_config', compact('problema'));
    }

    public function configurar_llm(Request $request)
    {
        $validated = $request->validate(Problemas::$llm_config_rules);
        try {
            $problema = Problemas::find($request->id);
            if (isset($request->habilitar_llm)) {
                $problema->habilitar_llm = true;
            } else {
                $problema->habilitar_llm = false;
            }
            $problema->limite_llm = $request->input('limite_llm');
            $problema->save();
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->route("problemas.configurar_llm", ["id" => $request->id])->with("error", $e->getMessage());
        }
        return redirect()->route("problemas.index")->with("success", "Se ha configurado la Large Language Model en el problema " . $problema->codigo . " correctamente");
    }

    public function store(Request $request)
    {
        $validated = $request->validate(Problemas::$createRules);
        try {
            db::beginTransaction();
            $problema = new Problemas;
            $this->problemaModificacion($problema, $request);
            db::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        return redirect()->route('casos_pruebas.assign', ["id" => $problema->id])->with('success', 'El Problema ' . $problema->nombre . ' ha sido creado, ingrese los casos de prueba.');
    }
    private static function problemaModificacion(Problemas $problema, Request $request){
            $problema->nombre = $request->input('nombre');
            $problema->codigo = $request->input('codigo');
            $problema->fecha_inicio = $request->input('fecha_inicio');
            $problema->fecha_termino = $request->input('fecha_termino');
            $problema->memoria_limite = $request->input('memoria_limite');
            $problema->tiempo_limite = $request->input('tiempo_limite');
            $problema->body_problema = $request->input('body_problema');
            $problema->body_problema_resumido = $request->input('body_problema_resumido');
            if (isset($request->visible)) {
                $problema->visible = true;
            } else {
                $problema->visible = false;
            }

            if (isset($request->habilitar_llm)) {
                $problema->habilitar_llm = true;
            } else {
                $problema->habilitar_llm = false;
            }
            $problema->limite_llm = $request->input('limite_llm');
            if(isset($request->archivos_adicionales)){
                $archivo = $request->file('archivos_adicionales');
                $nombre = $archivo->hashName();
                $request->file('archivos_adicionales')->storeAs(
                    'archivos_adicionales',$nombre,'public'
                );
                if(isset($problema->archivo_adicional)){
                    Storage::delete('public/archivos_adicionales/'.$problema->archivo_adicional);
                }
                $problema->archivo_adicional = $nombre;
            }
            $problema->save();
            $problema->cursos()->sync($request->input('cursos'));
            if($request->sql==true){
                $problema->lenguajes()->sync(LenguajesProgramaciones::where('abreviatura', '=', 'sql')->get()->pluck('id'));
            }else{
                $problema->lenguajes()->sync($request->input('lenguajes'));
            }
            $problema->categorias()->sync($request->input('categorias'));
    }
    public function update(Request $request)
    {

        $validated = $request->validate(Problemas::updateRules($request->codigo));
        try {
            db::beginTransaction();
            $problema = Problemas::find($request->id);
            $this->problemaModificacion($problema, $request);
            db::commit();
        } catch (\Exception $e) {
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        return redirect()->route('problemas.index')->with('success', 'El problema ha sido modificado');
    }
    public function eliminar(Request $request)
    {
        try {
            DB::beginTransaction();
            $problema = Problemas::find($request->id);
            $problema->delete();
            Storage::delete('public/archivos_adicionales/'.$problema->archivo_adicional);
            DB::commit();
        } catch (\PDOException $e) {
            db::rollBack();
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        return redirect()->route('problemas.index')->with('success', 'El problema "' . $problema->nombre . '" ha sido eliminado');
    }

    public function update_editorial(Request $request)
    {
        try {
            $problema = Problemas::find($request->id);
            $problema->body_editorial = $request->input('body_editorial');
            $problema->save();
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->back()->withInput($request->input())->with('error', $e->getMessage());
        }
        return redirect()->route('problemas.index')->with('success', 'El editorial para el problema "' . $problema->nombre . '" ha sido modificado');
    }

    public function editar_editorial(Request $request)
    {
        try {
            $problema = Problemas::find($request->id);
        } catch (\PDOException $e) {
            db::rollBack();
            return redirect()->route('problemas.index')->with('error', $e->getMessage());
        }
        return view('problemas.editorial', compact('problema'));
    }

    public function listado_problemas(Request $request)
    {
        try {
            if (!auth()->user()->cursos()->get()->contains($request->id)) {
                return redirect()->route('cursos.listados')->with('error', 'No tienes acceso al curso que estas tratando de acceder');
            }
            $curso = Cursos::find($request->id);
            $problemas = $curso->problemas()->where('visible', '=', true)->orderBy('created_at', 'DESC')->get()->map(function ($problema) {
                $problema->puntaje_total = $problema->casos_de_prueba()->get()->pluck('puntos')->sum();
                $problema->categorias = implode(',', $problema->categorias()->get()->pluck('nombre')->toArray());
                return $problema;
            });
        } catch (\PDOException $e) {
            DB::rollBack();
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.index', compact('problemas', 'curso'));
    }

    public function ver_problema(Request $request)
    {
        try {
            $problema = Problemas::where('codigo', '=', $request->codigo)->first();
            $cursos_usuario = auth()->user()->cursos()->get()->pluck('id')->toArray();
            if (!$problema->cursos()->whereIn('cursos.id', $cursos_usuario)->exists() || $problema->visible == false) {
                return redirect()->route('cursos.listado')->with('error', 'No tienes acceso al problema ' . $problema->nombre);
            }
            $problema->disponible = true;
            if (isset($problema->fecha_termino)) {
                $now = Carbon::now();
                $fecha_termino = Carbon::parse($problema->fecha_termino);
                if ($now->gt($fecha_termino)) {
                    $problema->disponible = false;
                }
            }
        } catch (\PDOException $e) {
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.ver_problema', compact('problema'))->with('id_curso', $request->id_curso);
    }
    public function ver_editorial(Request $request)
    {
        try {
            $problema = Problemas::where('codigo', '=', $request->codigo)->first();
            $cursos_usuario = auth()->user()->cursos()->get()->pluck('id')->toArray();
            if (!$problema->cursos()->whereIn('cursos.id', $cursos_usuario)->exists() || $problema->visible == false) {
                return redirect()->route('cursos.listado')->with('error', 'No tienes acceso al problema ' . $problema->nombre);
            }
            if (isset($problema->fecha_termino)) {
                $now = Carbon::now();
                $fecha_termino = Carbon::parse($problema->fecha_termino);
                if ($now->gt($fecha_termino)) {
                    return redirect()->route('cursos.listado')->with('error', 'El problema ' . $problema->nombre . 'no está disponible');
                }
            }
        } catch (\PDOException $e) {
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.ver_editorial', compact('problema'));
    }

    public function resolver_problema(Request $request)
    {
        try {
            $problema = Problemas::where('codigo', '=', $request->codigo)->first();
            $lenguajes = $problema->lenguajes()->get();
            $jueces = JuecesVirtuales::all();
            $last_envio = auth()->user()->envios()->where('id_problema', '=', $problema->id)->orderBy('created_at', 'DESC');
            if(isset($request->id_curso)){
                $last_envio = $last_envio->where('id_curso', '=', $request->id_curso);
            }
            $last_envio = $last_envio->first();
            $codigo = null;
            if (isset($last_envio->termino) || !isset($last_envio)) {
                DB::beginTransaction();
                $envio = new EnvioSolucionProblema;
                $envio->token = Str::random(40);
                $envio->problema()->associate($problema);
                $envio->usuario()->associate(auth()->user());
                if(isset($request->id_curso)){
                    $envio->curso()->associate(Cursos::find($request->id_curso));
                    DB::table('disponible')->where('id_curso', '=', $request->id_curso)->where('id_problema', '=', $problema->id)->increment('cantidad_intentos');
                }
                if(isset($last_envio->termino) && $last_envio->solucionado==false){
                    $codigo = $last_envio->codigo;
                    $envio->codigo = $codigo;
                    $envio->inicio = $last_envio->inicio;
                }
                $envio->save();
                $problema->save();
                DB::commit();
            }else if(isset($last_envio)){
                $codigo = $last_envio->codigo;
            }
        } catch (\PDOException $e) {
            return redirect()->route('cursos.listado')->with('error', $e->getMessage());
        }
        return view('plataforma.problemas.resolver_problema', compact('problema', 'lenguajes', 'jueces', 'codigo'))->with('id_curso', $request->id_curso);
    }
}
