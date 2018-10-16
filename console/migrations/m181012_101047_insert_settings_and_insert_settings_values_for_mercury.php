<?php

use yii\db\Migration;

/**
 * Class m181012_101047_insert_settings_and_insert_settings_values_for_mercury
 */
class m181012_101047_insert_settings_and_insert_settings_values_for_mercury extends Migration
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

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $db = \Yii::$app->db_api;
        $settingsData = [
            'merc' => $db->createCommand('SELECT * FROM  merc_dicconst')->queryAll()
        ];
        $isets = [];
        foreach ($settingsData as $serviceName => $params) {
            foreach ($params as $param) {
                $isets[] = [
                    '_ITEM_VALUES_'  => [
                        'name'          => $serviceName . '_' . $param['denom'],
                        'default_value' => $param['def_value'],
                        'comment'       => $param['comment'],
                        'type'          => self::TYPE_LIST[$param['type']],
                        'is_active'     => $param['is_active'],
                        'item_list'     => null,
                    ],
                    '_SERVICE_NAME_' => $serviceName,
                    '_OLD_ID_'       => $param['id'],
                ];
                $existsPconstIds[$param['id']] = $param['id'];
            }
        }
        $mapping = [];
        foreach ($isets as $iset) {
            $fields = [];
            $values = [];
            foreach ($iset['_ITEM_VALUES_'] as $fld => $val) {
                $fields[] = "`" . $fld . "`";
                $values[] = "'" . $val . "'";
            }
            $fields = implode(', ', $fields);
            $values = implode(', ', $values);

            $sql = 'INSERT INTO `integration_setting` (' . $fields . ') VALUES (' . $values . ')';
            $db->createCommand($sql)->execute();
            $lid = $db->getLastInsertID();
            $mapping[$iset['_SERVICE_NAME_']][$iset['_OLD_ID_']] = $lid;
        }
        $dicp = [
            'merc' => $db->createCommand('SELECT * FROM  merc_pconst')->queryAll(),
        ];
        $isetv = [];

        foreach ($dicp as $k => $v) {
            foreach ($v as $vv) {
                if (array_key_exists($vv['const_id'], $existsPconstIds) && $vv['org'] > 0) {
                    $isetv[] = [
                        'org_id'     => $vv['org'],
                        'setting_id' => $mapping[$k][$vv['const_id']],
                        'created_at' => \gmdate("Y-m-d H:i:s"),
                        'updated_at' => \gmdate("Y-m-d H:i:s"),
                        'value'      => $vv['value'],
                    ];
                }
            }
        }

        foreach ($isetv as $is) {
            $fields = [];
            $values = [];
            foreach ($is as $fld => $val) {
                $fields[] = "`" . $fld . "`";
                $values[] = "'" . $val . "'";
            }
            $fields = implode(', ', $fields);
            $values = implode(', ', $values);
            $sql = 'INSERT INTO `integration_setting_value` (' . $fields . ') VALUES (' . $values . ')';
            $db->createCommand($sql)->execute();
        }


    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181012_101047_insert_settings_and_insert_settings_values_for_mercury cannot be reverted.\n";

        return false;
    }

}
