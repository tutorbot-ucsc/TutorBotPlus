<?php

namespace App\Observers;

use App\Models\SolicitudRaLlm;
use Illuminate\Support\Facades\DB;
class LlmObserver
{
    /**
     * Handle the SolicitudRaLlm "created" event.
     */
    public function created(SolicitudRaLlm $solicitudRaLlm): void
    {
        DB::table('problemas')
        ->join('envio_solucion_problemas', 'envio_solucion_problemas.id_problema', 'problemas.id')
        ->join('evaluacion_solucions', 'evaluacion_solucions.id_envio', '=', 'envio_solucion_problemas.id')
        ->select('problemas.*')
        ->where('id_envio', '=', $solicitudRaLlm->id_envio)
        ->increment('problemas.cant_retroalimentacion_solicitada');
    }

    /**
     * Handle the SolicitudRaLlm "updated" event.
     */
    public function updated(SolicitudRaLlm $solicitudRaLlm): void
    {
        //
    }

    /**
     * Handle the SolicitudRaLlm "deleted" event.
     */
    public function deleted(SolicitudRaLlm $solicitudRaLlm): void
    {
        DB::table('problemas')
        ->join('envio_solucion_problemas', 'envio_solucion_problemas.id_problema', 'problemas.id')
        ->join('evaluacion_solucions', 'evaluacion_solucions.id_envio', '=', 'envio_solucion_problemas.id')
        ->select('problemas.*')
        ->where('id_envio', '=', $solicitudRaLlm->id_envio)
        ->decrement('problemas.cant_retroalimentacion_solicitada');
    }

    /**
     * Handle the SolicitudRaLlm "restored" event.
     */
    public function restored(SolicitudRaLlm $solicitudRaLlm): void
    {
        //
    }

    /**
     * Handle the SolicitudRaLlm "force deleted" event.
     */
    public function forceDeleted(SolicitudRaLlm $solicitudRaLlm): void
    {
        //
    }
}
