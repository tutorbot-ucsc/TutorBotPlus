<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certamenes;
use Illuminate\Support\Facades\DB;
use App\Models\Cursos;
use App\Models\ResolucionCertamenes;
use App\Models\SeleccionProblemasCertamenes;
use App\Models\Problemas;
use App\Models\EnvioSolucionProblema;
use App\Models\Resolver;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Str;

class CertamenesController extends Controller
{
    public function index(Request $request){
        $cursos_auth = auth()->user()->cursos()->get()->pluck('id')->toArray();
        $certamenes = Certamenes::whereIn('id_curso', $cursos_auth)->get()->map(function ($item){
            $item->fecha_inicio = Carbon::parse($item->fecha_inicio)->locale('es_ES')->isoFormat('lll');
            $item->fecha_termino = Carbon::parse($item->fecha_termino)->locale('es_ES')->isoFormat('lll');
            $item->creado = Carbon::parse($item->created_at)->locale('es_ES')->isoFormat('lll');
            return $item;
        });
        return view('certamen.index', compact('certamenes'));
    }

    public function crear(Request $request){
        $cursos = auth()->user()->cursos()->get();
        return view('certamen.crear', compact('cursos'));
    }
    public function editar(Request $request){
        $cursos = auth()->user()->cursos()->get();
        $certamen = Certamenes::find($request->id);
        $certamen->fecha_inicio = Carbon::parse($certamen->fecha_inicio);
        $certamen->fecha_termino = Carbon::parse($certamen->fecha_termino);
        return view('certamen.editar', compact('cursos', 'certamen'));
    }
    public function store(Request $request){
        $validated = $request->validate(Certamenes::$rules);
        try{
            DB::beginTransaction();
            $certamen = new Certamenes;
            $certamen->nombre = $request->input("nombre");
            $certamen->descripcion = $request->input("descripcion");
            $certamen->fecha_inicio = Carbon::parse($request->input("fecha_inicio"));
            $certamen->fecha_termino = Carbon::parse($request->input("fecha_termino"));
            $certamen->penalizacion_error = $request->input("penalizacion_error");
            $certamen->curso()->associate(Cursos::find($request->curso));
            $certamen->save();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return back()->withInput()->with("error", $e->getMessage());
        }
        return redirect()->route('certamen.banco_problemas', ['id_certamen'=>$certamen->id])->with('success', 'La evaluación "'.$certamen->nombre.'" ha sido creado.');
    }

    public function update(Request $request){
        $validated = $request->validate(Certamenes::$rules);
        try{
            DB::beginTransaction();
            $certamen = Certamenes::find($request->id);
            $certamen->nombre = $request->input("nombre");
            $certamen->descripcion = $request->input("descripcion");
            $certamen->fecha_inicio = Carbon::parse($request->input("fecha_inicio"));
            $certamen->fecha_termino = Carbon::parse($request->input("fecha_termino"));
            $certamen->penalizacion_error = $request->input("penalizacion_error");
            if($certamen->curso->id != $request->input('curso')){
                $certamen->curso()->dissociate();
                $certamen->curso()->associate(Cursos::find($request->input("curso")));
            }
            $certamen->save();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return back()->withInput()->with("error", $e->getMessage());
        }
        return redirect()->route('certamen.index')->with('success', "La evaluación ha sido actualizado.");
    }

    public function eliminar(Request $request){
        try{
            DB::beginTransaction();
            $certamen = Certamenes::find($request->id);
            $certamen->delete();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return back()->with("error", $e->getMessage());
        }
        return redirect()->route('certamen.index')->with('success', 'La evaluación "'.$certamen->nombre.'" ha sido eliminado.');
    }

    public function listado_certamenes(Request $request){
        try{
            $cursos_usuario = auth()->user()->cursos()->pluck('cursos.id');
            $evaluaciones = Certamenes::whereIn('id_curso', $cursos_usuario)->orderBy('fecha_inicio', 'desc')->get()->map(function($item){
                $item->fecha_inicio = Carbon::parse( $item->fecha_inicio)->locale('es_ES')->isoFormat('lll');
                $item->fecha_termino = Carbon::parse( $item->fecha_termino)->locale('es_ES')->isoFormat('lll');
                return $item;
            });
        }catch(\PDOException $e){
            return redirect()->route('cursos.listado')->with("error", $e->getMessage());
        }
        return view('plataforma.certamen.index', compact('evaluaciones'));
    }

    public function ver_certamen(Request $request){
        try{
            $certamen = Certamenes::find($request->id_certamen);
            $_now = Carbon::now();
            $certamen->disponibilidad = true;
            if(!($_now->gte(Carbon::parse($certamen->fecha_inicio)) && $_now->lte(Carbon::parse($certamen->fecha_termino)))){
                $certamen->disponibilidad = false;
            }
            $res_certamen = $certamen->resoluciones()->where('id_usuario', '=', auth()->user()->id)->first();
            $certamen->fecha_inicio = Carbon::parse( $certamen->fecha_inicio)->locale('es_ES')->isoFormat('lll');
            $certamen->fecha_termino = Carbon::parse( $certamen->fecha_termino)->locale('es_ES')->isoFormat('lll');
        }catch(\PDOException $e){
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }
        return view('plataforma.certamen.ver_certamen', compact('certamen', 'res_certamen'));
    }

