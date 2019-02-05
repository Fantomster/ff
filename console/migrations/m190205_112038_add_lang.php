<?php

use yii\db\Migration;

class m190205_112038_add_lang extends Migration
{
    public $translations = [
        'api.one.s.controllers.waybill.data.not.save' => 'Сохранить позицию в приходной накладной 1С не удалось.',
        'api.one.s.controllers.waybill.not.save'      => 'Сохранить приходную накладную 1С не удалось.',
        'api.one.s.waybill.not.find'                  => 'Приходной накладной 1С с таким номером не существует.',
        'api.one.s.waybill.not.send'                  => 'Приходную накладную 1С не удалось выгрузить.',
        'api.one.s.controllers.not.auth'              => 'Не удалось авторизоваться на сервере 1С.',
        'api.one.s.waybill.not.ready'                 => 'Приходная накладная 1С к выгрузке не готова.',
        'api.one.s.waybill.data.not.find'             => 'Товара с таким номером в приходной накладной 1С не существует.',
        'api.rkws.controllers.waybill.data.not.save'  => 'Сохранить позицию в приходной накладной R-Keeper не удалось.',
        'api.rkws.controllers.waybill.not.save'       => 'Сохранить приходную накладную R-Keeper не удалось.',
        'api.rkws.waybill.not.find'                   => 'Приходной накладной R-Keeper с таким номером не существует.',
        'api.rkws.waybill.not.send'                   => 'Приходную накладную R-Keeper не удалось выгрузить.',
        'api.rkws.controllers.not.auth'               => 'Не удалось авторизоваться на сервере R-Keeper.',
        'api.rkws.waybill.not.ready'                  => 'Приходная накладная R-Keeper к выгрузке не готова.',
        'api.rkws.waybill.data.not.find'              => 'Товара с таким номером в приходной накладной R-Keeper не существует.',
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
