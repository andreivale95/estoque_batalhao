<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Itens_estoque extends Model
{
    use HasFactory;

    protected $table = 'itens_estoque';

    protected $fillable = [
        'quantidade',
        'preco_unitario',
        'unidade',
        'fk_secao',
        'data_entrada',
        'data_saida',
        'fk_produto',
        'lote',
        'fornecedor',
        'nota_fiscal',
        'observacao',
        'sei',
        'data_trp',
        'fonte',
        'valor_total',
        'valor_unitario',
        'quantidade_inicial',
    ];
    public function secao()
    {
        return $this->belongsTo(Secao::class, 'fk_secao');
    }
    public function unidade()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidade');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'fk_produto'); // FK para a tabela produtos
    }










}
