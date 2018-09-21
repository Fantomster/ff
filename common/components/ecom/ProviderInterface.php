<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:21 PM
 */

namespace common\components\ecom;


interface ProviderInterface
{
    public function getResponse($login, $pass);
    public function insertFilesInQueue(array $list);
}