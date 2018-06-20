<?php

namespace api_web\modules\integration\interfaces;

interface ServiceInterface
{
    /**
     * Название сервиса
     * @return string
     */
    public function getServiceName();

    /**
     * Информация о лицензии MixCart
     * @return \api\common\models\iiko\iikoService|array|null|\yii\db\ActiveRecord
     */
    public function getLicenseMixCart();

    /**
     * @return bool
     */
    public function getLicenseMixCartActive();

    /**
     * Настройки
     * @return mixed
     */
    public function getSettings();
    public function setSettings($params);

    /**
     * Список опций, отображаемых на главной странице интеграции
     * @return mixed
     */
    public function getOptions();
}