<?php

namespace api_web\modules\integration\interfaces;

interface AgentInterface
{
    /**
     * Выгиузка данных в модель
     * @return mixed
     */
    public static function loadModel($key);

    /**
     * Инфонрмация о контрагенте из учетной системы
     * @return array
     */
    public function getAgentInfo();

    /**
     * Инфонрмация о контрагенте из сопоставления с mixcart
     * @return array
     */
    public function getVendorInfo();
}