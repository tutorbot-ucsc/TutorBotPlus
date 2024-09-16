<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EvaluacionSolucion;
use App\Models\EnvioSolucionProblema;
use App\Models\Problemas;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
class EvaluacionSolucionController extends Controller
{
    protected  $higlightjs_language = [
        "py" => "python",
        "c++" => "cpp",
        "c"=> "c",
        "java" => "java",
        "sql" => "sql",
    ];
    public function ver_evaluacion(Request $request){
        $envio = EnvioSolucionProblema::where("token", "=", $request->token)->first();
        $highlightjs_choice = $this->higlightjs_language[strtolower($envio->lenguaje->abreviatura)];
        $juez = $envio->juez_virtual;
        $evaluaciones = $envio->evaluaciones()->get();
        $evaluacion_arr = [];
        foreach($evaluaciones as $evaluacion){
            if ($evaluacion->estado == "En Proceso") {
                $evaluacion_arr[$evaluacion->token] = $evaluacion;
            }
        }
        if(sizeof($evaluacion_arr)>0){
            $client = new Client();
            try {
                $response = $client->request('GET', $juez->direccion.'/submissions/batch?tokens=' . implode('%2C', array_keys($evaluacion_arr)) . '&base64_encoded=true&fields=*', [
                    'headers' => [
                        'x-rapidapi-host' => $juez->host,
                        'x-rapidapi-key' => $juez->api_token,
                    ],
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
                        } else if($item['status']["id"] >=4 && $item['status']["id"] <=12) {
                            $evaluacion->estado = "Rechazado";
                        }
                        $evaluacion->save();
                    }
                }
            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }
        return view('plataforma.problemas.resultado', compact('envio', 'evaluaciones', 'highlightjs_choice'));
    }
}
