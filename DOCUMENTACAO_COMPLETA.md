# DOCUMENTAÇÃO COMPLETA - SISALMOX

## 📋 Visão Geral do Sistema

O **SISALMOX** é um sistema de gerenciamento de almoxarifado desenvolvido em **Laravel** que controla o inventário de materiais em unidades militares. O sistema permite gerenciar produtos, estoque, movimentações, cautelas (empréstimos), itens patrimoniais e containers de armazenamento.

---

## 🏗️ ESTRUTURA DO BANCO DE DADOS

### 1. **USUÁRIOS E AUTENTICAÇÃO**

#### **Tabela: users**
- **Objetivo**: Armazenar dados de usuários do sistema
- **Campos principais**:
  - `cpf` (PK): Identificador único do usuário
  - `nome`, `sobrenome`: Dados pessoais
  - `email`: Email único para login
  - `password`: Senha criptografada
  - `fk_perfil`: Referência ao perfil de acesso
  - `fk_unidade`: Unidade militar associada
  - `telefone`: Contato
  - `status`: 's' (ativo) ou 'n' (inativo)
  - `image`: Avatar/foto do usuário

#### **Tabela: perfis**
- **Objetivo**: Definir perfis de acesso (Admin, Usuário, Gestor)
- **Campos**:
  - `id_perfil` (PK)
  - `nome`: Nome do perfil
  - `status`: Ativo ou inativo

#### **Tabela: permissoes**
- **Objetivo**: Definir permissões específicas por módulo
- **Campos**:
  - `id_permissao` (PK)
  - `nome`: Descrição da permissão
  - `modulo`: ID do módulo associado

#### **Tabela: perfil_permissao**
- **Objetivo**: Relacionar perfis com suas permissões (muitos-para-muitos)
- **Campos**:
  - `fk_perfil`: FK para perfis
  - `fk_permissao`: FK para permissoes

#### **Tabela: modulos**
- **Objetivo**: Listar módulos de acesso do sistema (Estoque, Cautelas, Relatórios, etc.)
- **Campos**:
  - `id_modulo` (PK)
  - `nome`: Nome do módulo

---

### 2. **ESTRUTURA ORGANIZACIONAL**

#### **Tabela: unidades**
- **Objetivo**: Representar unidades militares
- **Campos**:
  - `id` (PK)
  - `nome`: Nome da unidade (Ex: "1º Batalhão")
- **Relacionamentos**: Muitos usuários, seções e cautelas por unidade

#### **Tabela: secaos**
- **Objetivo**: Seções/departamentos dentro de cada unidade
- **Campos**:
  - `id` (PK)
  - `nome`: Nome da seção
  - `fk_unidade`: FK para unidades

#### **Tabela: efetivo_militar**
- **Objetivo**: Cadastro de militares pertencentes às unidades
- **Campos**:
  - `id` (PK)
  - `nome`: Nome do militar
  - `matricula`: Número de matrícula
  - `fk_unidade`: FK para unidades
  - `posto_graduacao`: Patente/graduação
- **Dados**: 685 registros de militares

---

### 3. **PRODUTOS E CATEGORIAS**

#### **Tabela: categorias**
- **Objetivo**: Categorizar produtos (Uniformes, Alimentos, Equipamentos, etc.)
- **Campos**:
  - `id` (PK)
  - `nome`: Nome único da categoria
  - `tipo_tamanho`: Tipo de tamanho (P, M, G, etc.)
- **Dados**: 11 categorias cadastradas

#### **Tabela: tamanhos**
- **Objetivo**: Tamanhos disponíveis para produtos
- **Campos**:
  - `id` (PK)
  - `tamanho`: Valor do tamanho (P, M, G, GG, etc.)
  - `tipo_tamanho`: Tipo/categoria do tamanho
- **Dados**: 17 tamanhos cadastrados

