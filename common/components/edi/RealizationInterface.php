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
    /**
     * @param        $client
     * @param String $fileName
     * @param String $login
     * @param String $pass
     * @param int    $ediFilesQueueID
     * @return mixed
     */


    public function parseFile($content);

    public function getSendingOrderContent($order, $done, $dateArray, $orderContent);
}