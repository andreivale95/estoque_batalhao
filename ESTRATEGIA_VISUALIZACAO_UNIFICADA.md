# Estratégia de Visualização Unificada - Itens Estoque vs Patrimoniais

## 📊 Análise Comparativa

### TABELA: itens_estoque (CONSUMO)
```
Campos Específicos:
- quantidade (int) - quantidade disponível
- quantidade_cautelada (int) - quantidade emprestada
- preco_unitario (decimal) - preço por unidade
- valor_total (decimal) - valor total do lote
- valor_unitario (decimal) - valor unitário
- unidade (fk) - unidade de medida
- fk_item_pai (fk) - para containers/hierarquias
- lote (varchar) - identificação do lote
- fornecedor (varchar) - fornecedor
- nota_fiscal (varchar) - NF
- sei (varchar) - número SEI
- data_trp (date) - data TRP
- fonte (varchar) - fonte
- quantidade_inicial (int) - quantidade inicial

Campos Comuns:
- id, fk_produto, fk_secao
- data_entrada, data_saida
- quantidade_cautelada
- observacao
- created_at, updated_at
```

### TABELA: itens_patrimoniais (PERMANENTE)
```
Campos Específicos:
- patrimonio (varchar, UNIQUE) - número único do bem
- serie (varchar) - série do equipamento
- condicao (varchar) - novo/bom/regular/ruim

Campos Comuns:
- id, fk_produto, fk_secao
- data_entrada, data_saida
- quantidade_cautelada
- observacao
- created_at, updated_at
```

---

## 🎯 Solução: Visualização Unificada com UNION

### Estratégia
```sql
UNION entre:
- itens_estoque com colunas específicas preenchidas
- itens_patrimoniais com colunas específicas preenchidas
- Coluna 'tipo' adicionada dinamicamente ('consumo' ou 'permanente')
```

### Query Unificada Proposta
```sql
SELECT 
    'consumo' AS tipo,
    ie.id,
    ie.fk_produto,
    p.nome AS nome_produto,
    ie.quantidade,
    ie.quantidade_cautelada,
    ie.preco_unitario,
    ie.valor_total,
    ie.lote,
    ie.fornecedor,
    ie.fk_secao,
    s.nome AS secao_nome,
    ie.data_entrada,
    ie.data_saida,
    NULL AS patrimonio,
    NULL AS serie,
    NULL AS condicao,
    ie.observacao,
    ie.created_at
FROM itens_estoque ie
JOIN produtos p ON p.id = ie.fk_produto
LEFT JOIN secaos s ON s.id = ie.fk_secao

UNION

SELECT 
    'permanente' AS tipo,
    ip.id,
    ip.fk_produto,
    p.nome AS nome_produto,
    NULL AS quantidade,
    ip.quantidade_cautelada,
    NULL AS preco_unitario,
    NULL AS valor_total,
    NULL AS lote,
    NULL AS fornecedor,
    ip.fk_secao,
    s.nome AS secao_nome,
    ip.data_entrada,
    ip.data_saida,
    ip.patrimonio,
    ip.serie,
    ip.condicao,
    ip.observacao,
    ip.created_at
FROM itens_patrimoniais ip
JOIN produtos p ON p.id = ip.fk_produto
LEFT JOIN secaos s ON s.id = ip.fk_secao

ORDER BY data_entrada DESC
```

---

## 📋 Campos da Visualização Unificada

| Campo | Consumo | Permanente | Tipo | Observação |
|-------|---------|------------|------|-----------|
| `tipo` | 'consumo' | 'permanente' | STRING | Identificador do tipo |
| `id` | ✓ | ✓ | BIGINT | ID da linha |
| `fk_produto` | ✓ | ✓ | BIGINT | FK para produtos |
| `nome_produto` | ✓ | ✓ | STRING | Nome do produto |
| `quantidade` | ✓ | ✗ | INT | Quantidade disponível |
| `quantidade_cautelada` | ✓ | ✓ | INT | Quantidade cautelada |
| `preco_unitario` | ✓ | ✗ | DECIMAL | Preço por unidade |
| `valor_total` | ✓ | ✗ | DECIMAL | Valor total |
| `lote` | ✓ | ✗ | STRING | Número do lote |
| `fornecedor` | ✓ | ✗ | STRING | Fornecedor |
| `fk_secao` | ✓ | ✓ | BIGINT | FK para seção |
| `secao_nome` | ✓ | ✓ | STRING | Nome da seção |
| `data_entrada` | ✓ | ✓ | DATETIME | Data de entrada |
| `data_saida` | ✓ | ✓ | DATETIME | Data de saída |
| `patrimonio` | ✗ | ✓ | STRING | Número do patrimônio |
| `serie` | ✗ | ✓ | STRING | Série do bem |
| `condicao` | ✗ | ✓ | STRING | Condição do bem |
| `observacao` | ✓ | ✓ | TEXT | Observações |
| `created_at` | ✓ | ✓ | TIMESTAMP | Criado em |

---

## 🛠️ Implementação