#### **Tabela: produtos**
- **Objetivo**: Catálogo de todos os produtos do almoxarifado
- **Campos**:
  - `id` (PK)
  - `nome`: Nome do produto
  - `descricao`: Descrição detalhada
  - `marca`: Marca do produto
  - `unidade`: UN (unidade), CX (caixa), PCT (pacote), KG, LT
  - `fk_categoria`: FK para categorias
  - `fk_kit`: FK para kits (se for kit)
  - `ativo`: Y/N - Produto ativo ou inativo
  - `tipo_controle`: 'consumo' ou 'permanente'
  - `tamanho`: FK para tamanhos (opcional)
- **Dados**: 2 produtos cadastrados
- **Relacionamentos**: Múltiplas referências para estoque, cautelas, containers

#### **Tabela: kits**
- **Objetivo**: Agrupar produtos em kits/conjuntos
- **Campos**:
  - `id` (PK)
  - `nome`: Nome do kit
  - `descricao`: Descrição
  - `fk_unidade`: Unidade responsável
  - `disponivel`: S/N - Kit disponível
- **Dados**: 1 kit cadastrado

#### **Tabela: tipoprodutos**
- **Objetivo**: Tipos específicos de produtos por categoria
- **Campos**:
  - `id` (PK)
  - `nome`: Nome do tipo
  - `fk_categoria`: FK para categorias

---

### 4. **GESTÃO DE ESTOQUE**

#### **Tabela: itens_estoque**
- **Objetivo**: Controlar quantidade e localização de itens em estoque
- **Campos principais**:
  - `id` (PK)
  - `fk_produto`: FK para produtos
  - `quantidade`: Quantidade atual
  - `quantidade_cautelada`: Quantidade emprestada
  - `preco_unitario`: Preço por unidade
  - `unidade`: ID da unidade militar onde está armazenado
  - `fk_secao`: Seção onde está armazenado
  - `fk_item_pai`: Para itens compostos (estrutura hierárquica)
  - `lote`: Número do lote
  - `fornecedor`: Fornecedor
  - `nota_fiscal`: Número da NF
  - `data_entrada`: Data de entrada
  - `data_saida`: Data de saída
  - `sei`: Número SEI (Sistema Eletrônico de Informações)
  - `data_trp`: Data da transferência
  - `fonte`: Origem do produto
  - `valor_total`: Valor total
  - `valor_unitario`: Valor por unidade
  - `quantidade_inicial`: Quantidade no início
- **Dados**: 5 itens em estoque

#### **Tabela: itens_patrimoniais**
- **Objetivo**: Controlar itens de patrimônio permanente
- **Campos**:
  - `id` (PK)
  - `fk_produto`: FK para produtos
  - `patrimonio`: Número de patrimônio (único)
  - `serie`: Número de série
  - `fk_secao`: Seção responsável
  - `condicao`: Estado (novo, bom, danificado, etc.)
  - `data_entrada`: Data de entrada
  - `data_saida`: Data de saída
  - `quantidade_cautelada`: Quantidade emprestada
  - `observacao`: Observações adicionais

#### **Tabela: containers**
- **Objetivo**: Armazenar informações sobre containers/recipientes de armazenamento
- **Campos**:
  - `id` (PK)
  - `fk_produto`: FK para produtos
  - `tipo`: Tipo de container (Bolsa, Prateleira, Caixa, Armário)
  - `material`: Material (Plástico, Metal, Madeira, Tecido)
  - `capacidade_maxima`: Capacidade máxima
  - `unidade_capacidade`: Unidade (kg, unidades, litros)
  - `compartimentos`: Número de divisões
  - `cor`: Cor do container
  - `numero_serie`: Número de série (único)
  - `descricao_adicional`: Descrição extra
  - `status`: ativo, danificado, em_reparo, inativo

---

### 5. **FOTOS DE ITENS**

