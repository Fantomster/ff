<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%promo_action}}`.
 */
class m190214_094513_create_promo_action_table extends Migration
{
    public $table = '{{%promo_action}}';
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable($this->table, [
            'id' => $this->primaryKey()->comment("ID промо-акции"),
            'name' => $this->string(40)->notNull()->comment("Название промо-акции"),
            'code' => $this->string(20)->defaultValue(null)->comment("Код промо-акции"),
            'title' => $this->string(100)->notNull()->comment("Заголовок в сообщении"),
            'message' => $this->string(1000)->notNull()->comment("Содержание сообщения"),
            'created_at' => $this->timestamp()->null()->defaultValue(null)->comment("Дата создания акции"),
            'updated_at' => $this->timestamp()->null()->defaultValue(null)->comment("Дата изменения акции")

        ]);

        $this->createIndex('idx_name', $this->table, 'name');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
