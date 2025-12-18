<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Produto;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    /**
     * Criar container para um produto
     */
    public function criar(Request $request)
    {
        $validated = $request->validate([
            'fk_produto' => 'required|exists:produtos,id',
            'tipo' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'capacidade_maxima' => 'nullable|numeric|min:0',
            'unidade_capacidade' => 'nullable|string|max:50',
            'compartimentos' => 'nullable|integer|min:0',
            'cor' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|unique:containers',
            'descricao_adicional' => 'nullable|string',
            'status' => 'nullable|in:ativo,danificado,em_reparo,inativo',
        ]);

        $container = Container::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Container criado com sucesso!',
            'container' => $container,
        ]);
    }

    /**
     * Atualizar dados do container
     */
    public function atualizar(Request $request, Container $container)
    {
        $validated = $request->validate([
            'tipo' => 'nullable|string|max:255',
            'material' => 'nullable|string|max:255',
            'capacidade_maxima' => 'nullable|numeric|min:0',
            'unidade_capacidade' => 'nullable|string|max:50',
            'compartimentos' => 'nullable|integer|min:0',
            'cor' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|unique:containers,numero_serie,' . $container->id,
            'descricao_adicional' => 'nullable|string',
            'status' => 'nullable|in:ativo,danificado,em_reparo,inativo',
        ]);

        $container->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Container atualizado com sucesso!',
            'container' => $container,
        ]);
    }

    /**
     * Obter dados do container
     */
    public function obter(Container $container)
    {
        return response()->json($container);
    }

    /**
     * Deletar container
     */
    public function deletar(Container $container)
    {
        $container->delete();

        return response()->json([
            'success' => true,
            'message' => 'Container deletado com sucesso!',
        ]);
    }
}
