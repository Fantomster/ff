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
use common\helpers\DBNameHelper;

class m181008_122309_add_fields_in_outer_unit extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->addColumn('{{%outer_unit}}', 'parent_outer_uid',
            $this->string(45)->null()->after('outer_uid')->comment('Родительский outer_id'));
        $this->addColumn('{{%outer_unit}}', 'ratio',
            $this->float()->null()->after('parent_outer_uid')->comment('Коэффициент'));
        $this->addColumn('{{%outer_unit}}', 'org_id',
            $this->integer()->notNull()->after('ratio')->comment('ID Организации'));
        $dbName = DBNameHelper::getMainName();
        $this->addForeignKey('{{%outer_unit_org}}', '{{%outer_unit}}', 'org_id', $dbName.'.organization', 'id');
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%outer_unit_org}}', '{{%outer_unit}}');
        $this->dropColumn('{{%outer_unit}}', 'org_id');
        $this->dropColumn('{{%outer_unit}}', 'ratio');
        $this->dropColumn('{{%outer_unit}}', 'parent_outer_uid');
    }

}
