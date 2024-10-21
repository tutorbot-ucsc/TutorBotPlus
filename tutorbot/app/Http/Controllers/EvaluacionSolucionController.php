<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvaluacionSolucion;
use App\Models\EnvioSolucionProblema;
use App\Models\JuecesVirtuales;
use App\Models\Problemas;
use App\Models\SolicitudRaLlm;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use Carbon\Carbon;

class EvaluacionSolucionController extends Controller
{

    public function ver_evaluacion(Request $request)
    {
        $envio = EnvioSolucionProblema::with(['curso','problema', 'usuario'])->where("token", "=", $request->token)->first();
        if(!isset($envio)){
            return redirect()->route('envios.listado')->with('error', 'El envio no existe');
        }
        $problema = $envio->problema;
        $highlightjs_choice = EnvioSolucionProblema::$higlightjs_language[strtolower($envio->lenguaje->abreviatura)];
        $juez = $envio->juez_virtual;
        $evaluaciones = $envio->evaluaciones()->with('casos_pruebas')->get();
        //Calculo de intentos restante de retroalimentación
        $cant_retroalimentacion = $problema->limite_llm - DB::table('solicitud_ra_llms')->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')->join('resolver', 'envio_solucion_problemas.id_resolver', '=', 'resolver.id')->join('cursa', 'envio_solucion_problemas.id_cursa', '=', 'cursa.id')->where('resolver.id_problema', '=', $problema->id)->where('cursa.id_usuario', '=', auth()->user()->id)->count();
        //Booleano que verifica si hay una retroalimentación asociado al código.
        $tieneRetroalimentacion = SolicitudRaLlm::where('id_envio', '=', $envio->id)->exists();
        $evaluacion_arr = [];
        //Verifica si hay evaluaciones por procesar, si hay casos que no han sido evaluados entonces se almacenara en un array.
        foreach ($evaluaciones as $evaluacion) {
            if ($evaluacion->estado == "En Proceso") {
                $evaluacion_arr[$evaluacion->token] = $evaluacion;
            }
        }
        if (sizeof($evaluacion_arr) > 0) {
            $estado = $this::api_request($juez, $evaluacion_arr, $envio);
            if($estado=="error"){
                return redirect()->route('envios.listado')->with("error", $estado);
            }
        }
        if(isset($envio->termino)){
            $diferencia = Carbon::parse($envio->termino)->diffInSeconds(Carbon::parse($envio->inicio));
        }
        if (sizeof($evaluaciones) == $envio->cant_casos_resuelto && $envio->solucionado == false) {
            $envio->solucionado = true;
            DB::table('disponible')->where('id_curso', '=', $envio->curso->id)->where('id_problema', '=', $problema->id)->incrementEach(
                ["cantidad_resueltos"=>1,
                "tiempo_total"=>$diferencia,
                ]
            );
        }
        $envio->save();
        return view('plataforma.problemas.resultado', compact('envio', 'evaluaciones', 'highlightjs_choice', 'cant_retroalimentacion', 'tieneRetroalimentacion', 'problema', 'diferencia'));
    }

    private static function api_request($juez, $evaluacion_arr, $envio)
    {
        $client = new Client();
        //Crea el header para el request dependiendo del tipo de autenticación que se utiliza, revisar el modelo JuecesVirtuales.
        $header = JuecesVirtuales::generateHeaderRequest($juez);
        try {
            $response = $client->request('GET', $juez->direccion . '/submissions/batch?tokens=' . implode('%2C', array_keys($evaluacion_arr)) . '&base64_encoded=true&fields=*', [
                'headers' => $header,
            ]);
            $data = json_decode($response->getBody(), true);
        }catch(\Exception $e){
            return ["error"=> "Error al conecatr con la API del juez virtual"];
        }
        try{
            DB::beginTransaction();
            foreach ($data["submissions"] as $item) {
                $evaluacion = $evaluacion_arr[$item["token"]];
                $evaluacion->resultado = $item['status']["description"];
                if ($item['status']["id"] != 1 && $item['status']["id"] != 2) {
                    $evaluacion->tiempo = $item['time'];
                    $evaluacion->memoria = $item['memory'];
                    $evaluacion->stout = $item['stdout'];
                    if (isset($item["stderr"])) {
                        $evaluacion->error_compilacion = $item["stderr"];
                    } else {
                        $evaluacion->error_compilacion = $item["compile_output"];
                    }
                    if ($item['status']["id"] == 3) {
                        $evaluacion->estado = "Aceptado";
                        $envio->cant_casos_resuelto = $envio->cant_casos_resuelto + 1;
                        if(isset($evaluacion->casos_pruebas->puntos)){
                            $envio->puntaje = $envio->puntaje + $evaluacion->casos_pruebas->puntos;  
                        }
                    } else if ($item['status']["id"] == 4) {
                        $evaluacion->estado = "Rechazado";
                    } else if ($item['status']["id"] >= 5 && $item['status']["id"] <= 12) {
                        $evaluacion->estado = "Error";
                    }
                    $evaluacion->save();
                }
            }
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return ["error"=>"Error al verificar las evaluaciones"];
        }
    }
}
