<?php

use yii\db\Migration;

/**
 * Migration para criação da tabela de auditoria e logs (Segurança / LGPD).
 */
class m261118_120000_create_auditoria_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%auditoria_log}}', [
            'id' => $this->primaryKey(),
            'usuario_id' => $this->integer()->null(),
            'acao' => $this->string(100)->notNull(),
            'entidade' => $this->string(100)->notNull(),
            'entidade_id' => $this->integer()->null(),
            'detalhes' => $this->text()->null(),
            'criado_em' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP')->notNull(),
        ]);

        $this->createIndex('idx-auditoria_log-usuario_id', '{{%auditoria_log}}', 'usuario_id');
        $this->createIndex('idx-auditoria_log-entidade', '{{%auditoria_log}}', ['entidade', 'entidade_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%auditoria_log}}');
    }
}
