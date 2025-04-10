<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EfetivoMilitar extends Model
{
    protected $table = 'efetivo_militar';

    protected $fillable = [
        'posto_graduacao',
        'nome',
        'matricula',
        'fk_unidade',
    ];

    public function unidade()
    {
        return $this->belongsTo(Unidade::class, 'fk_unidade');
    }

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'efetivo_militar_produto', 'fk_efetivo_militar', 'fk_produto')
                    ->withTimestamps();
    }

}








