<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    protected $table = 'produtos';

    protected $fillable = [
        'nome',
        'descricao',
        'marca',
        'valor',
        'fk_categoria',
        'fk_kit',
        'tamanho',
        'unidade',
        'ativo',
        'patrimonio',


    ];


    public function condicao()
    {
        return $this->hasOne(Condicao::class, 'id', 'fk_condicao');
    }


    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'fk_categoria'); // FK para Categoria
    }

    public function kit()
    {
        return $this->belongsTo(Kit::class, 'fk_kit');
    }
    // Produto.php
    public function tamanho()
    {
        return $this->belongsTo(Tamanho::class, 'tamanho');
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'unidade');
    }





    public function militares()
    {
        return $this->belongsToMany(EfetivoMilitar::class, 'efetivo_militar_produto', 'fk_produto', 'fk_efetivo_militar')
            ->withTimestamps();
    }










}
