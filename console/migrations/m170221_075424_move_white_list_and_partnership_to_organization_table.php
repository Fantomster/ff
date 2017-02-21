<?php

use yii\db\Migration;

class m170221_075424_move_white_list_and_partnership_to_organization_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%organization}}', 'white_list', $this->boolean()->notNull()->defaultValue(0));
        $this->addColumn('{{%organization}}', 'partnership', $this->boolean()->notNull()->defaultValue(0));
        $subQueryWL = \common\models\WhiteList::find()->select('organization_id');
        $subQueryPartnership = \common\models\WhiteList::find()->select('organization_id')->where(['partnership' => 1]);
        \common\models\Organization::updateAll(['white_list' => 1], ['in', 'id', $subQueryWL]);
        \common\models\Organization::updateAll(['partnership' => 1], ['in', 'id', $subQueryPartnership]);
    }

    public function safeDown()
    {
        $this->dropColumn('{{%organization}}', 'white_list');
        $this->dropColumn('{{%organization}}', 'partnership');
    }
}
