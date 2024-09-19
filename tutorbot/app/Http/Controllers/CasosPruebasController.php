<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problemas;
use Illuminate\Support\Facades\DB;
use App\Models\Casos_Pruebas;
class CasosPruebasController extends Controller
{
    public function asignacion_casos(Request $request){
        $problema = Problemas::find($request->id);
        $casos = $problema->casos_de_prueba()->orderBy('created_at','desc')->get();
        return view("problemas.casos_pruebas.assign", compact('problema', 'casos'));
    }

    public function eliminar_caso(Request $request){
        try{
            DB::beginTransaction();
            $caso = Casos_Pruebas::find($request->id);
            $problema = Problemas::find($caso->id_problema);
            $problema->puntaje_total = $problema->puntaje_total - $caso->puntos;
            $caso->delete();
            $problema->save();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->route('casos_pruebas.assign', ["id"=>$caso->id_problema])->with('success', 'El caso de prueba '.$caso->id.' ha sido eliminado');

    }

    public function add_caso(Request $request){

        $validated = $request->validate(Casos_Pruebas::$rules);
        try{
            $problema = Problemas::find( $request->id );

            $caso = new Casos_Pruebas;
            $caso->entradas = $request->input("entradas");
            $caso->salidas = $request->input("salidas");
            $caso->puntos = $request->input("puntos");
            $problema->casos_de_prueba()->save($caso);
            $problema->puntaje_total = $problema->puntaje_total + $caso->puntos;
        }catch( \PDOException $e){
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
        return redirect()->route('casos_pruebas.assign', ["id"=>$request->id])->with('success', 'El caso de prueba '.$caso->id.' ha sido añadido');

    }
}
