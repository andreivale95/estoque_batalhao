<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kit extends Model
{
    use HasFactory;

    protected $table = 'kits';

    protected $fillable = [
        'nome',
        'descricao',
        'fk_unidade',
        'entregue',
        'disponivel',


    ];

    public function produtos()
    {
        return $this->hasMany(Produto::class, 'fk_kit');
    }


    // Kit.php
    public function itens()
    {
        return $this->hasMany(KitProduto::class, 'fk_kit'); // fk_kit é o nome da foreign key na tabela kit_produto
    }

    public function unidade()
    {
        return $this->hasOne(Unidade::class, 'id', 'fk_unidade');
    }





}
