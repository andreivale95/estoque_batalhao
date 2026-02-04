<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemFoto extends Model
{
    use HasFactory;

    protected $table = 'item_fotos';

    protected $fillable = [
        'fk_itens_estoque',
        'fk_iten_patrimonial',
        'fk_produto',
        'caminho_arquivo',
        'nome_original',
        'tipo_mime',
        'tamanho',
        'ordem',
    ];

    protected $casts = [
        'tamanho' => 'integer',
        'ordem' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com Itens_estoque (consumo)
     */
    public function itensEstoque()
    {
        return $this->belongsTo(Itens_estoque::class, 'fk_itens_estoque');
    }

    /**
     * Relacionamento com ItenPatrimonial (permanente)
     */
    public function itenPatrimonial()
    {
        return $this->belongsTo(ItenPatrimonial::class, 'fk_iten_patrimonial');
    }

    /**
     * Relacionamento com Produto
     */
    public function produto()
    {
        return $this->belongsTo(Produto::class, 'fk_produto');
    }

    /**
     * Obter a URL pública da foto
     */
    public function getUrlAttribute()
    {
        return url('storage/' . $this->caminho_arquivo);
    }

    /**
     * Obter o caminho relativo para storage
     */
    public function getStoragePath()
    {
        return $this->caminho_arquivo;
    }

    /**
     * Deletar arquivo ao remover o registro
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $existeOutro = self::where('caminho_arquivo', $model->caminho_arquivo)
                ->where('id', '!=', $model->id)
                ->exists();

            $arquivo = storage_path('app/public/' . $model->caminho_arquivo);
            if (!$existeOutro && file_exists($arquivo)) {
                unlink($arquivo);
            }
        });
    }
}
