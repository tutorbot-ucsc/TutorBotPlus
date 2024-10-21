<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\banco_problemas_certamenes;
use App\Models\Certamenes;
use App\Models\Problemas;
use Illuminate\Support\Facades\DB;

class BancoProblemasCertamenesController extends Controller
{
    public function index(Request $request){
        $certamen = Certamenes::find($request->id_certamen);
        $problemas = Problemas::whereRelation('cursos', 'cursos.id', '=', $certamen->id_curso)->whereNotIn('problemas.id', $certamen->problemas()->pluck('problemas.id'))->get();
        $banco_problemas = DB::table('banco_problemas_certamenes')
        ->join('problemas', 'banco_problemas_certamenes.id_problema', '=', 'problemas.id')
        ->select('problemas.nombre', 'banco_problemas_certamenes.id', 'banco_problemas_certamenes.puntaje', 'problemas.id as id_problema')
        ->where('banco_problemas_certamenes.id_certamen', '=', $request->id_certamen)
        ->get();
        return view('certamen.banco_de_problemas.add', compact('banco_problemas', 'certamen', 'problemas'));
    }
    public function verficar_cantidad_problemas(Certamenes $certamen, $puntaje){
        $banco_problema_puntajes = banco_problemas_certamenes::where('id_certamen', '=', $certamen->id)->distinct()->pluck('puntaje')->toArray();
        $count_puntaje = sizeof($banco_problema_puntajes);
        if($count_puntaje!=$certamen->cantidad_problemas){
            $certamen->cantidad_problemas = $count_puntaje;
            $certamen->save();
        }
    }
    public function add(Request $request){
        $request->validate([
            "puntaje" => "numeric|nullable",
            "problema"=> "required",
        ]);
        try{
            DB::beginTransaction();
            $certamen = Certamenes::find($request->id_certamen);
            $problema = Problemas::find($request->input('problema'));
            $puntaje = isset($request->puntaje)? $request->input('puntaje') : 1;
            $certamen->problemas()->attach($request->input('problema'), ['puntaje'=>$puntaje]);
            $this->verficar_cantidad_problemas($certamen, $request->input('puntaje'), $problema);
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('certamen.banco_problemas', ['id_certamen'=>$request->id_certamen])->with("error", $e->getMessage());
        }
        return redirect()->route('certamen.banco_problemas', $request->id_certamen)->with("success", "El problema '".$problema->nombre."' ha sido añadido.");
    }
    public function delete(Request $request){
        try{
            DB::beginTransaction();
            $certamen = Certamenes::find($request->id_certamen);
            $problema = $certamen->problemas()->find($request->id_problema);
            $puntaje = $problema->pivot->puntaje;
            $certamen->problemas()->detach($request->id_problema);
            $this->verficar_cantidad_problemas($certamen, $puntaje);
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('certamen.banco_problemas')->with("error", $e->getMessage());
        }
        return redirect()->route('certamen.banco_problemas', $request->id_certamen)->with("success", "El problema '".$problema->nombre."' ha sido removido.");
    }
}
