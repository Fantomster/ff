<?php

use yii\db\Migration;

class m180615_074353_recreate_table_rk_dictype extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->dropTable('{{%rk_dictype}}');
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%rk_dictype}}', [
            'id' => $this->primaryKey(),
            'denom' => $this->string()->null()->defaultValue(null),
            'created_at' => $this->timestamp()->null()->defaultValue(null),
            'comment' => $this->string()->null()->defaultValue(null),
            'contr' => $this->string(128)->null()->defaultValue(null),
        ], $tableOptions);
        $this->execute('alter table `rk_dictype` comment "Таблица сведений об услугах Mixcart интеграции c UCS в системе R-keeper";');
        $this->addCommentOnColumn('{{%rk_dictype}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%rk_dictype}}', 'denom', 'Наименование справочника в системе R-keeper');
        $this->addCommentOnColumn('{{%rk_dictype}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%rk_dictype}}', 'comment', 'Комментарий (не используется)');
        $this->addCommentOnColumn('{{%rk_dictype}}', 'contr', 'Название контроллера, вызываемого при загрузке справочника');
        $this->insert('{{%rk_dictype}}', [
            'denom' => 'Контрагенты',
            'created_at' => '2017-07-18 21:00:00',
            'comment' => null,
            'contr' => 'agent'
        ]);
        $this->insert('{{%rk_dictype}}', [
            'denom' => 'Склады',
            'created_at' => '2017-07-18 21:00:00',
            'comment' => null,
            'contr' => 'store'
        ]);
        $this->insert('{{%rk_dictype}}', [
            'denom' => 'Номенклатура',
            'created_at' => '2017-07-18 21:00:00',
            'comment' => null,
            'contr' => 'product'
        ]);
        $this->insert('{{%rk_dictype}}', [
            'denom' => 'Ед. измерения',
            'created_at' => '2017-08-14 21:00:00',
            'comment' => null,
            'contr' => 'edism'
        ]);
        $this->insert('{{%rk_dictype}}', [
            'denom' => 'Товарные группы',
            'created_at' => '2017-12-12 15:00:00',
            'comment' => null,
            'contr' => 'productgroup'
        ]);

    }

    public function safeDown()
    {
        echo "m180615_074353_recreate_table_rk_dictype cannot be reverted.\n";

        return false;
    }

}
