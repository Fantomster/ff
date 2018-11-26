<?php

use yii\db\Migration;

/**
 * Class m181121_080341_add_lang
 */
class m181121_080341_add_lang extends Migration
{
    public $translations = [
        'vendor.you_are_not_working_with_this_supplier' => 'Вы не работаете с этим поставщиком',
        'vendor.not_found_vendors' => 'Вам необходимо пригласить поставщиков к работе в MixCart',
        'vendor.not_allow_editing' => 'Поставщик работает в системе, редактировать его данные запрещенно.',
        'vendor.not_you_editing' => 'Вы можете редактировать только свои данные',
        'vendor.is_not_vendor' => 'Эта организация не является поставщиком',
    ];

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'api_web', $this->translations);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'api_web', $this->translations);
    }
}
