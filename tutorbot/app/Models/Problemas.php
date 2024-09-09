<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Categoria_Problema;
use App\Models\LenguajesProgramaciones;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Problemas extends Model
{
    use HasFactory;
    
    public function categorias(): BelongsToMany
    {
        return $this->belongsToMany(Categoria_Problema::class, 'pertenece', 'id_problema', 'id_categoria');
    }

    public function lenguajes(): BelongsToMany
    {
        return $this->belongsToMany(LenguajesProgramaciones::class, 'resolver', 'id_problema', 'id_lenguaje');
    }

    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Cursos::class,'disponible','id_problema', 'id_curso');  
    }

    public function casos_de_prueba(): HasMany
    {
        return $this->hasMany(Casos_Pruebas::class, 'id_problema');
    }
    
}
