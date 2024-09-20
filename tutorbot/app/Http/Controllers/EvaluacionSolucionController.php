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
    protected $higlightjs_language = [
        "py" => "python",
        "c++" => "cpp",
        "c"=> "c",
        "java" => "java",
        "sql" => "sql",
    ];

    public function ver_evaluacion(Request $request){
        $envio = EnvioSolucionProblema::where("token", "=", $request->token)->first();
        $problema = $envio->problema()->first();
        $highlightjs_choice = $this->higlightjs_language[strtolower($envio->lenguaje->abreviatura)];
        $juez = $envio->juez_virtual;
        $evaluaciones = $envio->evaluaciones()->get();
        //Calculo de intentos restante de retroalimentación
        $cant_retroalimentacion = $problema->limite_llm - DB::table('solicitud_ra_llms')->leftJoin('envio_solucion_problemas', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')->where('envio_solucion_problemas.id_problema', '=', $problema->id)->where('id_usuario','=', auth()->user()->id)->count();
        //Booleano que verifica si hay una retroalimentación asociado al código.
        $tieneRetroalimentacion = SolicitudRaLlm::where('id_envio', '=', $envio->id)->exists();
        $evaluacion_arr = [];
        //Verifica si hay evaluaciones por procesar, si hay casos que no han sido evaluados entonces se almacenara en un array.
        foreach($evaluaciones as $evaluacion){
            if ($evaluacion->estado == "En Proceso") {
                $evaluacion_arr[$evaluacion->token] = $evaluacion;
            }
        }
        if(sizeof($evaluacion_arr)>0){
            $this::api_request($juez, $evaluacion_arr, $envio);
        }
        if(sizeof($evaluaciones) == $envio->cant_casos_resuelto && $envio->solucionado == false){
            $envio->solucionado = true;
            $problema->cantidad_resueltos = $problema->cantidad_resueltos + 1;
            $problema->save();
        }
        $envio->save();
        return view('plataforma.problemas.resultado', compact('envio', 'evaluaciones', 'highlightjs_choice', 'cant_retroalimentacion', 'tieneRetroalimentacion', 'problema'));
    }

    private static function api_request($juez, $evaluacion_arr, $envio){
            $client = new Client();
            //Crea el header para el request dependiendo del tipo de autenticación que se utiliza, revisar el modelo JuecesVirtuales.
            $header = JuecesVirtuales::generateHeaderRequest($juez);
            try {
                $response = $client->request('GET', $juez->direccion.'/submissions/batch?tokens=' . implode('%2C', array_keys($evaluacion_arr)) . '&base64_encoded=true&fields=*', [
                    'headers' => $header,
                ]);
                $data = json_decode($response->getBody(), true);
                foreach ($data["submissions"] as $item) {
                    $evaluacion = $evaluacion_arr[$item["token"]];
                    $evaluacion->resultado = $item['status']["description"];
                    if ($item['status']["id"] != 1 && $item['status']["id"] != 2) {
                        $evaluacion->tiempo = $item['time'];
                        $evaluacion->memoria = $item['memory'];
                        $evaluacion->stout = $item['stdout'];
                        if(isset($item["stderr"])){
                            $evaluacion->error_compilacion = $item["stderr"];
                        }else{
                            $evaluacion->error_compilacion = $item["compile_output"];
                        }
                        if ($item['status']["id"] == 3) {
                            $evaluacion->estado = "Aceptado";
                            $envio->cant_casos_resuelto = $envio->cant_casos_resuelto + 1;
                        } else if($item['status']["id"] ==4) {
                            $evaluacion->estado = "Rechazado";
                        }else if($item['status']["id"] >=5 && $item['status']["id"] <=12){
                            $evaluacion->estado = "Error";
                        }
                        $evaluacion->save();
                    }
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
    }
}
