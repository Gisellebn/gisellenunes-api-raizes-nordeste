<?php

namespace app\services;

use Yii;
use app\models\Fidelidade;
use app\models\PagamentoMock;
use app\models\Pedido;
use app\models\Usuario;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

/**
 * Serviço de Aplicação responsável pelo processamento de pagamentos mock e fidelidade.
 */
class PagamentoService
{
    /**
     * Processa um pagamento simulado para um pedido.
     *
     * @param array $dados
     * @param int|null $usuarioId
     * @return array
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     */
    public function processarPagamento(array $dados, ?int $usuarioId = null): array
    {
        $pedidoId = $dados['pedidoId'] ?? $dados['pedido_id'] ?? null;
        if (empty($pedidoId)) {
            throw new UnprocessableEntityHttpException('O campo pedidoId é obrigatório.');
        }

        $pedido = Pedido::findOne($pedidoId);
        if (!$pedido) {
            throw new NotFoundHttpException("Pedido {$pedidoId} não encontrado.");
        }

        $simularRecusa = (bool) ($dados['simularRecusa'] ?? false);
        $valor = isset($dados['valor']) ? (float) $dados['valor'] : (float) $pedido->valor_total;
        $recusado = $simularRecusa || $valor > 500.0;

        $statusPagamento = $recusado ? 'RECUSADO' : 'APROVADO';
        $novoStatusPedido = $recusado ? 'PAGAMENTO_RECUSADO' : 'PAGO';

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $pagamento = new PagamentoMock();
            $pagamento->pedido_id = $pedido->id;
            $pagamento->valor = $valor;
            $pagamento->status = $statusPagamento;
            $pagamento->metodo = $dados['metodo'] ?? 'PIX';
            $pagamento->save(false);

            $statusAnterior = $pedido->status;
            $pedido->status = $novoStatusPedido;
            $pedido->save(false);

            $pontosCreditados = 0;
            if (!$recusado && $pedido->usuario_id) {
                $usuario = Usuario::findOne($pedido->usuario_id);
                if ($usuario && $usuario->aceita_termos_lgpd) {
                    $pontosCreditados = (int) floor($valor);
                    $fidelidade = Fidelidade::findOne(['usuario_id' => $usuario->id]);
                    if (!$fidelidade) {
                        $fidelidade = new Fidelidade();
                        $fidelidade->usuario_id = $usuario->id;
                        $fidelidade->pontos = 0;
                    }
                    $fidelidade->pontos = (int) $fidelidade->pontos + $pontosCreditados;
                    $fidelidade->save(false);
                }
            }

            AuditoriaService::registrar(
                $usuarioId ?? $pedido->usuario_id,
                'TENTATIVA_PAGAMENTO',
                'PagamentoMock',
                $pagamento->id,
                [
                    'pedido_id' => $pedido->id,
                    'status_pagamento' => $statusPagamento,
                    'status_pedido_anterior' => $statusAnterior,
                    'status_pedido_novo' => $novoStatusPedido,
                    'valor' => $valor,
                    'pontos_creditados' => $pontosCreditados,
                ]
            );

            $transaction->commit();

            return [
                'pagamentoId' => $pagamento->id,
                'pedidoId' => $pedido->id,
                'statusPagamento' => $statusPagamento,
                'statusPedido' => $novoStatusPedido,
                'valor' => $valor,
                'pontosCreditados' => $pontosCreditados,
            ];
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}

