<?php

use yii\db\Migration;

class m190205_150257_add_lang extends Migration
{
    public $translations = [
        'api.rkws.action.not.save'   => 'Запрос актуальных данных о лицензиях UCS сохранить не удалось.',
        'api.rkws.service.not.save' => 'Сведения о лицензии UCS сохранить не удалось.',
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