#### **Tabela: item_fotos**
- **Objetivo**: Armazenar fotos de produtos, itens de estoque e patrimônios
- **Campos**:
  - `id` (PK)
  - `fk_itens_estoque`: FK para itens_estoque (nullable)
  - `fk_iten_patrimonial`: FK para itens_patrimoniais (nullable)
  - `fk_produto`: FK para produtos (nullable)
  - `caminho_arquivo`: Caminho do arquivo no storage
  - `nome_original`: Nome original do arquivo
  - `tipo_mime`: Tipo MIME da imagem
  - `tamanho`: Tamanho do arquivo em bytes
  - `ordem`: Ordem de exibição
  - `created_at`, `updated_at`: Timestamps
- **Relacionamentos**: 
  - One-to-Many com produtos (até 1 foto por produto)
  - One-to-Many com itens_estoque (até 3 fotos para consumo)
  - One-to-Many com itens_patrimoniais (até 2 fotos por patrimônio)

---

### 6. **CAUTELAS (EMPRÉSTIMOS)**

#### **Tabela: cautelas**
- **Objetivo**: Registrar empréstimos de materiais
- **Campos**:
  - `id` (PK)
  - `nome_responsavel`: Pessoa responsável
  - `telefone`: Contato
  - `instituicao`: Instituição/unidade que pega emprestado
  - `responsavel_unidade`: Responsável pela unidade
  - `data_cautela`: Data do empréstimo
  - `data_prevista_devolucao`: Data esperada de devolução

#### **Tabela: cautela_produto**
- **Objetivo**: Itens específicos em cada cautela
- **Campos**:
  - `id` (PK)
  - `cautela_id`: FK para cautelas
  - `produto_id`: FK para produtos
  - `estoque_id`: FK para itens_estoque (opcional)
  - `iten_patrimonial_id`: FK para itens_patrimoniais (opcional)
  - `quantidade`: Quantidade emprestada
  - `quantidade_devolvida`: Quantidade devolvida
  - `data_devolucao`: Data da devolução
- **Dados**: 2 cautelas com produtos associados

#### **Tabela: efetivo_militar_produto**
- **Objetivo**: Rastrear produtos entregues a militares
- **Campos**:
  - `id` (PK)
  - `fk_efetivo_militar`: FK para efetivo_militar
  - `fk_produto`: FK para produtos
  - `entregue`: SIM/NAO - Se foi entregue

---

### 7. **HISTÓRICO E MOVIMENTAÇÕES**

#### **Tabela: historico_movimentacoes**
- **Objetivo**: Registrar todas as movimentações de estoque para auditoria
- **Campos principais**:
  - `id` (PK)
  - `fk_produto`: FK para produtos
  - `tipo_movimentacao`: entrada, saída, transferência, saída_kit, saída_manual_multipla, desfazer
  - `quantidade`: Quantidade movimentada
  - `data_movimentacao`: Data/hora da movimentação
  - `responsavel`: Pessoa responsável
  - `movimentacao_origem_id`: ID da movimentação original (para desfazer)
  - `fk_unidade`: Unidade origem
  - `unidade_origem`: ID da unidade de origem
  - `unidade_destino`: ID da unidade destino
  - `observacao`: Motivo ou observação
  - `sei`: Número SEI
  - `nota_fiscal`: Número da nota fiscal
  - `fornecedor`: Fornecedor (para entradas)
  - `militar`: Militar envolvido
  - `setor`: Setor envolvido
  - `data_trp`: Data da TRP
  - `valor_total`: Valor da movimentação
  - `valor_unitario`: Valor por unidade
  - `lote_saida`: Lote de saída
  - `movimentacao_origem_id`: ID da movimentação original
- **Dados**: 5 movimentações registradas

---

### 7. **CONFIGURAÇÕES**

#### **Tabela: config**
- **Objetivo**: Armazenar configurações do sistema
- **Campos**:
  - `id` (PK)
  - `upf`: Valor UPF (Unidade de Preço Fiscal)
  - `acp`: Valor ACP (Adicional de Custos Pessoal)
  - `renovacao_legacy`: Usa sistema legado (s/n)

