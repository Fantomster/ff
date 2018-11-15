<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:21 PM
 */

namespace common\components\edi;


/**
 * Interface ProviderInterface
 *
 * @package common\components\edi
 */
interface ProviderInterface
{
    public function handleFilesList();

    public function sendOrderInfo($order, $done);

    public function getFilesList($orgID);

    public function getFile($item);
}