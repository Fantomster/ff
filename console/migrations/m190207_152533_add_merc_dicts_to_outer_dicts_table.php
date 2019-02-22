<?php

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use common\models\OuterDictionary;
use yii\db\Migration;

/**
 * Class m190207_152533_add_merc_dicts_to_outer_dicts_table
 */
class m190207_152533_add_merc_dicts_to_outer_dicts_table extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    /**
     * {@inheritdoc}
     * @throws ValidationException
     */
    public function safeUp()
    {
        $this->addColumn('{{%outer_dictionary}}', 'is_common', $this->smallInteger(1)->comment('Флаг - указывающий, что словарь общий для всех организаций')->defaultValue(0));
        $arDicts = [
            'businessEntity'    => 1,
            'russianEnterprise' => 1,
            'foreignEnterprise' => 1,
            'productItem'       => 0,
            'transport'         => 0,
        ];
        foreach ($arDicts as $dictName => $isCommon) {
            $model = new OuterDictionary([
                'name'       => $dictName,
                'service_id' => Registry::MERC_SERVICE_ID,
                'is_common'  => $isCommon,
            ]);
            if (!$model->save()) {
                throw new ValidationException($model->getFirstErrors());
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190207_152533_add_merc_dicts_to_outer_dicts_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190207_152533_add_merc_dicts_to_outer_dicts_table cannot be reverted.\n";

        return false;
    }
    */
}
