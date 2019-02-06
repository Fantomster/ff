<?php

use yii\db\Migration;

class m190204_151333_add_lang extends Migration
{
    public $translations = [
        'api.tillypad.controllers.waybill.data.not.save' => 'Сохранить позицию в приходной накладной Tillypad не удалось.',
        'api.tillypad.controllers.waybill.not.save'      => 'Сохранить приходную накладную Tillypad не удалось.',
        'api.tillypad.waybill.not.find'                  => 'Приходной накладной Tillypad с таким номером не существует.',
        'api.tillypad.waybill.not.send'                  => 'Приходную накладную Tillypad не удалось выгрузить.',
        'api.tillypad.controllers.not.auth'              => 'Не удалось авторизоваться на сервере Tillypad.',
        'api.tillypad.waybill.not.ready'                 => 'Приходная накладная Tillypad к выгрузке не готова.',
        'api.tillypad.waybill.data.not.find'             => 'Товара с таким номером в приходной накладной Tillypad не существует.',
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
