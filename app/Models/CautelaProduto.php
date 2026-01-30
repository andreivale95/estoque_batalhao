<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ItenPatrimonial;

class CautelaProduto extends Model
{
    use HasFactory;

    protected $table = 'cautela_produto';

    protected $fillable = [
        'cautela_id',
        'produto_id',
        'estoque_id',
        'iten_patrimonial_id',
        'quantidade',
        'quantidade_devolvida',
        'data_devolucao',
    ];

    protected $casts = [
        'data_devolucao' => 'date',
    ];

    public function quantidadePendente()
    {
        return $this->quantidade - $this->quantidade_devolvida;
    }

    public function isDevolvido()
    {
        return $this->quantidade_devolvida >= $this->quantidade;
    }

    public function cautela()
    {
        return $this->belongsTo(Cautela::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function estoque()
    {
        return $this->belongsTo(Itens_estoque::class, 'estoque_id');
    }

    public function itenPatrimonial()
    {
        return $this->belongsTo(ItenPatrimonial::class, 'iten_patrimonial_id');
    }
}
