<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Entidade de Domínio: AuditoriaLog.
 * Registra ações sensíveis do sistema para rastreabilidade e conformidade LGPD.
 *
 * @property int $id
 * @property int|null $usuario_id
 * @property string $acao
 * @property string $entidade
 * @property int|null $entidade_id
 * @property string|null $detalhes
 * @property string $criado_em
 */
class AuditoriaLog extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%auditoria_log}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['acao', 'entidade'], 'required'],
            [['usuario_id', 'entidade_id'], 'integer'],
            [['detalhes'], 'string'],
            [['acao', 'entidade'], 'string', 'max' => 100],
            [['criado_em'], 'safe'],
        ];
    }
}

