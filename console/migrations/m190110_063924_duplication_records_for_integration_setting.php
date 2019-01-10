<?php

use common\models\IntegrationSetting;
use yii\db\Migration;

class m190110_063924_duplication_records_for_integration_setting extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $iSettings = IntegrationSetting::find()
            ->where([
                'service_id' => 2,
                'name'       => [
                    'URL',
                    'auth_login',
                    'auth_password',
                    'taxVat',
                    'auto_unload_invoice',
                    'main_org'
                ]
            ])
            ->all();

        foreach ($iSettings as $iSetting) {
            (new IntegrationSetting([
                'name'                => $iSetting->name,
                'default_value'       => $iSetting->default_value,
                'comment'             => $iSetting->comment,
                'type'                => $iSetting->type,
                'is_active'           => $iSetting->is_active,
                'item_list'           => $iSetting->item_list,
                'service_id'          => 10,
                'required_moderation' => $iSetting->required_moderation
            ]))->save();
        }
    }

    public function safeDown()
    {
        $this->delete('{{%integration_setting}}', [
            'service_id' => 10,
            'name'       => [
                'URL',
                'auth_login',
                'auth_password',
                'taxVat',
                'auto_unload_invoice',
                'main_org'
            ]
        ]);
    }
}