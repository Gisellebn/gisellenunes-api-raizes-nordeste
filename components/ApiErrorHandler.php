<?php

namespace app\components;

use Yii;
use yii\web\ErrorHandler;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Manipulador global de erros da API REST.
 * Padroniza todas as respostas de erro HTTP em JSON.
 */
class ApiErrorHandler extends ErrorHandler
{
    /**
     * Mapeamento de códigos HTTP para códigos de erro padronizados.
     */
    private const ERROR_CODES = [
        400 => 'REQUISICAO_INVALIDA',
        401 => 'NAO_AUTORIZADO',
        403 => 'ACESSO_NEGADO',
        404 => 'NAO_ENCONTRADO',
        405 => 'METODO_NAO_PERMITIDO',
        409 => 'CONFLITO_DE_ESTOQUE_OU_ESTADO',
        422 => 'ERRO_DE_VALIDACAO',
        500 => 'ERRO_INTERNO_SERVIDOR',
    ];

    /**
     * Renderiza a exceção no formato JSON padrão exigido pela API.
     *
     * @param \Throwable|\Exception $exception
     */
    protected function renderException($exception)
    {
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
            $response->isSent = false;
            $response->stream = null;
            $response->data = null;
            $response->content = null;
        } else {
            $response = new Response();
        }

        $statusCode = $exception instanceof HttpException ? $exception->statusCode : 500;
        $response->setStatusCode($statusCode);
        $response->format = Response::FORMAT_JSON;

        $errorCode = self::ERROR_CODES[$statusCode] ?? 'ERRO_DESCONHECIDO';
        $message = $exception->getMessage() ?: 'Ocorreu um erro ao processar a requisição.';

        $details = [];
        if (YII_DEBUG && $statusCode === 500) {
            $details[] = [
                'type' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ];
        }

        $requestPath = Yii::$app->has('request') ? '/' . ltrim(Yii::$app->request->getPathInfo(), '/') : '/';

        $response->data = [
            'error' => $errorCode,
            'message' => $message,
            'details' => $details,
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            'path' => $requestPath,
        ];

        $response->send();
    }
}
