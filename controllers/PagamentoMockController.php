<?php

namespace app\controllers;

use Yii;
use app\services\PagamentoService;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\ActiveController;

/**
 * Controller da API REST para simulação de Pagamentos Mock.
 */
class PagamentoMockController extends ActiveController
{
    public $modelClass = 'app\models\PagamentoMock';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options'],
        ];

        return $behaviors;
    }

    /**
     * Processa um pagamento simulado via POST /pagamentos/mock.
     */
    public function actionProcessar()
    {
        $dados = Yii::$app->request->getBodyParams();
        $usuarioId = Yii::$app->user->id ?? null;

        $service = new PagamentoService();
        return $service->processarPagamento($dados, $usuarioId);
    }

    /**
     * Processa um pagamento simulado via POST /pedidos/{id}/pagar.
     */
    public function actionPagarPedido($id)
    {
        $dados = Yii::$app->request->getBodyParams();
        $dados['pedidoId'] = $id;
        $usuarioId = Yii::$app->user->id ?? null;

        $service = new PagamentoService();
        return $service->processarPagamento($dados, $usuarioId);
    }
}
