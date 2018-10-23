<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 17/10/2018
 * Time: 13:39
 */

namespace api_web\components;

/**
 * Class Registry
 * Constants and static fields in ONE place for all project
 *
 * @package api_web\components
 */
class Registry
{

    /** - Services section - */
    const RK_SERVICE_ID = 1;
    const IIKO_SERVICE_ID = 2;
    /**@var int vendor waybills from emails */
    const VENDOR_DOC_MAIL_SERVICE_ID = 3;
    /**@var int const for mercuriy service id in all_service table */
    const MERC_SERVICE_ID = 4;
    /**@var int const for EDI service id in all_service table */
    const EDI_SERVICE_ID = 6;
    /**@var int const for 1C (rest) service id in all_service table */
    const ONE_S_CLIENT_SERVICE_ID = 8;

    /** - Waybill section - */
    const WAYBILL_COMPARED = 1;
    const WAYBILL_FORMED = 2;
    const WAYBILL_ERROR = 3;
    const WAYBILL_RESET = 4;
    const WAYBILL_UNLOADED = 5;
    const WAYBILL_UNLOADING = 6;

    /**@var array $statuses */
    static $waybill_statuses = [
        self::WAYBILL_COMPARED  => 'compared',
        self::WAYBILL_FORMED    => 'formed',
        self::WAYBILL_ERROR     => 'error',
        self::WAYBILL_RESET     => 'reset',
        self::WAYBILL_UNLOADED  => 'unloaded',
        self::WAYBILL_UNLOADING => 'unloading',
    ];

    /**@var array интеграционные сервисы */
    static $integration_services = [
        self::RK_SERVICE_ID,
        self::IIKO_SERVICE_ID,
        self::MERC_SERVICE_ID,
        self::VENDOR_DOC_MAIL_SERVICE_ID,
        self::ONE_S_CLIENT_SERVICE_ID
    ];
}