#### **Tabela: fontes**
- **Objetivo**: Listar fontes/origem dos produtos
- **Campos**:
  - `id` (PK)
  - `nome`: Nome da fonte
- **Dados**: 2 fontes cadastradas

---

### 8. **TABELAS AUXILIARES (LARAVEL)**

#### **Tabela: condicoes**
- **Objetivo**: Estados/condições de itens
- **Campos**: id, condicao
- **Dados**: 7 condições

#### **Tabela: migrations**
- **Objetivo**: Versionamento de schema do banco
- Registra todas as migrações executadas

#### **Tabela: audits**
- **Objetivo**: Auditoria de mudanças (spatie/laravel-audit)
- Registra todas as ações de criação, edição e deleção

#### **Tabela: cache, cache_locks**
- **Objetivo**: Cache de aplicação

#### **Tabela: jobs, failed_jobs**
- **Objetivo**: Fila de jobs assincronos

#### **Tabela: password_reset_tokens, personal_access_tokens**
- **Objetivo**: Recuperação de senha e tokens API

---

## 📊 DIAGRAMA DE RELACIONAMENTOS

```
users ◄──┬─── perfis ◄─── perfil_permissao ──► permissoes ──► modulos
         └─── unidades ◄─┬─── secaos
                         ├─── efetivo_militar
                         └─── kits
                             
categorias ◄─┬─── produtos ◄─┬─── itens_estoque ◄─┬─ cautela_produto ──► cautelas
             └─ tipoprodutos │                     └─ item_fotos
                             ├─── itens_patrimoniais ◄─ item_fotos
                             ├─── containers
                             ├─── historico_movimentacoes
                             ├─── efetivo_militar_produto
                             └─── item_fotos

itens_estoque ◄─── historico_movimentacoes
```

---

## 🔑 RELACIONAMENTOS PRINCIPAIS

| Tabela A | Tabela B | Tipo | Campo FK |
|----------|----------|------|----------|
| users | perfis | N:1 | fk_perfil |
| users | unidades | N:1 | fk_unidade |
| produtos | categorias | N:1 | fk_categoria |
| produtos | kits | N:1 | fk_kit |
| produtos | tamanhos | N:1 | tamanho |
| itens_estoque | produtos | N:1 | fk_produto |
| itens_estoque | secaos | N:1 | fk_secao |
| itens_estoque | itens_estoque | 1:N | fk_item_pai |
| itens_patrimoniais | produtos | N:1 | fk_produto |
| itens_patrimoniais | secaos | N:1 | fk_secao |
| containers | produtos | N:1 | fk_produto |
| cautela_produto | cautelas | N:1 | cautela_id |
| cautela_produto | produtos | N:1 | produto_id |
| cautela_produto | itens_estoque | N:1 | estoque_id |
| cautela_produto | itens_patrimoniais | N:1 | iten_patrimonial_id |
| item_fotos | produtos | N:1 | fk_produto |
| item_fotos | itens_estoque | N:1 | fk_itens_estoque |
| item_fotos | itens_patrimoniais | N:1 | fk_iten_patrimonial |
| historico_movimentacoes | produtos | N:1 | fk_produto |
| historico_movimentacoes | unidades | N:1 | fk_unidade |
| historico_movimentacoes | historico_movimentacoes | N:1 | movimentacao_origem_id |
| secaos | unidades | N:1 | fk_unidade |
| efetivo_militar | unidades | N:1 | fk_unidade |
| efetivo_militar_produto | efetivo_militar | N:1 | fk_efetivo_militar |
| efetivo_militar_produto | produtos | N:1 | fk_produto |
| kits | unidades | N:1 | fk_unidade |
| permissoes | modulos | N:1 | modulo |
| perfil_permissao | perfis | N:1 | fk_perfil |
| perfil_permissao | permissoes | N:1 | fk_permissao |

