<?php

use yii\db\Migration;

/**
 * Class m181127_063612_set_service_id_in_license
 */
class m181127_063612_set_service_id_in_license extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $array = [
            1 => \api_web\components\Registry::RK_SERVICE_ID,
            2 => \api_web\components\Registry::IIKO_SERVICE_ID,
            3 => \api_web\components\Registry::VENDOR_DOC_MAIL_SERVICE_ID,
            4 => \api_web\components\Registry::ONE_S_CLIENT_SERVICE_ID,
            5 => \api_web\components\Registry::TILLYPAD_SERVICE_ID,
            7 => \api_web\components\Registry::MERC_SERVICE_ID,
            8 => \api_web\components\Registry::EGAIS_SERVICE_ID,
            9 => \api_web\components\Registry::EDI_SERVICE_ID,
            10 => \api_web\components\Registry::ONE_S_VENDOR_SERVICE_ID,
            11 => \api_web\components\Registry::MC_LITE_LICENSE_ID,
            12 => \api_web\components\Registry::MC_BUSINESS_LICENSE_ID,
            13 => \api_web\components\Registry::MC_ENTERPRICE_LICENSE_ID,
        ];
        $table = \common\models\licenses\License::tableName();
        foreach($array as $id => $service_id) {
            $this->update($table, ['service_id' => $service_id], ['id' => $id]);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181127_063612_set_service_id_in_license cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181127_063612_set_service_id_in_license cannot be reverted.\n";

        return false;
    }
    */
}
