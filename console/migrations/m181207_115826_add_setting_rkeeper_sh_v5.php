<?php

use yii\db\Migration;

/**
 * Class m181207_115826_add_setting_rkeeper_sh_v5
 */
class m181207_115826_add_setting_rkeeper_sh_v5 extends Migration
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
        $this->insert('rk_dicconst', [
            'denom'     => 'sh_version',
            'def_value' => 4,
            'comment'   => 'Версия Store House',
            'type'      => 1,
            'is_active' => 1
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $setting_id = \Yii::$app->db_api->createCommand("SELECT id FROM rk_dicconst WHERE denom = 'sh_version'")->queryScalar();
        if ($setting_id) {
            $this->delete('rk_dicconst', ['id' => $setting_id]);
            $this->delete('rk_pconst', ['const_id' => $setting_id]);
        }
    }
}
