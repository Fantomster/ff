<?php

namespace api_web\modules\integration\modules\vetis\models;

class VetisWaybill
{
    /**
     * Список сертифитаков
     * @param $request
     * @return array
     */
    public function getList($request)
    {
        return ['result' => $request];
    }
}