<?php

/**
 * Class Migration
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @updateddAt 2018-10-08
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

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

        $dbName = DBNameHelper::getMainName();
        $this->addForeignKey('{{%organization_dictionary_org}}', '{{%organization_dictionary}}', 'org_id', $dbName.'.organization', 'id');

    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%organization_dictionary_org}}', '{{%organization_dictionary}}');
    }

}
