<?php

use yii\db\Migration;


class m180615_103356_recreate_table_rk_dicstatus extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    public function safeUp()
    {
        $this->dropTable('{{%rk_dicstatus}}');
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%rk_dicstatus}}', [
            'id' => $this->primaryKey(),
            'denom' => $this->string()->null()->defaultValue(null),
            'comment' => $this->string()->null()->defaultValue(null)
        ], $tableOptions);
        $this->execute('alter table `rk_dicstatus` comment "Таблица сведений о названиях статусов запроса на закачку справочников в системе R-keeper";');
        $this->addCommentOnColumn('{{%rk_dicstatus}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_dicstatus}}', 'denom', 'Название статуса запроса на закачку справочников в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_dicstatus}}', 'comment', 'Комментарий (не используется)');
        $this->insert('{{%rk_dicstatus}}', [
            'denom' => 'Данных нет',
            'comment' => null
        ]);
        $this->insert('{{%rk_dicstatus}}', [
            'denom' => 'Запрос отправлен',
            'comment' => null
        ]);
        $this->insert('{{%rk_dicstatus}}', [
            'denom' => 'Ошибка запроса данных',
            'comment' => null
        ]);
        $this->insert('{{%rk_dicstatus}}', [
            'denom' => 'Ошибка получения данных',
            'comment' => null
        ]);
        $this->insert('{{%rk_dicstatus}}', [
            'denom' => 'Ответ не получен в течение 5 минут',
            'comment' => null
        ]);
        $this->insert('{{%rk_dicstatus}}', [
            'denom' => 'Данные загружены',
            'comment' => null
        ]);

    }

    public function safeDown()
    {
        echo "m180615_103356_recreate_table_rk_dicstatus cannot be reverted.\n";

        return false;
    }

}
