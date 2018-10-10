<?php

/**
 * Class Migration
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-10-10
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

use yii\db\Migration;
use common\models\IntegrationSetting;
use api\common\models\RkDicconst;
use api\common\models\iiko\iikoDicconst;
use api\common\models\one_s\OneSDicconst;
use api\common\models\RkPconst;
use api\common\models\iiko\iikoPconst;
use api\common\models\one_s\OneSPconst;
use common\models\IntegrationSettingValue;

class m181010_101638_aggregate_old_sync_settings extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {

        IntegrationSetting::updateAll(['is_active' => 0]);

        $settingsData = [
            'rkws' => RkDicconst::find()->all(),
            'iiko' => iikoDicconst::find()->all(),
            'ones' => OneSDicconst::find()->all(),
        ];

        $isets = [];
        foreach ($settingsData as $serviceName => $params) {
            foreach ($params as $param) {
                $isets[] = [
                    '_ITEM_VALUES_' => [
                        'name' => $serviceName . '_' . $param->denom,
                        'default_value' => $param->def_value,
                        'comment' => $param->comment,
                        'type' => IntegrationSetting::TYPE_LIST[$param->type],
                        'is_active' => $param->is_active,
                        'item_list' => null,
                    ],
                    '_SERVICE_NAME_' => $serviceName,
                    '_OLD_ID_' => $param->id,
                ];
            }
        }

        $mapping = [];
        foreach ($isets as $iset) {
            $item = new IntegrationSetting($iset['_ITEM_VALUES_']);
            $item->save();
            $mapping[$iset['_SERVICE_NAME_']][$iset['_OLD_ID_']] = $item->id;
        }

        $dicp['rkws'] = RkPconst::find()->all();
        $dicp['iiko'] = iikoPconst::find()->all();
        $dicp['ones'] = OneSPconst::find()->all();

        $isetv = [];
        foreach ($dicp as $k => $v) {
            foreach ($v as $vv) {
                $isetv[] = [
                    'org_id' => $vv->org,
                    'setting_id' => $mapping[$k][$vv->const_id],
                    'created_at' => null,
                    'updated_at' => null,
                    'value' => $vv->value,
                ];
            }
        }

        foreach ($isetv as $v) {
            $item = new IntegrationSettingValue($v);
            $item->save();
        }
    }

    public function safeDown()
    {
        IntegrationSetting::updateAll(['is_active' => 0]);
    }

}
