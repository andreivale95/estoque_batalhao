<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoMovimentacao extends Model
{
    use HasFactory;

    protected $table = 'historico_movimentacoes';

    protected $fillable = [
        'fk_produto',
        'tipo_movimentacao',
        'quantidade',
        'responsavel',
        'observacao',
        'data_movimentacao',
        'fk_unidade',
        'unidade_origem',
        'unidade_destino',

    ];



}