    public function inicializar_certamen(Request $request){
        try{
            DB::beginTransaction();
            $res_certamen = auth()->user()->evaluaciones()->where('id_certamen','=',$request->id_certamen)->first();
            if(isset($res_certamen)){
                if($res_certamen->finalizado == true){
                    throw new \Exception("Error: Ya has resuelto este certamen");
                }
            }else{
                $certamen = Certamenes::find($request->id_certamen);
                $res_certamen = new ResolucionCertamenes;
                $res_certamen->token = Str::random(55);
                $res_certamen->id_usuario = auth()->user()->id;
                $certamen->resoluciones()->save($res_certamen);
                
                $categorias = $certamen->categorias()->get();
                $problemas_seleccionados = [];
                $problemas_seleccionados_id = [];
                foreach ($categorias as $categoria){
                    //selecciona un problema aleatorio, ignorando los problemas que ya fueron escogidos previamente
                    $problema_aleatorio = $categoria->problemas()->inRandomOrder()->first();
                    $seleccion = new SeleccionProblemasCertamenes;
                    $seleccion->problema()->associate($problema_aleatorio);
                    array_push($problemas_seleccionados, $seleccion);        
                    array_push($problemas_seleccionados_id, $problema_aleatorio->id);
                }
                $res_certamen->ProblemasSeleccionadas()->saveMany($problemas_seleccionados);
            }
            DB::commit();
        }catch(\PDOException $e){
            DB::rollback();
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }
        return redirect()->route('certamenes.resolucion', ['token'=>$res_certamen->token]);
    }

    public function resolver_certamen(Request $request){
        try{
            $res_certamen = ResolucionCertamenes::with(['certamen', 'ProblemasSeleccionadas'])->where('token', '=', $request->token)->first();
            if(!isset($res_certamen)){
                throw new \Exception("Error: El token de resolución de certamen no existe");
            }
            if($res_certamen->id_usuario != auth()->user()->id){
                throw new \Exception("Error: Estás tratando de acceder a una resolución de certamen que no te pertenece.");
            }

            $ultimos_envios = DB::table('envio_solucion_problemas')
            ->leftJoin('resolver', 'resolver.id', '=', 'envio_solucion_problemas.id_resolver')
            ->leftJoin('cursa', 'cursa.id', '=', 'envio_solucion_problemas.id_cursa')
            ->where('envio_solucion_problemas.id_certamen', '=', $res_certamen->id)
            ->where('cursa.id_usuario', '=', auth()->user()->id)
            ->select('resolver.id_problema', 'envio_solucion_problemas.solucionado', 'envio_solucion_problemas.puntaje');
            $problemas = Problemas::with('lenguajes')->leftJoinSub($ultimos_envios, 'ultimos_envios', function (JoinClause $join){
                $join->on('ultimos_envios.id_problema', '=', 'problemas.id');
            })
            ->whereIn('problemas.id', $res_certamen->ProblemasSeleccionadas()->pluck('id_problema')->toArray())
            ->select('problemas.nombre', 'problemas.id', 'problemas.codigo','problemas.body_problema', 'problemas.memoria_limite', 'problemas.tiempo_limite', 'problemas.puntaje_total', DB::raw('max(ultimos_envios.solucionado) as resuelto'), DB::raw('max(ultimos_envios.puntaje) as puntaje_maximo'))
            ->groupBy('problemas.nombre', 'problemas.id','problemas.codigo' , 'problemas.body_problema', 'problemas.memoria_limite', 'problemas.tiempo_limite', 'problemas.puntaje_total')
            ->get()->map(function($item) use($res_certamen){
                $item->resolver_ruta = route('certamenes.resolver_problema', ['token_certamen'=>$res_certamen->token, 'codigo'=>$item->codigo, 'id_curso'=>$res_certamen->certamen->id_curso]);
                $item->pdf_ruta = route('problemas.pdf_enunciado', ['id_problema'=>$item->id]);
                return $item;
            });
            
        }catch(\PDOException $e){
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }catch(\Exception $e){
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }
            return view('plataforma.certamen.ver_problemas', compact('problemas', 'res_certamen'));
    }

    public function finalizar_certamen(Request $request){
        try{
            $res_certamen = ResolucionCertamenes::where('token', '=', $request->token)->first();
            $res_certamen->finalizar_certamen();
        }catch(\PDOException $e){
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }
            return redirect()->route('certamenes.listado')->with("success", "Has finalizado el certamen");
    }
    

    public function guardar_codigo_certamen(Request $request){
        try{
            DB::beginTransaction();
            $last_envio = EnvioSolucionProblema::where('id_certamen', '=', $request->id_certamen)->orderBy('created_at', 'DESC')->first();
            $last_envio->codigo = $request->codigo_save;
            if(isset($request->lenguaje_save)){
                $resolver = Resolver::where('id_problema', '=', $request->id_problema)->where('id_lenguaje', '=', $request->lenguaje_save)->first();
                $last_envio->ProblemaLenguaje()->dissociate();
                $last_envio->ProblemaLenguaje()->associate($resolver);
            }
            $last_envio->save();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('certamenes.resolucion', ['token'=>$request->token_certamen])->with('error', $e->getMessage());
        }
        return redirect()->route('certamenes.resolucion', ['token'=>$request->token_certamen])->with('succes', 'El código desarrollado ha sido almacenado');
    }
}


