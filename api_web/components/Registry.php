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
    const WAYBILL_COMPARED = 'compared';
    const WAYBILL_FORMED = 'formed';
    const WAYBILL_ERROR = 'error';
    const WAYBILL_RESET = 'reset';
    const WAYBILL_UNLOADED = 'unloaded';
    const WAYBILL_UNLOADING = 'unloading';

    /**@var array $statuses */
    static $waybill_statuses = [
        1 => self::WAYBILL_COMPARED,
        2 => self::WAYBILL_FORMED,
        3 => self::WAYBILL_ERROR,
        4 => self::WAYBILL_RESET,
        5 => self::WAYBILL_UNLOADED,
        6 => self::WAYBILL_UNLOADING,
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