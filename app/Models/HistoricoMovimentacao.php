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
        'militar',

    ];

    public function militar()
    {
        return $this->belongsTo(EfetivoMilitar::class, 'militar');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'fk_produto');
    }

    public function origem()
    {
        return $this->belongsTo(Unidade::class, 'unidade_origem');
    }

    public function destino()
    {
        return $this->belongsTo(Unidade::class, 'unidade_destino');
    }

}
