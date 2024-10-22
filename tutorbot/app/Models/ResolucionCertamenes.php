<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResolucionCertamenes extends Model
{
    use HasFactory;

    public function usuario(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'id_usuario');
    }

    public function certamen(): BelongsTo
    {
        return $this->BelongsTo(Certamenes::class, 'id_certamen');
    }

    public function ProblemasSeleccionadas(): HasMany{
        return $this->hasMany(SeleccionProblemasCertamenes::class, 'id_res_certamen');
    }
}
