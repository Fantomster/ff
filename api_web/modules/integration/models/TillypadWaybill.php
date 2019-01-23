<?php

namespace api_web\modules\integration\models;

use api_web\components\Registry;

class TillypadWaybill extends iikoWaybill
{
    public $service = Registry::TILLYPAD_SERVICE_ID;
}