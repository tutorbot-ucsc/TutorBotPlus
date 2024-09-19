<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\EnvioSolucionProblema;
use App\Models\EvaluacionSolucion;
use App\Models\JuecesVirtuales;
use App\Models\LenguajesProgramaciones;
use App\Models\Problemas;
use GuzzleHttp\Client;
use Carbon\Carbon;

class EnvioSolucionProblemaController extends Controller
{

    public function ver_envios(Request $request){
        $envios_query = DB::table('envio_solucion_problemas')
        ->join('problemas', 'problemas.id', '=', 'envio_solucion_problemas.id_problema')
        ->join('lenguajes_programaciones', 'lenguajes_programaciones.id', '=', 'envio_solucion_problemas.id_lenguaje')
        ->whereNull('id_certamen')
        ->where('id_usuario', '=', auth()->user()->id)
        ->select('problemas.nombre as nombre_problema', 'problemas.codigo as codigo_problema','envio_solucion_problemas.id as id_envio', 'envio_solucion_problemas.created_at','envio_solucion_problemas.token', 'lenguajes_programaciones.abreviatura as nombre_lenguaje', 'envio_solucion_problemas.solucionado', 'envio_solucion_problemas.inicio', 'envio_solucion_problemas.termino')
        ->orderBy('envio_solucion_problemas.created_at', 'DESC');
        if(isset($request->id_problema)){
            $envios_query = $envios_query->where('problemas.id', '=', $request->id_problema);
        }
        $envios = $envios_query->get();
        return view('plataforma.envios.index', compact('envios'));
    }

    public function enviar_solucion(Request $request)
    {
        $validated = $request->validate(EnvioSolucionProblema::$rules);
        //Escoge un juez virtual de manera aleatoria si el usuario escogió eso.
        if ($request->juez_virtual == "0") {
            $request->juez_virtual = JuecesVirtuales::inRandomOrder()->first()->id;
        }
        try {
            DB::beginTransaction();
            $envio = auth()->user()->envios()->where('id_problema', '=', $request->id_problema)->orderBy('created_at', 'DESC')->first();
            $envio->codigo = $request->codigo;
            $envio->juez_virtual()->associate(JuecesVirtuales::find($request->juez_virtual));
            $envio->lenguaje()->associate(LenguajesProgramaciones::where("codigo", "=", $request->lenguaje)->first());
            $envio->termino = Carbon::now();
            $envio->save();
            DB::commit();
        } catch (\PDOException $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->with('codigo', $request->codigo);
        }
        $resultado = $this->enviar_api_juez($envio, $request->lenguaje);
        if ($resultado["estado"]) {
            return redirect()->route('envios.ver', ["token" => $envio->token]);
        } else {
            $envio->delete();
            return back()->with('error', $resultado["mensaje"])->with("codigo", $request->codigo);
        }
    }

    public function enviar_api_juez($envio, $lenguaje)
    {
        try {
            $problema = Problemas::find($envio->id_problema);
            $juez = JuecesVirtuales::find($envio->id_juez);
            $codigo = base64_encode($envio->codigo);
        } catch (\PDOException $e) {
            return false;
        }
        //Transformación a JSON en batch del código fuente, los casos de prueba (entradas y salidas) y su memoria limite y tiempo limite.
        $batch_submissions = [];
        $casos = $problema->casos_de_prueba()->get();
        foreach ($casos as $caso) {
            $entrada = base64_encode($caso->entradas);
            $salida = base64_encode($caso->salidas);
            array_push($batch_submissions, '{"language_id":' . $lenguaje . ',"source_code":"' . $codigo . '","stdin":"' . $entrada . '","expected_output":"' . $salida . '", "memory_limit":"' . $problema->memoria_limite . '", "cpu_time_limit":"' . $problema->tiempo_limite . '"}');
        }
        $client = new Client();
        //Crea el header para el request dependiendo del tipo de autenticación que se utiliza, revisar el modelo JuecesVirtuales.
        $headerRequest = JuecesVirtuales::generateBodyRequest($juez);
        $headerRequest['Content-Type'] = 'application/json';
        try {
            $response = $client->request('POST', $juez->direccion . '/submissions/batch?base64_encoded=true', [
                'body' => '{"submissions":[' . implode(',', $batch_submissions) . ']}',
                'headers' => $headerRequest,
            ]);

            $data = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return ["estado" => false, "mensaje" => $e->getMessage()];
        }
        try {
            for ($i = 0; $i < sizeof($batch_submissions); $i++) {
                DB::beginTransaction();
                $evaluacion = new EvaluacionSolucion;
                $evaluacion->token = $data[$i]['token'];
                $evaluacion->envio()->associate($envio);
                $evaluacion->casos_pruebas()->associate($casos[$i]);
                $evaluacion->save();
                DB::commit();
            }
        } catch (\PDOException $e) {
            DB::rollBack();
            return ["estado" => false, "mensaje" => "Error en el ingreso de evaluaciones a la base de datos"];
        }
        return ["estado" => true];
    }
}
