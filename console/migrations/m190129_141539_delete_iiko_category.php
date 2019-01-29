<?php

use yii\db\Migration;

/**
 * Class m190129_141539_delete_iiko_category
 */
class m190129_141539_delete_iiko_category extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * @return bool|void
     * @throws Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function safeUp()
    {
        $modelCategory = \common\models\OuterDictionary::findOne([
            'service_id' => \api_web\components\Registry::IIKO_SERVICE_ID,
            'name'       => 'category'
        ]);

        if ($modelCategory) {
            if (\common\models\OrganizationDictionary::deleteAll(['outer_dic_id' => $modelCategory->id])) {
                $modelCategory->delete();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190129_141539_delete_iiko_category cannot be reverted.\n";
        return false;
    }
}
