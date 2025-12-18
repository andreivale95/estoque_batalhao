<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    use HasFactory;

    protected $table = 'containers';

    protected $fillable = [
        'fk_produto',
        'tipo',
        'material',
        'capacidade_maxima',
        'unidade_capacidade',
        'compartimentos',
        'cor',
        'numero_serie',
        'descricao_adicional',
        'status',
    ];

    /**
     * Relacionamento: Um container pertence a um produto
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'fk_produto');
    }
}
