<?php
/**
 * Created by PhpStorm.
 * Developer: Arsen
 * Vk: https://vk.com/a.arsik
 * Inst: https://www.instagram.com/arsen.web/
 * Date: 2019-01-11
 * Time: 14:51
 */

namespace api_web\modules\integration\classes\sync;

class TillypadProduct extends ServiceTillypad
{
    public $index = self::DICTIONARY_PRODUCT;
    public $queueName = 'TillypadProductSync';
}