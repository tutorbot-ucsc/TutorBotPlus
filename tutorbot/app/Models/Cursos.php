<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Validation\Rule;

class Cursos extends Model
{
    use HasFactory;

    protected $fillable = [
     "nombre",
     "descripcion",
     "codigo",  
    ];

    public static $createRules = [
        "nombre" => ["required","string","max:255"],
        "descripcion" => ["string","max:500"],
        "codigo" => ["required","string","unique:App\Models\Cursos,codigo"],
    ];

    public static function updateRules($id){
        return [
            "nombre" => ["required","string","max:255"],
            "descripcion" => ["string","max:500"],
            "codigo" => ["required","string", Rule::unique('cursos')->ignore($id)],
        ];
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'cursa', 'id_curso', 'id_usuario');
    }
}
