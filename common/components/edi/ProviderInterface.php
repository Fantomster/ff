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
    public function handleFilesList($orgId);

    public function sendOrderInfo($order, $orgId, $done);

    public function getFilesList($organizationId);

    public function getFile($item, $orgId);
}