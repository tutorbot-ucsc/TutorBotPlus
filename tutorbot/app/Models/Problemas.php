<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Categoria_Problema;
use App\Models\LenguajesProgramaciones;
use App\Models\EnvioSolucionProblema;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Problemas extends Model
{
    use HasFactory;
    

    protected $filable = [
        "nombre",
        "codigo",
        "fecha_inicio",
        "fecha_termino",
        "memoria_limite",
        "tiempo_limite",
        "visible",
        "body_problema",
        "body_problema_resumido",
        "habilitar_llm",
        "limite_llm",
        "archivo_adicional"
    ];

    public static $createRules = [
        "nombre" => ['required','max:255','string'],
        "codigo" => ['required', 'string', 'max:100', 'unique:App\Models\Problemas,codigo'], 
        "fecha_inicio" => ['date', 'nullable', 'before_or_equal:fecha_termino'],
        "fecha_termino" => ['date', 'nullable', 'after_or_equal:fecha_inicio'],
        "memoria_limite" => ["nullable","numeric"],
        "tiempo_limite" => ["nullable", "numeric"],
        "visible" => ["required", "boolean"],
        "body_problema" => ["required", "string", "min:50"],
        "body_problema_resumido" => ["string", 'nullable'],
        "habilitar_llm" => ["boolean"],
        "limite_llm" => ["nullable","numeric"],
        "archivo_adicional" => ["mimes:zip"]
    ];
    public static $llm_config_rules = [
        "habilitar_llm" => ["boolean"],
        "limite_llm" => ["nullable","numeric"],
        "body_problema_resumido" => ["string", 'nullable'],
    ];
    public static function updateRules($codigo){
        return 
        [
            "nombre" => ['required','max:255','string'],
            "codigo" => ['required', 'string', 'max:100', Rule::unique('problemas')->ignore($codigo, "codigo")], 
            "fecha_inicio" => ['date', 'nullable'],
            "fecha_termino" => ['date', 'nullable'],
            "memoria_limite" => ["nullable","numeric"],
            "tiempo_limite" => ["nullable", "numeric"],
            "visible" => ["required", "boolean"],
            "body_problema" => ["required", "string", "min:50"],
            "body_problema_resumido" => ["string" ,'nullable'],
            "habilitar_llm" => ["boolean"],
            "limite_llm" => ["nullable","numeric"],
            "archivo_adicional" => ["mimes:zip"]
        ];
    }
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
    
    public function envios(): HasMany
    {
        return $this->hasMany(EnvioSolucionProblema::class,'id_problema');
    }
}
