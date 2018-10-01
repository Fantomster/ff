<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:24 PM
 */

namespace common\components\ecom;

/**
 * Interface RealizationInterface
 *
 * @package common\components\ecom
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
    public function getDoc($client, String $fileName, String $login, String $pass, int $ediFilesQueueID);

    /**
     * @return array
     */
    public function getFileList();
}