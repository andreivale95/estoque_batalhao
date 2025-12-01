<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CautelaProduto extends Model
{
    use HasFactory;

    protected $table = 'cautela_produto';

    protected $fillable = [
        'cautela_id',
        'produto_id',
        'quantidade',
    ];

    public function cautela()
    {
        return $this->belongsTo(Cautela::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