### 1. Criar Repository/Service para Query Unificada
```php
// app/Services/EstoqueUnificadoService.php

namespace App\Services;

class EstoqueUnificadoService
{
    public function obterEstoqueUnificado($filtros = [])
    {
        return DB::table('itens_estoque as ie')
            ->select(
                DB::raw("'consumo' as tipo"),
                'ie.id',
                'ie.fk_produto',
                'p.nome as nome_produto',
                'ie.quantidade',
                'ie.quantidade_cautelada',
                'ie.preco_unitario',
                'ie.valor_total',
                'ie.lote',
                'ie.fornecedor',
                'ie.fk_secao',
                's.nome as secao_nome',
                'ie.data_entrada',
                'ie.data_saida',
                DB::raw('NULL as patrimonio'),
                DB::raw('NULL as serie'),
                DB::raw('NULL as condicao'),
                'ie.observacao',
                'ie.created_at'
            )
            ->join('produtos as p', 'p.id', '=', 'ie.fk_produto')
            ->leftJoin('secaos as s', 's.id', '=', 'ie.fk_secao')
            ->union(
                DB::table('itens_patrimoniais as ip')
                    ->select(
                        DB::raw("'permanente' as tipo"),
                        'ip.id',
                        'ip.fk_produto',
                        'p.nome as nome_produto',
                        DB::raw('NULL as quantidade'),
                        'ip.quantidade_cautelada',
                        DB::raw('NULL as preco_unitario'),
                        DB::raw('NULL as valor_total'),
                        DB::raw('NULL as lote'),
                        DB::raw('NULL as fornecedor'),
                        'ip.fk_secao',
                        's.nome as secao_nome',
                        'ip.data_entrada',
                        'ip.data_saida',
                        'ip.patrimonio',
                        'ip.serie',
                        'ip.condicao',
                        'ip.observacao',
                        'ip.created_at'
                    )
                    ->join('produtos as p', 'p.id', '=', 'ip.fk_produto')
                    ->leftJoin('secaos as s', 's.id', '=', 'ip.fk_secao')
            )
            ->orderBy('data_entrada', 'desc')
            ->paginate(10);
    }
}
```

### 2. Usar no Controller
```php
// app/Http/Controllers/EstoqueController.php

use App\Services\EstoqueUnificadoService;

public function listarUnificado(EstoqueUnificadoService $service)
{
    $itens = $service->obterEstoqueUnificado();
    return view('estoque.listarUnificado', compact('itens'));
}
```

### 3. View Unificada
```blade
{{-- resources/views/estoque/listarUnificado.blade.php --}}

<table class="table table-striped">
    <thead>
        <tr>
            <th>Tipo</th>
            <th>Produto</th>
            <th>Quantidade</th>
            <th>Cautelado</th>
            <th>Patrimônio</th>
            <th>Série</th>
            <th>Lote</th>
            <th>Seção</th>
            <th>Condição</th>
            <th>Data Entrada</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach($itens as $item)
            <tr class="{{ $item->tipo === 'permanente' ? 'bg-light-blue' : 'bg-light-green' }}">
                <td>
                    @if($item->tipo === 'consumo')
                        <span class="badge bg-green">Consumo</span>
                    @else
                        <span class="badge bg-blue">Permanente</span>
                    @endif
                </td>
                <td>{{ $item->nome_produto }}</td>
                <td>{{ $item->quantidade ?? '-' }}</td>
                <td>{{ $item->quantidade_cautelada }}</td>
                <td>{{ $item->patrimonio ?? '-' }}</td>
                <td>{{ $item->serie ?? '-' }}</td>
                <td>{{ $item->lote ?? '-' }}</td>
                <td>{{ $item->secao_nome ?? '-' }}</td>
                <td>
                    @if($item->condicao)
                        <span class="badge 
                            {{ $item->condicao === 'novo' ? 'bg-success' : '' }}
                            {{ $item->condicao === 'bom' ? 'bg-info' : '' }}
                            {{ $item->condicao === 'regular' ? 'bg-warning' : '' }}
                            {{ $item->condicao === 'ruim' ? 'bg-danger' : '' }}">
                            {{ ucfirst($item->condicao) }}
                        </span>
                    @else
                        -
                    @endif
                </td>
                <td>{{ $item->data_entrada?->format('d/m/Y') ?? '-' }}</td>
                <td>
                    @if($item->tipo === 'consumo')
                        <a href="{{ route('estoque.item', $item->id) }}" class="btn btn-xs btn-info">
                            <i class="fa fa-eye"></i>
                        </a>
                    @else
                        <a href="{{ route('patrimonial.item', $item->id) }}" class="btn btn-xs btn-info">
                            <i class="fa fa-eye"></i>
                        </a>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{ $itens->links() }}
```

---

## 🎨 Filtros Sugeridos

```blade
<!-- Filtros na visualização unificada -->
<div class="box-body">
    <form method="GET" class="form-inline">
        <div class="form-group">
            <label>Tipo:</label>
            <select name="tipo" class="form-control">
                <option value="">Todos</option>
                <option value="consumo">Consumo</option>
                <option value="permanente">Permanente</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Produto:</label>
            <select name="produto" class="form-control">
                <option value="">Todos</option>
                @foreach($produtos as $p)
                    <option value="{{ $p->id }}">{{ $p->nome }}</option>
                @endforeach
            </select>
        </div>
        
        <div class="form-group">
            <label>Seção:</label>
            <select name="secao" class="form-control">
                <option value="">Todas</option>
                @foreach($secoes as $s)
                    <option value="{{ $s->id }}">{{ $s->nome }}</option>
                @endforeach
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>
</div>
```

---

## 📈 Vantagens da Abordagem

✅ Mantém duas tabelas separadas (consumo vs permanente)
✅ Uma única visualização unificada para ambas
✅ Fácil filtrar por tipo
✅ Campos específicos mostram quando aplicável
✅ Sem duplicação de dados
✅ Flexível para expandir

---

## ⚠️ Considerações

- UNION remove duplicatas por padrão (use UNION ALL para manter)
- Performance: índices em fk_produto e fk_secao
- Paginação: funciona após UNION
- Ordenação: aplicada após UNION

