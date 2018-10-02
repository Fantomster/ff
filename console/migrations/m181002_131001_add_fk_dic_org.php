<?php

use yii\db\Migration;
use common\helpers\DBNameHelper;

class m181002_131001_add_fk_dic_org extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    public function safeUp()
    {

        $dbName = DBNameHelper::getDsnAttribute('dbname', \Yii::$app->db->dsn);

        $this->addForeignKey('{{%organization_dictionary_org}}', '{{%organization_dictionary}}', 'org_id', $dbName.'.organization', 'id');

    }

    public function safeDown()
    {

    }


}
