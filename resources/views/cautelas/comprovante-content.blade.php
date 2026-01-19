<style>
    .comprovante-html {
        font-family: Arial, sans-serif;
        color: #333;
    }
    .comprovante-html .container {
        max-width: 800px;
        margin: 0 auto;
    }

    .comprovante-html .header {
        text-align: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #3c8dbc;
        padding-bottom: 15px;
    }
    .comprovante-html .header h1 {
        margin: 0;
        color: #3c8dbc;
        font-size: 28px;
    }
    .comprovante-html .header p {
        margin: 5px 0;
        color: #666;
    }
    .comprovante-html .info-section {
        margin: 20px 0;
        display: flex;
        justify-content: space-between;
    }
    .comprovante-html .info-column {
        flex: 1;
        margin-right: 20px;
    }
    .comprovante-html .info-column:last-child {
        margin-right: 0;
    }
    .comprovante-html .info-label {
        font-weight: bold;
        color: #3c8dbc;
        font-size: 12px;
        text-transform: uppercase;
        margin-bottom: 5px;
    }
    .comprovante-html .info-value {
        border-bottom: 1px solid #ddd;
        padding-bottom: 5px;
        margin-bottom: 15px;
        font-size: 14px;
    }
    .comprovante-html .table {
        width: 100%;
        border-collapse: collapse;
        margin: 30px 0;
    }
    .comprovante-html .table thead {
        background-color: #3c8dbc;
        color: white;
    }
    .comprovante-html .table th, .comprovante-html .table td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: left;
    }
    .comprovante-html .table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    .comprovante-html .table td {
        font-size: 13px;
    }
    .comprovante-html .resumo {
        margin: 30px 0;
        padding: 15px;
        background-color: #f0f0f0;
        border-left: 4px solid #3c8dbc;
    }
    .comprovante-html .resumo-item {
        display: flex;
        justify-content: space-between;
        margin: 8px 0;
        font-size: 14px;
    }
    .comprovante-html .resumo-item.total {
        font-weight: bold;
        border-top: 2px solid #3c8dbc;
        padding-top: 10px;
        margin-top: 10px;
        color: #3c8dbc;
    }
    .comprovante-html .footer {
        text-align: center;
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #ddd;
        font-size: 12px;
        color: #666;
    }
    .comprovante-html .assinatura {
        margin-top: 50px;
        display: flex;
        justify-content: space-around;
    }
    .comprovante-html .assinatura-item {
        text-align: center;
    }
    .comprovante-html .assinatura-linha {
        border-top: 1px solid #333;
        width: 150px;
        margin-top: 30px;
    }
    .comprovante-html .status-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
    }
    .comprovante-html .status-pendente {
        background-color: #fff3cd;
        color: #856404;
    }
    .comprovante-html .status-devolvido {
        background-color: #d4edda;
        color: #155724;
    }
</style>

<div class="comprovante-html">
    <div class="container">
        <div class="header">
            <h1>COMPROVANTE DE CAUTELA</h1>
            <p>Nº {{ str_pad($cautela->id, 6, '0', STR_PAD_LEFT) }} - {{ $cautela->created_at->format('d/m/Y H:i') }}</p>
        </div>

        <div class="info-section">
            <div class="info-column">
                <div class="info-label">Responsável</div>
                <div class="info-value">{{ $cautela->nome_responsavel }}</div>

                <div class="info-label">Telefone</div>
                <div class="info-value">{{ $cautela->telefone }}</div>
            </div>
            <div class="info-column">
                <div class="info-label">Instituição</div>
                <div class="info-value">{{ $cautela->instituicao }}</div>

                <div class="info-label">Data da Cautela</div>
                <div class="info-value">{{ $cautela->data_cautela->format('d/m/Y') }}</div>
            </div>
            <div class="info-column">
                <div class="info-label">Data Prevista Devolução</div>
                <div class="info-value">{{ $cautela->data_prevista_devolucao->format('d/m/Y') }}</div>

                <div class="info-label">Data de Emissão</div>
                <div class="info-value">{{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <h3 style="color: #3c8dbc; border-bottom: 2px solid #3c8dbc; padding-bottom: 10px;">ITENS CAUTELADOS</h3>

        @if($cautela->produtos->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th style="width: 80px; text-align: center;">Quantidade</th>
                        <th style="width: 80px; text-align: center;">Devolvida</th>
                        <th style="width: 80px; text-align: center;">Pendente</th>
                        <th style="width: 100px; text-align: center;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cautela->produtos as $item)
                    <tr>
                        <td>{{ $item->produto->nome ?? 'Produto Desconhecido' }}</td>
                        <td style="text-align: center;">{{ $item->quantidade }}</td>
                        <td style="text-align: center;">{{ $item->quantidade_devolvida }}</td>
                        <td style="text-align: center;">{{ $item->quantidadePendente() }}</td>
                        <td style="text-align: center;">
                            @if($item->isDevolvido())
                                <span class="status-badge status-devolvido">DEVOLVIDO</span>
                            @else
                                <span class="status-badge status-pendente">PENDENTE</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="resumo">
                @php
                    $totalCautelado = $cautela->produtos->sum('quantidade');
                    $totalDevolvido = $cautela->produtos->sum('quantidade_devolvida');
                    $totalPendente = $totalCautelado - $totalDevolvido;
                @endphp
                <div class="resumo-item">
                    <span>Total Cautelado:</span>
                    <span>{{ $totalCautelado }} itens</span>
                </div>
                <div class="resumo-item">
                    <span>Total Devolvido:</span>
                    <span>{{ $totalDevolvido }} itens</span>
                </div>
                <div class="resumo-item total">
                    <span>Total Pendente:</span>
                    <span>{{ $totalPendente }} itens</span>
                </div>
            </div>
        @else
            <p style="text-align: center; color: #999; padding: 20px;">Nenhum item cautelado.</p>
        @endif

        <div class="assinatura">
            <div class="assinatura-item">
                <strong style="font-size: 12px;">Responsável pela Cautela</strong>
                <div class="assinatura-linha"></div>
                <p style="font-size: 11px; margin-top: 5px;">{{ $cautela->nome_responsavel }}</p>
            </div>
            <div class="assinatura-item">
                <strong style="font-size: 12px;">Responsável da Unidade</strong>
                <div class="assinatura-linha"></div>
                <p style="font-size: 11px; margin-top: 5px;">{{ $cautela->responsavel_unidade ?? '' }}</p>
            </div>
        </div>

        <div class="footer">
            <p>Este comprovante foi emitido eletronicamente e tem validade como recibo da cautela.</p>
            <p>Emitido em {{ now()->format('d/m/Y') }} às {{ now()->format('H:i:s') }}</p>
        </div>
    </div>
</div>
