<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;
class banco_problemas_certamenes extends Pivot
{   
    protected $fillable = [
        "id_certamen",
        "id_problema",
        "puntaje"
    ];
    protected static function booted()
    {
        static::created(function ($pivot_model) {
            DB::table('certamenes')->where('id','=',$pivot_model->id_certamen)->incrementEach([
                'puntaje_total' => $pivot_model->puntaje,
                'cantidad_problemas' => 1,
            ]);
        });

        static::deleting(function ($pivot_model) {
            $puntaje = DB::table('banco_problemas_certamenes')->where('id_problema', '=', $pivot_model->id_problema)->where('id_certamen', '=', $pivot_model->id_certamen)->value('puntaje');
            DB::table('certamenes')->where('id','=',$pivot_model->id_certamen)->decrementEach([
                'puntaje_total' => $puntaje,
                'cantidad_problemas' => 1,
            ]);
        });
    }
}
