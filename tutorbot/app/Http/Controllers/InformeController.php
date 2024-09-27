<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problemas;
use App\Models\Cursos;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
class InformeController extends Controller
{
    public function index_problema(Request $request){
        $problema = Problemas::with("cursos")->find($request->id);
        $cursos = $problema->cursos()->select('disponible.cantidad_resueltos', 'disponible.cantidad_intentos', 'disponible.cant_retroalimentacion_solicitada','cursos.id as id_curso','cursos.nombre', 'cursos.codigo')->get();
        return view("informes.problemas.index", compact("problema", "cursos"));
    }
    public function ver_informe_problema(Request $request){
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
        ->groupBy("envio_solucion_problemas.token","envio_solucion_problemas.id_curso", "envio_solucion_problemas.id_problema","users.firstname", "users.lastname", "users.rut", 'envio_solucion_problemas.token', 'envio_solucion_problemas.cant_casos_resuelto','envio_solucion_problemas.puntaje','lenguajes_programaciones.nombre', 'envio_solucion_problemas.solucionado', 'envio_solucion_problemas.inicio', 'envio_solucion_problemas.termino', 'ultima_evaluacion.resultado', 'ultima_evaluacion.estado')
        ->orderBy("envio_solucion_problemas.created_at", "DESC")
        ->orderBy("users.firstname", "ASC")
        ->get();
        return view("informes.problemas.informe", compact("envios", "problema"));
    }
}
