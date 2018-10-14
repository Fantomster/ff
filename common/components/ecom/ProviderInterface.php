<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/20/2018
 * Time: 12:21 PM
 */

namespace common\components\ecom;


/**
 * Interface ProviderInterface
 *
 * @package common\components\ecom
 */
interface ProviderInterface
{
    /**
     * @param $login
     * @param $pass
     * @return mixed
     */
    public function getResponse($login, $pass);


}