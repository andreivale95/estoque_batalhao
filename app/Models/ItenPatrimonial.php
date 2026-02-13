<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ItemFoto;

class ItenPatrimonial extends Model
{
    use HasFactory;

    protected $table = 'itens_patrimoniais';

    protected $fillable = [
        'fk_produto',
        'patrimonio',
        'serie',
        'fk_secao',
        'ordem_pdf',
        'condicao',
        'data_entrada',
        'data_saida',
        'quantidade_cautelada',
        'observacao',
        'fornecedor',
        'nota_fiscal',
        'lote',
        'fonte',
        'data_trp',
        'sei',
        'valor_unitario',
        'valor_total',
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

    public function fotos()
    {
        return $this->hasMany(ItemFoto::class, 'fk_iten_patrimonial');
    }
}
