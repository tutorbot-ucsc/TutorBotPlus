<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problemas;
use App\Models\Cursos;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use App\Models\EvaluacionSolucion;
use App\Models\LenguajesProgramaciones;
use App\Models\EnvioSolucionProblema;
class InformeController extends Controller
{
    public function index_problema(Request $request){
        $problema = Problemas::with("cursos")->find($request->id);
        $cursos = $problema->cursos()->select('disponible.cantidad_resueltos', 'disponible.cantidad_intentos', 'disponible.cant_retroalimentacion_solicitada','cursos.id as id_curso','cursos.nombre', 'cursos.codigo')->get();
        return view("informes.problemas.index", compact("problema", "cursos"));
    }
    public function ver_envios_problema(Request $request){
        $problema = Problemas::find($request->id_problema);
        if(!isset($problema) && !Cursos::where("id", "=", $request->id_curso)->exists()){
            redirect()->route('informes.problemas.index', ["id"=>$request->id_problema])->with("Error", "El curso o problema no existe");
        }
        $ultima_evaluacion = DB::table('evaluacion_solucions')
        ->select('resultado', 'id_envio', 'estado')
        ->where('estado', '=', 'Rechazado')
        ->orWhere('estado', '=', 'En Proceso')
        ->orWhere('estado', '=', 'Error')
        ->orderBy('updated_at','DESC')
        ->groupBy('id_envio', 'resultado', 'estado');
        $envios = DB::table("envio_solucion_problemas")
        ->join('problemas', 'problemas.id', '=', 'envio_solucion_problemas.id_problema')
        ->leftJoin('casos__pruebas', 'casos__pruebas.id_problema', '=', 'problemas.id')
        ->join("users", "users.id", "=", "envio_solucion_problemas.id_usuario")
        ->join("lenguajes_programaciones", "lenguajes_programaciones.id", "=", "envio_solucion_problemas.id_lenguaje")
        ->leftJoinSub($ultima_evaluacion, 'ultima_evaluacion', function (JoinClause $join){
            $join->on('envio_solucion_problemas.id', '=', 'ultima_evaluacion.id_envio');
        })
        ->select("envio_solucion_problemas.token","envio_solucion_problemas.id_curso", "envio_solucion_problemas.id_problema","users.firstname", "users.lastname", "users.rut", 'envio_solucion_problemas.token', 'envio_solucion_problemas.cant_casos_resuelto','envio_solucion_problemas.puntaje','lenguajes_programaciones.nombre as nombre_lenguaje', 'envio_solucion_problemas.solucionado', 'envio_solucion_problemas.inicio', 'envio_solucion_problemas.termino', 'ultima_evaluacion.resultado', 'ultima_evaluacion.estado', DB::raw('count(casos__pruebas.id) as total_casos'))
        ->where("envio_solucion_problemas.id_curso", "=", $request->id_curso)
        ->where("envio_solucion_problemas.id_problema", "=", $request->id_problema)
        ->whereNotNull("termino")
        ->groupBy("envio_solucion_problemas.token","envio_solucion_problemas.id_curso", "envio_solucion_problemas.id_problema","users.firstname", "users.lastname", "users.rut", 'envio_solucion_problemas.token', 'envio_solucion_problemas.cant_casos_resuelto','envio_solucion_problemas.puntaje','lenguajes_programaciones.nombre', 'envio_solucion_problemas.solucionado', 'envio_solucion_problemas.inicio', 'envio_solucion_problemas.termino', 'ultima_evaluacion.resultado', 'ultima_evaluacion.estado')
        ->orderBy("envio_solucion_problemas.created_at", "DESC")
        ->orderBy("users.firstname", "ASC");
        if(isset($request->id_usuario)){
            $envios = $envios->where('envio_solucion_problemas.id_usuario', '=', $request->id_usuario);
        }
        $envios = $envios->get();
        return view("informes.problemas.envios", compact("envios", "problema"));
    }

