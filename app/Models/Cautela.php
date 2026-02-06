<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cautela extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_responsavel',
        'telefone',
        'instituicao',
        'responsavel_unidade',
        'data_cautela',
        'data_prevista_devolucao',
        'fotos',
    ];

    protected $casts = [
        'data_cautela' => 'date',
        'data_prevista_devolucao' => 'date',
        'fotos' => 'array',
    ];

    public function produtos()
    {
        return $this->hasMany(CautelaProduto::class);
    }
}
