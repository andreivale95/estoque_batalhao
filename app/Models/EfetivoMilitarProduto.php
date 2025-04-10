<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EfetivoMilitarProduto extends Model
{
    use HasFactory;

    protected $table = 'efetivo_militar_produto';

    protected $fillable = [
        'nome',
        'fk_efetivo_militar',
        'fk_produto',



    ];

    public function militar()
    {
        return $this->belongsTo(EfetivoMilitar::class, 'fk_efetivo_militar');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'fk_produto');
    }
    }






