<?php

/**
 * Class Migration
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-10-08
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

use yii\db\Migration;

class m181008_101714_add_field_parent_in_outer_category extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addColumn('{{%outer_category}}', 'parent_outer_uid',
            $this->string(45)->null()->after('outer_uid')->comment('Родительский outer_id'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%outer_category}}', 'parent_outer_uid');
    }

}
