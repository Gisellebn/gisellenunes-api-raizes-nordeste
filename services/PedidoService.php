<?php

namespace app\services;

use Yii;
use app\models\EstoqueUnidade;
use app\models\Pedido;
use app\models\PedidoItem;
use app\models\Produto;
use app\models\Unidade;
use yii\web\ConflictHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

/**
 * Serviço de Aplicação responsável pelo fluxo de pedidos multicanal e controle de estoque.
 */
class PedidoService
{
    public const CANAIS_VALIDOS = ['APP', 'TOTEM', 'BALCAO', 'PICKUP', 'WEB'];

    /**
     * Cria um novo pedido validando canal, unidade, produtos e estoque disponível.
     *
     * @param array $dados
     * @param int|null $usuarioId
     * @return Pedido
     * @throws UnprocessableEntityHttpException
     * @throws NotFoundHttpException
     * @throws ConflictHttpException
     */
    public function criarPedido(array $dados, ?int $usuarioId = null): Pedido
    {
        $canal = $dados['canalPedido'] ?? $dados['canal_pedido'] ?? null;
        if (empty($canal) || !in_array($canal, self::CANAIS_VALIDOS, true)) {
            throw new UnprocessableEntityHttpException(
                'O campo canalPedido é obrigatório e deve ser um dos seguintes valores: ' . implode(', ', self::CANAIS_VALIDOS) . '.'
            );
        }

        $unidadeId = $dados['unidadeId'] ?? $dados['unidade_id'] ?? null;
        if (empty($unidadeId)) {
            throw new UnprocessableEntityHttpException('O campo unidadeId é obrigatório.');
        }

        $unidade = Unidade::findOne($unidadeId);
        if (!$unidade) {
            throw new NotFoundHttpException("Unidade {$unidadeId} não encontrada.");
        }

        $itens = $dados['itens'] ?? [];
        if (empty($itens) || !is_array($itens)) {
            throw new UnprocessableEntityHttpException('O pedido deve conter ao menos um item.');
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $pedido = new Pedido();
            $pedido->usuario_id = $usuarioId ?? ($dados['usuario_id'] ?? null);
            $pedido->unidade_id = (int) $unidadeId;
            $pedido->canal_pedido = $canal;
            $pedido->status = 'AGUARDANDO_PAGAMENTO';
            $pedido->valor_total = 0;

            if (!$pedido->save()) {
                throw new UnprocessableEntityHttpException('Dados inválidos ao criar pedido.');
            }

            $valorTotal = 0.0;

            foreach ($itens as $itemData) {
                $produtoId = $itemData['produtoId'] ?? $itemData['produto_id'] ?? null;
                $quantidade = (int) ($itemData['quantidade'] ?? 0);

                if (empty($produtoId) || $quantidade <= 0) {
                    throw new UnprocessableEntityHttpException('Cada item deve informar produtoId e quantidade maior que zero.');
                }

                $produto = Produto::findOne($produtoId);
                if (!$produto) {
                    throw new NotFoundHttpException("Produto {$produtoId} não encontrado.");
                }

                $estoque = EstoqueUnidade::findOne([
                    'unidade_id' => $unidadeId,
                    'produto_id' => $produtoId,
                ]);

                if (!$estoque || (int) $estoque->quantidade_disponivel < $quantidade) {
                    throw new ConflictHttpException(
                        "Estoque insuficiente para o produto '{$produto->nome}' na unidade selecionada."
                    );
                }

                $estoque->quantidade_disponivel = (int) $estoque->quantidade_disponivel - $quantidade;
                $estoque->save(false);

                $item = new PedidoItem();
                $item->pedido_id = $pedido->id;
                $item->produto_id = (int) $produtoId;
                $item->quantidade = $quantidade;
                $item->preco_unitario = $produto->preco;
                $item->save(false);

                $valorTotal += (float) $produto->preco * $quantidade;
            }

            $pedido->valor_total = $valorTotal;
            $pedido->save(false);

            AuditoriaService::registrar(
                $pedido->usuario_id,
                'CRIACAO_PEDIDO',
                'Pedido',
                $pedido->id,
                [
                    'canal_pedido' => $pedido->canal_pedido,
                    'unidade_id' => $pedido->unidade_id,
                    'valor_total' => $pedido->valor_total,
                ]
            );

            $transaction->commit();
            return $pedido;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}
