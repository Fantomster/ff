<?php

use yii\db\Migration;

/**
 * Class m180911_061743_add_trans_for_vsd_expertize_statuses
 */
class m180911_061743_add_trans_for_vsd_expertize_statuses extends Migration
{
    public $translations_ru = [
        'the_result_is_unknown' => 'Результат неизвестен',
        'the_result_can_not_be_determined' => 'Результат невозможно определить (не нормируется)',
        'positive_result' => 'Положительный результат',
        'negative_result' => 'Отрицательный результат',
        'not_conducted' => 'Не проводилось',
        'VSE_subjected_the_raw_materials_from_which_the_products_were_manufactured' => 'ВСЭ подвергнуто сырьё, из которого произведена продукция',
        'the_products_are_fully' => 'Продукция подвергнута ВСЭ в полном объеме',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations_ru);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations_ru);
    }
}
