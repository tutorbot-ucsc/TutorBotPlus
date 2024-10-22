<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certamenes;
use Illuminate\Support\Facades\DB;
use App\Models\Cursos;
use App\Models\ResolucionCertamenes;
use App\Models\SeleccionProblemasCertamenes;
use Carbon\Carbon;
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
            if(isset($res_certamen) && $res_certamen->finalizado==true){
                $certamen->disponibilidad = false;
            }
            $certamen->fecha_inicio = Carbon::parse( $certamen->fecha_inicio)->locale('es_ES')->isoFormat('lll');
            $certamen->fecha_termino = Carbon::parse( $certamen->fecha_termino)->locale('es_ES')->isoFormat('lll');
        }catch(\PDOException $e){
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }
        return view('plataforma.certamen.ver_certamen', compact('certamen', 'res_certamen'));
    }

    public function iniciar_resolver_certamen(Request $request){
        try{
            $certamen = Certamenes::find($request->id_certamen);
            $res_certamen = new ResolucionCertamenes;
            $res_certamen->token = Str::random(55);
            $res_certamen->id_usuario = auth()->user()->id;
            $certamen->resoluciones()->save($res_certamen);
            
            $categorias = $certamen->categorias()->get();
            $problemas_seleccionados = [];
            foreach ($categorias as $categoria){
                $problema_aleatorio = $categoria->problemas()->inRandomOrder()->first();
                $seleccion = new SeleccionProblemasCertamenes;
                $seleccion->problema()->associate($problema_aleatorio);
                array_push($problemas_seleccionados, $seleccion);        
            }
            $res_certamen->ProblemasSeleccionadas()->saveMany($problemas_seleccionados);
        }catch(\PDOException $e){ 
            return redirect()->route('certamenes.listado')->with("error", $e->getMessage());
        }
        dd($res_certamen, $certamen);
    }
}
