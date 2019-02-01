<?php

use yii\db\Migration;

class m190201_104226_add_comments_fields_assorti16 extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `tillypad_log` comment "Таблица сведений о логах в системе Tillypad";');
        $this->addCommentOnColumn('{{%one_s_contragent}}', 'is_active', 'Показатель состояния активности в системе 1С (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%one_s_good}}', 'is_active', 'Показатель состояния активности в системе 1С (0 - не активен, 1 - активен)');
        $this->addCommentOnColumn('{{%one_s_store}}', 'is_active', 'Показатель состояния активности в системе 1С (0 - не активен, 1 - активен)');
    }

    public function safeDown()
    {
        $this->execute('alter table `tillypad_log` comment "";');
        $this->dropCommentFromColumn('{{%one_s_contragent}}', 'is_active');
        $this->dropCommentFromColumn('{{%one_s_good}}', 'is_active');
        $this->dropCommentFromColumn('{{%one_s_store}}', 'is_active');
    }
}
