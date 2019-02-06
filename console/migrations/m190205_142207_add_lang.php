<?php

use yii\db\Migration;

class m190205_142207_add_lang extends Migration
{
    public $translations = [
        'api.allmaps.position.not.save'   => 'Позиции с таким номером в глобальном сопоставлении не существует.',
        'api.iiko.child.product.not.save' => 'Не удалось сохранить продукт дочернего бизнеса.',
    ];

    public function safeUp()
    {
        \console\helpers\BatchTranslations::insertCategory('ru', 'error', $this->translations);
    }

    public function safeDown()
    {
        \console\helpers\BatchTranslations::deleteCategory('ru', 'error', $this->translations);
    }
}
