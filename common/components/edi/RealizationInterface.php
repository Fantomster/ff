<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:24 PM
 */

namespace common\components\edi;

/**
 * Interface RealizationInterface
 *
 * @package common\components\edi
 */
interface RealizationInterface
{
    public function parseFile($content, $providerID);

    public function getSendingOrderContent($order, $done, $dateArray, $orderContent);
}