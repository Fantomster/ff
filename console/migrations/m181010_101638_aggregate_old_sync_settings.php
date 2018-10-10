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

class m181010_101638_aggregate_old_sync_settings extends Migration
{


    const TYPE_LIST = [
        1 => 'dropdown_list',
        2 => 'input_text',
        3 => 'password',
        4 => 'dropdown_list',
        5 => 'radio',
        6 => 'input_text',
        7 => 'checkbox',
    ];

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {

        /** @noinspection PhpUndefinedFieldInspection */
        \Yii::$app->db_api->createCommand('UPDATE integration_setting SET `is_active` = 0')->execute();
        /** @noinspection PhpUndefinedFieldInspection */
        $db = \Yii::$app->db_api;
        $settingsData = [
            'rkws' => $db->createCommand('SELECT * FROM  rk_dicconst')->queryAll(),
            'iiko' => $db->createCommand('SELECT * FROM  iiko_dicconst')->queryAll(),
            'ones' => $db->createCommand('SELECT * FROM  one_s_dicconst')->queryAll(),
        ];
        $isets = [];
        foreach ($settingsData as $serviceName => $params) {
            foreach ($params as $param) {
                $isets[] = [
                    '_ITEM_VALUES_' => [
                        'name' => $serviceName . '_' . $param['denom'],
                        'default_value' => $param['def_value'],
                        'comment' => $param['comment'],
                        'type' => self::TYPE_LIST[$param['type']],
                        'is_active' => $param['is_active'],
                        'item_list' => null,
                    ],
                    '_SERVICE_NAME_' => $serviceName,
                    '_OLD_ID_' => $param['id'],
                ];
            }
        }
        $mapping = [];
        foreach ($isets as $iset) {
            $fields = [];
            $values = [];
            foreach ($iset['_ITEM_VALUES_'] as $fld => $val) {
                $fields[] = "`".$fld."`";
                $values[] = "'".$val."'";
            }
            $fields = implode(', ', $fields);
            $values = implode(', ', $values);

            $sql = 'INSERT INTO `integration_setting` ('.$fields.') VALUES ('.$values.')';
            $db->createCommand($sql)->execute();
            $lid = $db->getLastInsertID();
            $mapping[$iset['_SERVICE_NAME_']][$iset['_OLD_ID_']] = $lid;
        }
        $dicp = [
            'rkws' => $db->createCommand('SELECT * FROM  rk_pconst')->queryAll(),
            'iiko' => $db->createCommand('SELECT * FROM  iiko_pconst')->queryAll(),
            'ones' => $db->createCommand('SELECT * FROM  one_s_pconst')->queryAll(),
        ];
        $isetv = [];
        foreach ($dicp as $k => $v) {
            foreach ($v as $vv) {
                $isetv[] = [
                    'org_id' => $vv['org'],
                    'setting_id' => $mapping[$k][$vv['const_id']],
                    'created_at' => \gmdate("Y-m-d H:i:s"),
                    'updated_at' => \gmdate("Y-m-d H:i:s"),
                    'value' => $vv['value'],
                ];
            }
        }
        foreach ($isetv as $is) {
            $fields = [];
            $values = [];
            foreach ($is as $fld => $val) {
                $fields[] = "`".$fld."`";
                $values[] = "'".$val."'";
            }
            $fields = implode(', ', $fields);
            $values = implode(', ', $values);
            $sql = 'INSERT INTO `integration_setting_value` ('.$fields.') VALUES ('.$values.')';
            $db->createCommand($sql)->execute();
        }

    }

    public function safeDown()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        \Yii::$app->db_api->createCommand('UPDATE integration_setting SET `is_active` = 1')->execute();
    }

}