---

## 🎯 FUNCIONALIDADES DO SISTEMA

### **1. AUTENTICAÇÃO E CONTROLE DE ACESSO**
- ✅ Login com email e senha
- ✅ Sistema de perfis (Admin, Gestor, Usuário)
- ✅ Permissões por módulo
- ✅ Recuperação de senha
- ✅ Gestão de usuários

### **2. CADASTRO DE PRODUTOS**
- ✅ Criar, editar, listar e deletar produtos
- ✅ Categorização de produtos
- ✅ Tamanhos e marcas
- ✅ Tipo de controle (consumo vs permanente)
- ✅ Agrupamento em kits
- ✅ Status ativo/inativo
- ✅ Upload de foto do produto (até 1 imagem por produto)

### **3. GESTÃO DE ESTOQUE**
- ✅ Controle de quantidade por produto
- ✅ Itens patrimoniais com número
- ✅ Controle por seção
- ✅ Preço unitário e total
- ✅ Lote e fornecedor
- ✅ Data de entrada/saída
- ✅ Estrutura hierárquica (itens pai/filho)
- ✅ Upload de fotos na entrada (até 3 para itens de consumo, 2 por patrimônio para permanentes)
- ✅ Galeria de fotos por item no estoque
- ✅ Visualização unificada de estoque (consumo + permanente)

### **4. MOVIMENTAÇÕES DE ESTOQUE**
- ✅ Entrada de produtos
- ✅ Saída de produtos
- ✅ Transferência entre unidades
- ✅ Saída de kit
- ✅ Saída manual múltipla
- ✅ Histórico completo auditável
- ✅ Rastreamento de responsável
- ✅ Desfazer movimentações (entrada/saída)
- ✅ Indicador visual de movimentações desfeitas
- ✅ Suporte a itens permanentes e de consumo

### **5. CAUTELAS (EMPRÉSTIMOS)**
- ✅ Criar cautelas de empréstimo
- ✅ Associar múltiplos produtos
- ✅ Controlar quantidade emprestada e devolvida
- ✅ Data prevista de devolucão
- ✅ Rastrear responsáveis
- ✅ Status de devolução
- ✅ Suporte para itens permanentes com seleção de patrimônio
- ✅ Controle de quantidade cautelada por item/patrimônio
- ✅ Sistema de devolução parcial e total

### **6. ITENS PATRIMONIAIS**
- ✅ Número de patrimônio único
- ✅ Número de série
- ✅ Condição do item
- ✅ Controle de cautela por patrimônio
- ✅ Rastreamento por seção
- ✅ Fotos individuais por patrimônio (até 2 fotos)
- ✅ Controle de saída permanente (baixa)
- ✅ Observações por item

### **7. CONTAINERS/RECIPIENTES**
- ✅ Tipos de containers (caixa, prateleira, armário)
- ✅ Material de fabricação
- ✅ Capacidade máxima
- ✅ Número de série
- ✅ Status (ativo, danificado, em reparo)
- ✅ Compartimentos

### **8. ESTRUTURA ORGANIZACIONAL**
- ✅ Múltiplas unidades militares
- ✅ Seções por unidade
- ✅ Cadastro de militares
- ✅ Associação de usuários a unidades
- ✅ Responsáveis por seção

### **9. RELATÓRIOS E AUDITORIA**
- ✅ Histórico completo de movimentações
- ✅ Auditoria de mudanças (criação, edição, deleção)
- ✅ Rastreamento de responsáveis
- ✅ SEI (Sistema Eletrônico de Informações)
- ✅ Nota fiscal e fornecedor

### **10. CONFIGURAÇÕES**
- ✅ UPF (Unidade de Preço Fiscal)
- ✅ ACP (Adicional de Custos Pessoal)
- ✅ Compatibilidade com sistema legado

