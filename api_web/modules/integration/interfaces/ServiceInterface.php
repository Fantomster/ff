<?php

namespace api_web\modules\integration\interfaces;

interface ServiceInterface
{
    public function getLicenseMixCart();

    public function getSettings();

    public function getOptions();
}