<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 9/18/2018
 * Time: 12:08 PM
 */

namespace api_web\modules\integration\interfaces;

/**
 * Interface DictionaryInterface
 *
 * @package api_web\modules\integration\interfaces
 */
interface DictionaryInterface
{
    /**
     * @return mixed
     */
    public function getList();
}