    public function ver_informe_problema(Request $request){
        $estadistica_estados = EvaluacionSolucion::join('envio_solucion_problemas', 'envio_solucion_problemas.id', '=', 'evaluacion_solucions.id_envio')->select('resultado')->where('envio_solucion_problemas.id_problema', '=', $request->id_problema)->where('envio_solucion_problemas.id_curso', '=', $request->id_curso)->get()->countBy('resultado')->toArray();
        $info_usuarios_envios = DB::table('envio_solucion_problemas')
        ->select(DB::raw('max(envio_solucion_problemas.cant_casos_resuelto) as max_casos_resueltos'), DB::raw('max(envio_solucion_problemas.puntaje) as max_puntaje'), 'envio_solucion_problemas.id_usuario',  DB::raw('max(envio_solucion_problemas.solucionado) as solucionado'))
        ->where('id_problema', '=', $request->id_problema)
        ->where('id_curso', '=', $request->id_curso)
        ->whereNotNull('envio_solucion_problemas.termino')
        ->groupBy('id_usuario');
        $envios = DB::table('users')
        ->join('envio_solucion_problemas', 'envio_solucion_problemas.id_usuario', '=', 'users.id')
        ->leftJoin('solicitud_ra_llms', 'solicitud_ra_llms.id_envio', '=', 'envio_solucion_problemas.id')
        ->leftJoinSub($info_usuarios_envios, 'info_usuario_envios', function (JoinClause $join){
            $join->on('info_usuario_envios.id_usuario','=','users.id');
        })
        ->select('envio_solucion_problemas.id_curso','envio_solucion_problemas.id_problema', 'envio_solucion_problemas.id_usuario','users.rut', 'users.firstname', 'users.lastname', DB::raw('count(solicitud_ra_llms.id) as cant_retroalimentacion'), DB::raw('count(envio_solucion_problemas.id) as cant_intentos'),'info_usuario_envios.max_casos_resueltos', 'info_usuario_envios.max_puntaje' ,'info_usuario_envios.solucionado')
        ->groupBy('envio_solucion_problemas.id_curso','envio_solucion_problemas.id_problema', 'envio_solucion_problemas.id_usuario','users.rut', 'users.firstname', 'users.lastname', 'info_usuario_envios.max_casos_resueltos', 'info_usuario_envios.max_puntaje' ,'info_usuario_envios.solucionado')
        ->where('envio_solucion_problemas.id_problema', '=', $request->id_problema)
        ->where('envio_solucion_problemas.id_curso', '=', $request->id_curso)
        ->whereNotNull('envio_solucion_problemas.termino')
        ->whereNull('id_certamen')
        ->orderBy('envio_solucion_problemas.solucionado', 'DESC')->get();
        $lenguajes_estadistica = DB::table('lenguajes_programaciones')
        ->join('envio_solucion_problemas', 'envio_solucion_problemas.id_lenguaje', '=', 'lenguajes_programaciones.id')
        ->select('lenguajes_programaciones.nombre')
        ->where('envio_solucion_problemas.id_problema', '=', $request->id_problema)
        ->where('envio_solucion_problemas.id_curso', '=', $request->id_curso)
        ->whereNotNull('envio_solucion_problemas.termino')
        ->get()->countBy('nombre')->toArray();
        $problema_estadistica = DB::table('disponible')
        ->join('problemas', 'problemas.id', '=', 'disponible.id_problema')
        ->join('casos__pruebas', 'problemas.id', '=', 'casos__pruebas.id_problema')
        ->select('problemas.nombre','disponible.cantidad_resueltos', 'disponible.cantidad_intentos', 'disponible.tiempo_total', 'cant_retroalimentacion_solicitada', DB::raw('count(casos__pruebas.id) as total_casos'), DB::raw('sum(casos__pruebas.puntos) as puntaje_total'))
        ->where('disponible.id_problema','=', $request->id_problema)
        ->where('disponible.id_curso', '=', $request->id_curso)
        ->groupBy('problemas.nombre','disponible.cantidad_resueltos', 'disponible.cantidad_intentos', 'disponible.tiempo_total', 'cant_retroalimentacion_solicitada')
        ->first();

        return view('informes.problemas.informe', compact('estadistica_estados', 'envios', 'lenguajes_estadistica', 'problema_estadistica'))->with("id_problema", $request->id_problema);
    }
}
