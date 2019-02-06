<?php

use yii\db\Migration;

class m190123_102829_add_column_vetis_country_uuid_table_organization extends Migration
{
    public function safeUp()
    {
        $this->addColumn(\common\models\Organization::tableName(), 'vetis_country_uuid', $this->string()->null()->defaultValue('72a84b51-5c5e-11e1-b9b7-001966f192f1')->comment('Уникальный идентификатор государства, в котором находится организация'));
    }

    public function safeDown()
    {
        $this->dropColumn(\common\models\Organization::tableName(), 'vetis_country_uuid');
    }
}
