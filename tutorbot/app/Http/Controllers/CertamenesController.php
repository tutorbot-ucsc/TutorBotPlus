<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Certamenes;
use Illuminate\Support\Facades\DB;
use App\Models\Cursos;
use Carbon\Carbon;
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
}
