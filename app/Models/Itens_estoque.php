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
        'quantidade_cautelada',
        'preco_unitario',
        'unidade',
        'fk_secao',
        'fk_item_pai',
        'data_entrada',
        'data_saida',
        'fk_produto',
        'lote',
        'fornecedor',
        'nota_fiscal',
        'observacao',
        'sei',
        'data_trp',
        'fonte',
        'valor_total',
        'valor_unitario',
        'quantidade_inicial',
    ];
    public function secao()
    {
        return $this->belongsTo(Secao::class, 'fk_secao');
    }
    public function unidade()
    {
        return $this->hasOne(Unidade::class, 'id', 'unidade');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'fk_produto'); // FK para a tabela produtos
    }

    // Relações hierárquicas
    public function itemPai()
    {
        return $this->belongsTo(Itens_estoque::class, 'fk_item_pai');
    }

    public function itensFilhos()
    {
        return $this->hasMany(Itens_estoque::class, 'fk_item_pai');
    }

    // Método para obter a localização completa (caminho hierárquico)
    public function getLocalizacaoCompleta()
    {
        $localizacao = [];
        
        // Adiciona a seção
        if ($this->secao) {
            $localizacao[] = 'Seção: ' . $this->secao->nome;
        }
        
        // Percorre a hierarquia de pais
        $item = $this;
        $caminhoPais = [];
        
        while ($item->itemPai) {
            $item = $item->itemPai;
            $caminhoPais[] = $item->produto->nome ?? 'Item sem nome';
        }
        
        // Inverte para começar do topo
        if (!empty($caminhoPais)) {
            $caminhoPais = array_reverse($caminhoPais);
            $localizacao[] = 'Dentro de: ' . implode(' → ', $caminhoPais);
        }
        
        return implode(' | ', $localizacao);
    }

    // Método para obter apenas o caminho hierárquico simplificado
    public function getCaminhoHierarquico()
    {
        $caminho = [$this->produto->nome ?? 'Item sem nome'];
        $item = $this;
        
        while ($item->itemPai) {
            $item = $item->itemPai;
            array_unshift($caminho, $item->produto->nome ?? 'Item sem nome');
        }
        
        return implode(' → ', $caminho);
    }

    // Verifica se é um container (tem itens dentro)
    public function isContainer()
    {
        return $this->itensFilhos()->exists();
    }

    // Retorna quantidade total considerando filhos
    public function getQuantidadeTotalComFilhos()
    {
        $total = $this->quantidade;
        
        foreach ($this->itensFilhos as $filho) {
            $total += $filho->getQuantidadeTotalComFilhos();
        }
        
        return $total;
    }










}
