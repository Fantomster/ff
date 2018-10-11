<?php

use yii\db\Migration;
use common\models\AllService;
use \common\models\licenses\License;
use \common\models\licenses\LicenseService;
use \common\models\licenses\LicenseOrganization;


/**
 * Class m181004_065039_insert_info_in_licenses_tables
 */
class m181004_065039_insert_info_in_licenses_tables extends Migration
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
        $this->insertData(1, '\api\common\models\RkService');
        $this->insertData(2, '\api\common\models\iiko\iikoService');
        $this->insertData(4, '\api\common\models\merc\mercService');
        $this->insertData(8, '\api\common\models\one_s\OneSService');
    }


    private function insertData(int $serviseID, String $serviceClass): void
    {
        $oldService = $serviceClass::find()->all();
        if ($oldService && is_iterable($oldService)) {
            $service = AllService::findOne(['id' => $serviseID]);
            foreach ($oldService as $item) {
                $license = new License();
                $license->name = $service->denom;
                $license->is_active = $item->status_id ?? 0;
                $license->login_allowed = 1;
                $license->save();
                $licenseService = new LicenseService();
                $licenseService->license_id = $license->id;
                $licenseService->service_id = $serviseID;
                $licenseService->save();
                $licenseOrganization = new LicenseOrganization();
                $licenseOrganization->license_id = $license->id;
                $licenseOrganization->org_id = $item->org ?? null;
                $licenseOrganization->fd = $item->fd ?? null;
                $licenseOrganization->td = $item->td ?? null;
                $licenseOrganization->object_id = $item->code ?? null;
                $licenseOrganization->outer_user = $item->name ?? null;
                $licenseOrganization->outer_name = $item->name ?? null;
                $licenseOrganization->outer_address = $item->address ?? null;
                $licenseOrganization->outer_phone = $item->phone ?? null;
                $licenseOrganization->status_id = $item->status_id ?? null;
                $licenseOrganization->is_deleted = $item->is_deleted ?? null;

                if (isset($item->last_active)) {
                    $lastActive = $item->last_active;
                    if ($lastActive[0] == '2') {
                        $licenseOrganization->outer_last_active = $lastActive;
                    } else {
                        $licenseOrganization->outer_last_active = null;
                    }
                }
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
