<?php

use yii\db\Migration;

class m171030_181700_add_default_column_to_api_settings extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function up()
    {
        $this->addColumn('{{%rk_settings}}','defval', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('{{%rk_settings}}','defval');
        return false;
    }


}