### **11. SISTEMA DE FOTOS**
- ✅ Upload de foto no cadastro de produto (1 por produto)
- ✅ Upload de fotos na entrada de estoque:
  - Até 3 fotos para itens de consumo
  - Até 2 fotos por patrimônio para itens permanentes
- ✅ Galeria de fotos na página de detalhes do produto
- ✅ Validação de formato (JPG, PNG, GIF) e tamanho (máx 5MB)
- ✅ Armazenamento organizado em `storage/app/public/`
- ✅ Limpeza automática de arquivos órfãos ao deletar registros
- ✅ URLs públicas para exibição de imagens

---

## 📈 ESTATÍSTICAS DO BANCO DE DADOS

| Tabela | Registros |
|--------|-----------|
| users | 3 |
| perfis | 2 |
| unidades | 24 |
| efetivo_militar | 685 |
| categorias | 11 |
| produtos | 2 |
| tamanhos | 17 |
| itens_estoque | 5 |
| itens_patrimoniais | - |
| item_fotos | - |
| historico_movimentacoes | 5 |
| kits | 1 |
| cautelas | 0 |
| cautela_produto | 2 |
| condicoes | 7 |
| modulos | 5 |
| permissoes | 7 |
| fontes | 2 |
| containers | - |

**Total de tabelas**: 42+

---

## 🔒 SEGURANÇA

1. **Hash de Senhas**: Senhas armazenadas com bcrypt
2. **Auditoria**: Rastreamento completo com tabela audits
3. **Permissões**: Sistema granular de permissões por módulo
4. **Tokens**: Suporte a personal access tokens (API)
5. **Validação**: Constrains nas chaves estrangeiras
6. **Índices**: Índices em FKs para performance

---

## 🎨 TIPOS DE DADOS UTILIZADOS

| Tipo | Uso |
|------|-----|
| BIGINT | IDs e FKs principais |
| INT | IDs menores, unidades |
| VARCHAR | Textos curtos (nomes, descricões) |
| TEXT | Textos longos (observações) |
| ENUM | Valores predefinidos (status, tipo) |
| DECIMAL | Valores monetários e preços |
| DATE/DATETIME | Datas e horários |
| JSON | Dados de auditoria |
| CHAR | CPF (formato fixo) |

---

## 🚀 FUNCIONALIDADES AVANÇADAS

### **Controle de Quantities**
- Quantidade total em estoque
- Quantidade cautelada (emprestada)
- Quantidade disponível = total - cautelada

### **Hierarquia de Itens**
- Itens podem ter itens pai
- Estrutura de árvore para compostos

### **Transferência Entre Unidades**
- Rastreia origem e destino
- Registra responsável
- Mantém histórico

### **Múltiplas Saídas**
- Saída simples (um produto)
- Saída de kit (múltiplos produtos)
- Saída manual múltipla (vários produtos diferentes)

### **Auditoria Completa**
- Quem criou
- Quem modificou
- Quando foi criado/modificado
- Quais campos foram alterados

---

## 📝 CAMPOS AUDITÁVEIS

Todas as tabelas possuem:
- `created_at`: Data de criação
- `updated_at`: Data de última atualização
- Tabela `audits`: Registra todas as mudanças

---

## 🔄 FLUXOS PRINCIPAIS

### **Fluxo de Entrada de Produto**
1. Criar produto em `produtos`
2. Registrar item em `itens_estoque`
3. Registrar movimentação em `historico_movimentacoes`
4. Auditar em `audits`

### **Fluxo de Cautela**
1. Criar cautela em `cautelas`
2. Adicionar produtos em `cautela_produto`
3. Atualizar `quantidade_cautelada` em `itens_estoque`
4. Devolução: atualizar `quantidade_devolvida`

### **Fluxo de Transferência**
1. Criar movimentação com tipo `transferencia`
2. Especificar `unidade_origem` e `unidade_destino`
3. Atualizar estoque nas duas unidades
4. Auditar mudanças

