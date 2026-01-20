<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItenPatrimonial extends Model
{
    use HasFactory;

    protected $table = 'itens_patrimoniais';

    protected $fillable = [
        'fk_produto',
        'patrimonio',
        'serie',
        'fk_secao',
        'condicao',
        'data_entrada',
        'data_saida',
        'quantidade_cautelada',
        'observacao',
    ];

    // Relações
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'fk_produto');
    }

    public function secao()
    {
        return $this->belongsTo(Secao::class, 'fk_secao');
    }
}
