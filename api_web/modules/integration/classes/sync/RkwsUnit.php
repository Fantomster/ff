<?php

/**
 * Class RkwsUnit
 * @package api_web\module\integration\sync
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-20
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */

namespace api_web\modules\integration\classes\sync;

class RkwsUnit extends ServiceRkws
{

    /** @var string $index Символьный идентификатор справочника */
    public $index = 'unit';

    /** @var string $OperDenom Поле Denom в таблице all_service_operation */
    public static $OperDenom = 'sh_get_munits';

}
