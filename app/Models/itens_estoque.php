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
        'unidade',
        'data_entrada',
        'data_saida',
        'fk_produto',
        'lote',
        'fornecedor',
        'nota_fiscal',
        'observacao',
    ];

    public function unidade()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidade');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'fk_produto'); // FK para a tabela produtos
    }










}
