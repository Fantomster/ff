<?php

use yii\db\Migration;
use \common\models\licenses\License;
use \common\models\licenses\LicenseOrganization;

/**
 * Class m181030_120815_insert_data_in_license_tables_three
 */
class m181030_120815_insert_data_in_license_tables_three extends Migration
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
        $this->dropForeignKey('license_service_id', '{{%license}}');
        $this->dropForeignKey('license_id_organization', '{{%license_organization}}');
        $this->truncateTable('{{%license}}');
        $this->truncateTable('{{%license_organization}}');
        $this->alterColumn('{{%license}}', 'service_id', $this->integer()->null());
        $this->addForeignKey('license_id_organization', '{{%license_organization}}', 'license_id', '{{%license}}', 'id');

        $this->batchInsert('{{%license}}', [
            'name',
            'is_active',
            'login_allowed',
            'service_id',
        ], [
            [
                'R-keeper',
                1,
                1,
                1,
            ],
            [
                'iiko',
                1,
                1,
                2,
            ],
            [
                'Поставки (Почтовый робот)',
                1,
                1,
                null,
            ],
            [
                '1C Интеграция закупщика',
                1,
                1,
                8,
            ],
            [
                'Tillypad',
                1,
                1,
                10,
            ],
            [
                'Poster',
                1,
                1,
                null,
            ],
            [
                'ВЕТИС Меркурий',
                1,
                1,
                4,
            ],
            [
                'ЕГАИС Погашение',
                1,
                1,
                5,
            ],
            [
                'Документы ЭДО',
                1,
                1,
                null,
            ],
            [
                '1C Интеграция поставщика',
                1,
                1,
                7,
            ],
            [
                'MC Light',
                1,
                1,
                null,
            ],
            [
                'MC Businesses',
                1,
                1,
                null,
            ],
            [
                'MC Enterprise',
                1,
                1,
                null,
            ],

        ]);

        $this->insertData(1, '\api\common\models\RkServicedata');
        $this->insertData(2, '\api\common\models\iiko\iikoService');
        $this->insertData(4, '\api\common\models\merc\mercService');
        $this->insertData(8, '\api\common\models\one_s\OneSService');
        $this->insertData(10, '\api\common\models\tillypad\TillypadService');
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
