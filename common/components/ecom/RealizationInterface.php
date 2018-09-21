<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:24 PM
 */

namespace common\components\ecom;

interface RealizationInterface
{
    public function getDoc($client, String $fileName, String $login, String $pass, int $ediFilesQueueID);
}