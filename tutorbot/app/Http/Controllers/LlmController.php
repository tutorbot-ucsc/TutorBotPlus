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
        ->join('casos__pruebas', 'casos__pruebas.id', '=', 'evaluacion_solucions.id_caso')
        ->join('envio_solucion_problemas', 'envio_solucion_problemas.id', '=', 'evaluacion_solucions.id_envio')
        ->join('lenguajes_programaciones', 'lenguajes_programaciones.id', '=', 'envio_solucion_problemas.id_lenguaje')
        ->join('problemas', 'problemas.id', '=', 'envio_solucion_problemas.id_problema')
        ->select('evaluacion_solucions.*' ,'envio_solucion_problemas.codigo', 'envio_solucion_problemas.token','envio_solucion_problemas.id_lenguaje', 'lenguajes_programaciones.nombre as nombre_lenguaje','envio_solucion_problemas.id_problema', 'problemas.limite_llm', 'casos__pruebas.entradas', 'casos__pruebas.salidas')
        ->where('envio_solucion_problemas.token', '=', $request->token)
        ->where('estado', '=', 'Rechazado')
        ->orWhere('estado', '=', 'Error')
        ->orderBy('estado', 'ASC')->first();

        $cant_retroalimentacion = $evaluacion->limite_llm - DB::table('solicitud_ra_llms')->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')->where('envio_solucion_problemas.id_problema', '=', $evaluacion->id_problema)->where('id_usuario','=', auth()->user()->id)->count();
        if($cant_retroalimentacion == 0){
            return redirect()->route('envios.ver', ['token'=>$request->token])->with('error', 'Has superado el límite de uso de la LLM');
        }
        $codigo = $evaluacion->codigo;

       if($evaluacion->estado == "Error"){
            if(isset($evaluacion->error_compilacion)){
                $prompt = SolicitudRaLlm::promptError   (base64_decode($evaluacion->error_compilacion), $evaluacion->nombre_lenguaje);
            }else{
                $prompt = SolicitudRaLlm::promptError   (null, $evaluacion->nombre_lenguaje,$evaluacion->resultado);
            }
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                    ['role' => 'user', 'content' => $codigo],
                ],
            ]);
        }else if($evaluacion->estado == "Rechazado"){
            $entradas = $evaluacion->entradas;
            $salidas = $evaluacion->salidas;
            $result = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => SolicitudRaLlm::promptErrorRespuestaErronea($entradas, $salidas, base64_decode($evaluacion->stout), $evaluacion->nombre_lenguaje)],
                    ['role' => 'user', 'content' => $codigo],
                ],
            ]);
        }
        try{
            DB::beginTransaction();
            $retroalimentacion = new SolicitudRaLlm;
            $retroalimentacion->retroalimentacion = $result->choices[0]->message->content;
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
        ->join('problemas', 'envio_solucion_problemas.id_problema', '=', 'problemas.id')
        ->select('solicitud_ra_llms.*', 'problemas.id as id_problema', 'problemas.limite_llm', 'problemas.habilitar_llm')
        ->where('envio_solucion_problemas.token', '=', $request->token)
        ->orderBy('created_at', 'DESC')->first();
        $cant_retroalimentacion = $retroalimentacion->limite_llm - DB::table('solicitud_ra_llms')->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')->where('envio_solucion_problemas.id_problema', '=', $retroalimentacion->id_problema)->where('id_usuario','=', auth()->user()->id)->count();
        $highlightjs_choice = EnvioSolucionProblema::$higlightjs_language[strtolower($envios->lenguaje->abreviatura)];
        if(!isset($retroalimentacion)){
            return redirect()->route('generar_retroalimentacion', ['token'=>$request->token]);
        }
        return view('plataforma.problemas.retroalimentacion', compact('retroalimentacion', 'cant_retroalimentacion', 'envios', 'highlightjs_choice'))->with('token', $request->token);
    }
}
