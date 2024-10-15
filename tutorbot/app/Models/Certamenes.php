<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Cursos;
use App\Models\Problemas;


class Certamenes extends Model
{
    use HasFactory;

    public function cursos(): HasMany
    {
        return $this->HasMany(Cursos::class, 'id_curso');
    }

    public function problemas(): BelongsToMany
    {
        return $this->belongsToMany(Problemas::class, 'banco_problemas_certamenes', 'id_certamen', 'id_problema');
    }
}
