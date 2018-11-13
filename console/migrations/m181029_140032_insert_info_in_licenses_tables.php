<?php

use yii\db\Migration;
use common\models\AllService;
use \common\models\licenses\License;
use \common\models\licenses\LicenseService;
use \common\models\licenses\LicenseOrganization;

/**
 * Class m181029_140032_insert_info_in_licenses_tables
 */
class m181029_140032_insert_info_in_licenses_tables extends Migration
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
        $this->dropForeignKey('license_id', '{{%license_service}}');
        $this->dropForeignKey('service_id', '{{%license_service}}');
        $this->dropForeignKey('license_id_organization', '{{%license_organization}}');
        $this->truncateTable('{{%license}}');
        $this->truncateTable('{{%license_organization}}');
        $this->addColumn('{{%license}}', 'service_id', $this->integer()->notNull());
        $this->addCommentOnColumn('{{%license}}', 'service_id', 'Указатель на ID сервиса');
        $this->addForeignKey('license_service_id', '{{%license}}', 'service_id', '{{%all_service}}', 'id');
        $this->addForeignKey('license_id_organization', '{{%license_organization}}', 'license_id', '{{%license}}', 'id');
        $services = \common\models\AllService::find()->all();
        foreach ($services as $item) {
            $license = new License();
            $license->name = $item->denom;
            $license->is_active = $item->is_active;
            $license->login_allowed = 1;
            $license->service_id = $item->id;
            $license->save();
        }
        $this->insertData(1, '\api\common\models\RkService');
        $this->insertData(2, '\api\common\models\iiko\iikoService');
        $this->insertData(4, '\api\common\models\merc\mercService');
        $this->insertData(8, '\api\common\models\one_s\OneSService');
    }


    private function insertData(int $serviseID, String $serviceClass): void
    {
        $oldService = $serviceClass::find()->all();
        if ($oldService && is_iterable($oldService)) {
            foreach ($oldService as $item) {
                $license = License::findOne(['service_id' => $serviseID]);
                $licenseOrganization = new LicenseOrganization();
                $licenseOrganization->license_id = $license->id;
                $licenseOrganization->org_id = $item->org ?? null;
                $licenseOrganization->fd = $item->fd ?? null;
                $licenseOrganization->td = $item->td ?? null;
                $licenseOrganization->object_id = $item->object_id ?? null;
                $licenseOrganization->status_id = $item->status_id ?? null;
                $licenseOrganization->is_deleted = $item->is_deleted ?? null;
                $licenseOrganization->save();
            }
        }
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
