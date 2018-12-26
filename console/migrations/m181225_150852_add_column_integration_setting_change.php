<?php

use yii\db\Migration;

class m181225_150852_add_column_integration_setting_change extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addColumn('{{%integration_setting_change}}', 'rejected_user_id', $this->integer(11)
            ->null()
            ->comment('Указатель на ID пользователя который отменил запрос о изменении'));
        $this->addColumn('{{%integration_setting_change}}', 'rejected_at', $this->timestamp()
            ->null()
            ->comment('Дата отмены изменения'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%integration_setting_change}}', 'rejected_user_id');
        $this->dropColumn('{{%integration_setting_change}}', 'rejected_at');
    }
}
