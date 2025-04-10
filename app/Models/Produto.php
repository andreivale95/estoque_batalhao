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
        'fk_tipo_produto',
        'fk_kit',
        'tamanho',
        'ativo',



    ];


    public function condicao()
    {
        return $this->hasOne(Condicao::class, 'id', 'fk_condicao');
    }


    public function tipoProduto()
    {
        return $this->belongsTo(TipoProduto::class, 'fk_tipo_produto'); // FK para tipo_produto
    }



    public function kit()
    {
        return $this->belongsTo(Kit::class, 'fk_kit');
    }




    public function militares()
    {
        return $this->belongsToMany(EfetivoMilitar::class, 'efetivo_militar_produto', 'fk_produto', 'fk_efetivo_militar')
                    ->withTimestamps();
    }










}
