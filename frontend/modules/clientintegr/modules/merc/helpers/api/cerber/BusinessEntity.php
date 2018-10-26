<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 27.06.2018
 * Time: 11:50
 */

namespace frontend\modules\clientintegr\modules\merc\helpers\api\cerber;

class BusinessEntity
{
    var $guid;//UUID
    var $active;//boolean
    var $last;//boolean
    var $status;//VersionStatus
    var $createDate;//dateTime
    var $updateDate;//dateTime
    var $previous;//UUID
    var $next;//UUID
}