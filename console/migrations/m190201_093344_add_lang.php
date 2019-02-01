<?php

use yii\db\Migration;

class m190201_093344_add_lang extends Migration
{
    public $translations = [
        'api.iiko.controllers.waybill.data.not.save' => 'Сохранить позицию в приходной накладной IIKO не удалось.',
        'api.iiko.controllers.waybill.not.save'      => 'Сохранить приходную накладную IIKO не удалось.',
        'api.allmaps.position.not.save'              => 'Сохранить позицию в глобальном сопоставлении не удалось.',
        'api.iiko.waybill.not.find'                  => 'Приходной накладной IIKO с таким номером не существует.',
        'api.controllers.order.not.find'             => 'Заказа с таким номером не существует.',
        'api.controllers.method.not.ajax'            => 'Способ отправки должен быть только AJAX.',
        'api.iiko.waybill.not.send'                  => 'Приходную накладную IIKO не удалось выгрузить.',
        'api.iiko.controllers.not.auth'              => 'Не удалось авторизоваться на сервере IIKO.',
        'api.iiko.waybill.not.ready'                 => 'Приходная накладная IIKO к выгрузке не готова.',
        'api.iiko.waybill.data.not.find'             => 'Товара с таким номером в приходной накладной IIKO не существует.',
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
