<?php

namespace app\services;

use app\models\AuditoriaLog;

/**
 * Serviço de Aplicação responsável pelo registro de logs de auditoria (LGPD).
 */
class AuditoriaService
{
    /**
     * Registra uma ação sensível na tabela auditoria_log.
     *
     * @param int|null $usuarioId
     * @param string $acao
     * @param string $entidade
     * @param int|null $entidadeId
     * @param array|string|null $detalhes
     * @return bool
     */
    public static function registrar(?int $usuarioId, string $acao, string $entidade, ?int $entidadeId = null, $detalhes = null): bool
    {
        $registro = new AuditoriaLog();
        $registro->usuario_id = $usuarioId;
        $registro->acao = $acao;
        $registro->entidade = $entidade;
        $registro->entidade_id = $entidadeId;
        $registro->detalhes = is_array($detalhes) ? json_encode($detalhes, JSON_UNESCAPED_UNICODE) : $detalhes;

        return $registro->save(false);
    }
}

