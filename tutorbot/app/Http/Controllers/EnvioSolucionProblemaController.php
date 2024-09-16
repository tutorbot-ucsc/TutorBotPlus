<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EnvioSolucionProblema;
use App\Models\EvaluacionSolucion;
use App\Models\JuecesVirtuales;
use App\Models\LenguajesProgramaciones;
use App\Models\Problemas;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
class EnvioSolucionProblemaController extends Controller
{
    public function enviar_solucion(Request $request){
        $validated = $request->validate(EnvioSolucionProblema::$rules);
        if($request->juez_virtual == "0"){
            $request->juez_virtual = JuecesVirtuales::inRandomOrder()->first()->id;
        }
        try{
            DB::beginTransaction();
            $envio = new EnvioSolucionProblema;
            $envio->codigo = $request->codigo;
            $envio->token = Str::random(40);
            $envio->usuario()->associate(auth()->user());
            $envio->juez_virtual()->associate(JuecesVirtuales::find($request->juez_virtual));
            $envio->problema()->associate(Problemas::find($request->id_problema));
            $envio->lenguaje()->associate(LenguajesProgramaciones::where("codigo", "=", $request->lenguaje));
            $envio->save();
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return back()->with('error', $e->getMessage())->with('codigo', $request->codigo);
        }
        $resultado = $this->enviar_api_juez($envio, $request->lenguaje);
        if($resultado["estado"]){
            return redirect()->route('envios.ver', ["token"=>$envio->token]);
        }else{
            $envio->delete();
            return back()->with('error', $resultado["mensaje"])->with("codigo", $request->codigo);
        }
    }

    public function enviar_api_juez($envio, $lenguaje){
        try{
            $problema = Problemas::find($envio->id_problema);
            $juez = JuecesVirtuales::find($envio->id_juez);
            $codigo = base64_encode($envio->codigo);
        }catch(\PDOException $e){
                return false;
        }        
        //Transformacion JSON de lo que pide el juez virtual Judge0
        $batch_submissions = [];
        $casos = $problema->casos_de_prueba()->get();
        foreach($casos as $caso){
            $entrada = base64_encode($caso->entradas);
            $salida = base64_encode($caso->salidas);
            array_push($batch_submissions, '{"language_id":' . $lenguaje . ',"source_code":"' . $codigo . '","stdin":"' . $entrada . '","expected_output":"' . $salida . '", "memory_limit":"'.$problema->memoria_limite.'", "cpu_time_limit":"'.$problema->tiempo_limite.'"}');
        }
        $client = new Client();
        try {
            $response = $client->request('POST', $juez->direccion.'/submissions/batch?base64_encoded=true', [
                'body' => '{"submissions":[' . implode(',', $batch_submissions) . ']}',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-rapidapi-host' => $juez->host,
                    'x-rapidapi-key' => $juez->api_token,
                ],
            ]);

            $data = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ["estado"=>false, "mensaje"=>$e->getMessage()];
        }
        try{
            DB::beginTransaction();
            for ($i = 0; $i < sizeof($batch_submissions); $i++) {
                $evaluacion = new EvaluacionSolucion;
                $evaluacion->token = $data[$i]['token'];
                $evaluacion->envio()->associate($envio);
                $evaluacion->casos_pruebas()->associate($casos[$i]);
                $evaluacion->save();
            }
            DB::commit();
        }catch(\PDOException $e){
            DB::rollBack();
            return ["estado"=>false, "mensaje"=>"Error en el ingreso de evaluaciones a la base de datos"];
        }
        return ["estado"=>true];
    }
}