### **Fluxo de Patrimônio**
1. Registrar item em `itens_patrimoniais`
2. Associar a um `fk_produto`
3. Controlar numero de patrimônio (único)
4. Rastrear cautelas de patrimônio

---

## 📚 DOCUMENTAÇÃO DE CAMPOS ESPECIAIS

### **tipo_controle (produtos)**
- `consumo`: Produto de consumo (reduz quantidade)
- `permanente`: Bem permanente (patrimônio)

### **tipo_movimentacao (histórico_movimentacoes)**
- `entrada`: Recebimento de produtos
- `saída`: Saída de produtos
- `transferência`: Movimentação entre unidades
- `saída_kit`: Saída de todos os itens de um kit
- `saída_manual_multipla`: Saída de múltiplos produtos
- `desfazer`: Reversão de movimentação anterior

### **status (users, perfis, containers)**
- `s` ou `S`: Ativo/Sim
- `n` ou `N`: Inativo/Não

### **unidade (produtos)**
- `UN`: Unidade
- `CX`: Caixa
- `PCT`: Pacote
- `KG`: Quilograma
- `LT`: Litro

### **entregue (efetivo_militar_produto)**
- `SIM`: Produto entregue
- `NAO`: Ainda não entregue

---

## � ATUALIZAÇÕES RECENTES (Janeiro-Fevereiro 2026)

### **Gestão de Itens Patrimoniais**
- ✅ Implementado controle de itens permanentes com número de patrimônio
- ✅ Sistema de cautela para patrimônios com seleção individual
- ✅ Controle de saída permanente (baixa) de patrimônios
- ✅ Fotos individuais por patrimônio (até 2 por item)
- ✅ Observações específicas por patrimônio

### **Sistema de Fotos**
- ✅ Upload de foto no cadastro de produto
- ✅ Upload de fotos na entrada de estoque:
  - Até 3 fotos para itens de consumo
  - Até 2 fotos por patrimônio para permanentes
- ✅ Galeria de fotos na página de detalhes
- ✅ Validação de formato e tamanho
- ✅ Limpeza automática de arquivos órfãos

### **Movimentações Aprimoradas**
- ✅ Função de desfazer movimentações (entrada/saída)
- ✅ Suporte completo para itens permanentes
- ✅ Indicador visual de movimentações desfeitas
- ✅ Rastreamento de movimentação origem

### **Melhorias na Interface**
- ✅ Visualização unificada de estoque (consumo + permanente)
- ✅ Lista de patrimônios movida para o final do formulário
- ✅ Campos dinâmicos baseados em tipo de controle
- ✅ Validações client-side e server-side aprimoradas

### **Banco de Dados**
- ✅ Nova tabela: `item_fotos` (fotos de produtos/estoque/patrimônios)
- ✅ Novo campo: `iten_patrimonial_id` em `cautela_produto`
- ✅ Novo campo: `fk_produto` em `item_fotos`
- ✅ Relacionamentos cascade delete para integridade

---

## �🎯 PRÓXIMOS PASSOS PARA DESENVOLVIMENTO

1. ✅ Implementar dashboards com estatísticas
2. ✅ Gerar relatórios em PDF
3. ✅ Integração com SEI
4. ✅ App mobile para consulta
5. ✅ Código de barras para produtos
6. ✅ Backup automático
7. ✅ Notificações de vencimento de cautelas
8. ✅ Sincronização entre unidades

---

## 📞 SUPORTE

Para dúvidas sobre a estrutura do banco de dados ou funcionalidades, consulte:
- ARQUITETURA.md - Decisões arquiteturais
- EVOLUTION.md - Histórico de alterações
- Migrations em `database/migrations/`

---

**Última atualização**: 2024
**Versão do Laravel**: 11.x
**Banco de Dados**: MySQL 5.7+

