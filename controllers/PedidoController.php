<?php

namespace app\controllers;

use Yii;
use app\models\Pedido;
use app\services\AuditoriaService;
use app\services\PedidoService;
use yii\data\ActiveDataProvider;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\Cors;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

/**
 * Controller da API REST para gerenciamento de Pedidos Multicanal.
 */
class PedidoController extends ActiveController
{
    public $modelClass = 'app\models\Pedido';

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['http://localhost:5173', 'http://localhost:8080'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options'],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index'], $actions['create'], $actions['delete']);
        return $actions;
    }

    /**
     * Lista pedidos com filtros opcionais por canalPedido, status e paginação.
     * GET /pedidos?canalPedido=APP&status=PAGO&page=1&limit=10
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $query = Pedido::find()->orderBy(['id' => SORT_DESC]);

        $canal = $request->get('canalPedido') ?? $request->get('canal_pedido');
        if (!empty($canal)) {
            $query->andWhere(['canal_pedido' => $canal]);
        }

        $status = $request->get('status');
        if (!empty($status)) {
            $query->andWhere(['status' => $status]);
        }

        $limit = max(1, min(100, (int) $request->get('limit', 10)));
        $page = max(0, ((int) $request->get('page', 1)) - 1);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
                'page' => $page,
            ],
        ]);
    }

    /**
     * Cria um novo pedido validando canalPedido e estoque disponível.
     * POST /pedidos
     */
    public function actionCreate()
    {
        $dados = Yii::$app->request->getBodyParams();
        $usuarioId = Yii::$app->user->id ?? null;

        $service = new PedidoService();
        $pedido = $service->criarPedido($dados, $usuarioId);

        Yii::$app->response->setStatusCode(201);
        return $pedido;
    }

    /**
     * Cancela um pedido e registra log de auditoria.
     * DELETE /pedidos/{id}
     */
    public function actionDelete($id)
    {
        $pedido = Pedido::findOne($id);
        if (!$pedido) {
            throw new NotFoundHttpException("Pedido {$id} não encontrado.");
        }

        $statusAnterior = $pedido->status;
        $pedido->status = 'CANCELADO';
        $pedido->save(false);

        AuditoriaService::registrar(
            Yii::$app->user->id ?? null,
            'CANCELAMENTO_PEDIDO',
            'Pedido',
            $pedido->id,
            ['status_anterior' => $statusAnterior, 'status_novo' => 'CANCELADO']
        );

        return $pedido;
    }
}
