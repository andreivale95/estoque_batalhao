<?php

use App\Models\Cidade;
use App\Models\Empresa;
use App\Models\EmpresaPorte;
use App\Models\EmpresaTipo;
use App\Models\Permissao;
use App\Models\PessoaFisica;
use App\Models\Processo;
use App\Models\Regional;
use App\Models\RegionalCidade;
use App\Models\Sat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Utils;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\SecaoController;
use App\Http\Controllers\ProdutoController;

Route::get('/secoes/{secao}/items', [SecaoController::class, 'getItems']);
Route::get('/produtos/{id}/detalhes-secao', [ProdutoController::class, 'getDetalhesPorSecao']);

use function Psy\debug;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

require __DIR__ . '/apiRelatorios.php';


Route::get('/', function (Request $request) {
    return json_encode([
        'message' => "Hello API",
        'data' => [
            'name' => 'adriano',
            'nascimento' => '16/08/1999'
        ]
    ]);
});

Route::get('/sats', function (Request $request) {
    $c = Sat::all();
    return json_encode($c);
});

Route::post('/renovacaoAnual', function (Request $request) {
    try {
        // consulta na base se é renovação ou primeira certificação
        $res = Utils::RenovacaoAnual($request->get('cnpj'));
        Log::info('api renovação', [$res, $request->get('cnpj')]);
        return $res;
    } catch (Exception $e) {
        Log::error('erro ao consultar renovação anual', [$e]);
        return -1;
    }
});

Route::get('/regional/cidades/{regional}', function (Request $request, $regional) {
    $c = RegionalCidade::query()
        ->where('fk_regional', $regional)
        ->get();
    return json_encode($c);
});

Route::get('/permissoes/perfil/{perfil}', function (Request $request, $perfil) {
    $p = Permissao::query()
        ->join('perfil_permissao', 'id_permissao', '=', 'fk_permissao')
        ->where('fk_perfil', $perfil)
        ->get();
    return json_encode($p);
});

Route::get('/usuarios/sat/{sat}', function (Request $request, $sat) {
    $users = User::query()
        ->where('fk_sat', $sat)
        ->where('tipo', 'i')
        ->where('status', 's')
        ->get();
    return json_encode([
        'usuarios' => $users,
    ]);
});

Route::get('/isento/tipo/{tipo}/porte/{porte}', function (Request $request, $tipo, $porte) {
    $et = EmpresaTipo::where('id_empresa_tipo', $tipo)->first();
    $ep = EmpresaPorte::where('id_empresa_porte', $porte)->first();

    if ($et->isento == 's')
        return json_encode([
            'isento' => 's'
        ]);
    if ($ep->isento == 's')
        return json_encode([
            'isento' => 's'
        ]);
    return json_encode([
        'isento' => 'n'
    ]);
});

Route::get('/calc-cadastro/porte/{porte}', function (Request $request, $porte) {
    $ep = EmpresaPorte::where('id_empresa_porte', $porte)->first();

    return json_encode([
        'porte' => $ep->titulo,
        'valor' => $ep->valor
    ]);
});

Route::get('/calc-taxa/area/{area}/tipo/{tipo}', function (Request $request, $area, $tipo) {
    $valor = Utils::calcularTaxa($area, $tipo);

    return json_encode([
        'valor' => $valor
    ]);
});

Route::get('/cnpj/{cnpj}', function (Request $request, $cnpj) {

    try {

        $api_receita = "https://receitaws.com.br/v1/cnpj/$cnpj";

        if (Empresa::where('cnpj', $cnpj)->exists()) {

            $empresa = Empresa::where('cnpj', $cnpj)->first();
            $endereco = $empresa->endereco()->first();
            $res = [
                'cnpj' => $empresa->cnpj,
                'razao_social' => $empresa->razao_social,
                'nome_fantazia' => $empresa->nome_fantazia,
                'telefone' => $empresa->telefone,
                'email' => $empresa->email,
                'atividade_principal' => $empresa->atividade_principal,
                'responsavel' => $empresa->responsavel,
                'fk_empresa_porte' => $empresa->fk_empresa_porte,

                'cep' => $endereco->cep,
                'estado' => $endereco->estado,
                'cidade' => $endereco->cidade,
                'bairro' => $endereco->bairro,
                'endereco' => $endereco->endereco,
                'numero' => $endereco->numero,
                'complemento' => $endereco->complemento,
            ];

            return response($res);
        } else {

            $resultado = Http::get($api_receita, []);

            if ($resultado) {
                $res = [
                    'cnpj' => $resultado['cnpj'],
                    'razao_social' => $resultado['nome'],
                    'nome_fantazia' => $resultado['fantasia'],
                    'atividade_principal' => $resultado['atividade_principal'][0]['text'],
                    'cnae' => $resultado['atividade_principal'][0]['code'],
                    'email' => $resultado['email'],
                    'telefone' => $resultado['telefone'],

                    'cep' => $resultado['cep'],
                    'estado' => $resultado['uf'],
                    'cidade' => $resultado['municipio'],
                    'bairro' => $resultado['bairro'],
                    'endereco' => $resultado['logradouro'],
                    'numero' => $resultado['numero'],
                    'complemento' => $resultado['complemento'],
                ];


                return response($res, 200);
            }

            return response("not found", 404);
        }
    } catch (Exception $exception) {
        return json_encode([
            "exception" => $exception
        ]);
    } finally {
    }
});

Route::get('/cpf/{cpf}', function (Request $request, $cpf) {

    try {
        $pessoa = PessoaFisica::where('cpf', $cpf)->first();
        $endereco = $pessoa->endereco()->first();

        $res = [

            'cpf' => $pessoa->cpf,
            'nome' => $pessoa->nome,
            'email' => $pessoa->email,
            'telefone' => $pessoa->telefone,

            'cep' => $endereco->cep,
            'estado' => $endereco->estato,
            'cidade' => $endereco->cidade,
            'bairro' => $endereco->bairro,
            'endereco' => $endereco->endereco,
            'numero' => $endereco->numero,
            'complemento' => $endereco->complemento,
        ];

        if ($res)
            return response()->json($res);
        return response("not found", 404);
    } catch (Exception $exception) {
        return json_encode([
            "exception" => $exception
        ]);
    }
});
