<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    use HasFactory;

    protected $table = 'perfis';

    public function permissoes(){
        return $this->hasMany(PerfilPermissao::class, 'fk_perfil', 'id_perfil');
    }

    protected $fillable = [
        'id_perfil',
        'nome',
        'status'
    ];
}
