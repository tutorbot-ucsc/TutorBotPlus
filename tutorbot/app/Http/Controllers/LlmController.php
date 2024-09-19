<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SolicitudRaLlm;
use App\Models\LenguajesProgramaciones;
use App\Models\EvaluacionSolucion;
use App\Models\EnvioSolucionProblema;
use App\Models\Problemas;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\DB;


class LlmController extends Controller
{
    public function generar_retroalimentacion(Request $request){
        $evaluacion = DB::table('evaluacion_solucions')
        ->join('envio_solucion_problemas', 'envio_solucion_problemas.id', '=', 'evaluacion_solucions.id_envio')
        ->select('evaluacion_solucions.*' ,'envio_solucion_problemas.codigo', 'envio_solucion_problemas.token','envio_solucion_problemas.id_lenguaje', 'envio_solucion_problemas.id_problema')
        ->where('envio_solucion_problemas.token', '=', $request->token)
        ->where('estado', '=', 'Rechazado')
        ->orWhere('estado', '=', 'Error')
        ->orderBy('estado', 'ASC')->first();
        $problema = Problemas::find($evaluacion->id_problema);
        $cant_retroalimentacion = $problema->limite_llm - DB::table('solicitud_ra_llms')->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')->where('envio_solucion_problemas.id_problema', '=', $problema->id)->where('id_usuario','=', auth()->user()->id)->count();
        if($cant_retroalimentacion == 0){
            return redirect()->route('envios.ver', ['token'=>$request->token])->with('error', 'Has superado el límite de uso de la LLM');
        }
        $codigo = $evaluacion->codigo;
        $lenguaje = LenguajesProgramaciones::find($evaluacion->id_lenguaje)->value('nombre');
        
       /* if($evaluacion->estado == "Error"){
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => SolicitudRaLlm::promptErrorCompilacion($evaluacion->error_compilacion, $lenguaje)],
                    ['role' => 'user', 'content' => $codigo],
                ],
            ]);
        }else if($evaluacion->estado == "Rechazado"){
            $entradas = $evaluacion->casos_pruebas->entradas;
            $salidas = $evaluacion->casos_pruebas->entradas;
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => SolicitudRaLlm::promptErrorRespuestaErronea($entradas, $salidas, $evaluacion->stout, $lenguaje)],
                    ['role' => 'user', 'content' => $codigo],
                ],
            ]);
        }*/
        try{
            DB::beginTransaction();
            $retroalimentacion = new SolicitudRaLlm;
            $retroalimentacion->retroalimentacion = "test";
            $retroalimentacion->id_envio = $evaluacion->id_envio;
            $retroalimentacion->save();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return redirect()->route('envios.ver', ['token'=>$request->token])->with('error', $e->getMessage());
        }
        return redirect()->route('envios.retroalimentacion', ['token'=>$request->token]);
    }

    public function ver_retroalimentacion(Request $request){
        $envios = EnvioSolucionProblema::where('token', '=', $request->token)->first();
        $retroalimentacion = DB::table('solicitud_ra_llms')
        ->join('envio_solucion_problemas', 'envio_solucion_problemas.id', '=', 'solicitud_ra_llms.id_envio')
        ->select('solicitud_ra_llms.*')
        ->where('envio_solucion_problemas.token', '=', $request->token)
        ->orderBy('created_at', 'DESC')->first();
        $problema = Problemas::find($envios->id_problema);
        $cant_retroalimentacion = $problema->limite_llm -  DB::table('solicitud_ra_llms')->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')->where('envio_solucion_problemas.id_problema', '=', $problema->id)->where('id_usuario','=', auth()->user()->id)->count();
        if(!isset($retroalimentacion)){
            return redirect()->route('generar_retroalimentacion', ['token'=>$request->token]);
        }
        return view('plataforma.problemas.retroalimentacion', compact('retroalimentacion', 'problema', 'cant_retroalimentacion'))->with('token', $request->token);
    }
}
