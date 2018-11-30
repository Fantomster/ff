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

    /** id лицензий MixCart из таблицы license*/
    const MC_LITE_LICENSE_ID = 11;
    const MC_BUSINESS_LICENSE_ID = 12;
    const MC_ENTERPRICE_LICENSE_ID = 13;

    /** - Services section - */
    const RK_SERVICE_ID = 1;
    const IIKO_SERVICE_ID = 2;
    /**@var int vendor waybills from emails */
    const VENDOR_DOC_MAIL_SERVICE_ID = 3;
    /**@var int const for mercuriy service id in all_service table */
    const MERC_SERVICE_ID = 4;
    /**@var int const for EGAIS service id in all_service table */
    const EGAIS_SERVICE_ID = 5;
    /**@var int const for EDI service id in all_service table */
    const EDI_SERVICE_ID = 6;
    /**@var int const for 1C (rest) service id in all_service table */
    const ONE_S_VENDOR_SERVICE_ID = 7;
    /**@var int const for 1C (rest) service id in all_service table */
    const ONE_S_CLIENT_SERVICE_ID = 8;
    /**@var int const for 1C (rest) service id in all_service table */
    const TILLYPAD_SERVICE_ID = 10;
    /**@var int const for MixCart service_id in all_service table */
    const MC_BACKEND = 9;

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
        self::ONE_S_CLIENT_SERVICE_ID,
        self::TILLYPAD_SERVICE_ID,
    ];

    /** @var array сервисы в которых генерируются накладные */
    static $waybill_services = [
        self::RK_SERVICE_ID,
        self::IIKO_SERVICE_ID,
        self::ONE_S_CLIENT_SERVICE_ID
    ];

    /**@var array сервисы MixCart */
    static $mc_licenses_id = [
        self::MC_LITE_LICENSE_ID,
        self::MC_BUSINESS_LICENSE_ID,
        self::MC_ENTERPRICE_LICENSE_ID
    ];

    /**
     * Список сервисов, которые разрешают войти в систему
     *
     * @var array
     */
    static $allow_enter_services = [
        self::MC_LITE_LICENSE_ID,
        self::MC_BUSINESS_LICENSE_ID,
        self::MC_ENTERPRICE_LICENSE_ID,
        self::MERC_SERVICE_ID
    ];

    const DOC_GROUP_STATUS_WAIT_SENDING = 1;
    const DOC_GROUP_STATUS_WAIT_FORMING = 2;
    const DOC_GROUP_STATUS_SENT = 3;

    static $doc_group_status = [
        self::DOC_GROUP_STATUS_WAIT_SENDING => 'sending',
        self::DOC_GROUP_STATUS_WAIT_FORMING => 'forming',
        self::DOC_GROUP_STATUS_SENT         => 'sent',
    ];

    /** @var array коды операций выгрузки накладныхх по сервисам */
    static $operation_code_send_waybill = [
        self::RK_SERVICE_ID           => 33,
        self::IIKO_SERVICE_ID         => 5,
        self::TILLYPAD_SERVICE_ID     => 5,
        self::ONE_S_CLIENT_SERVICE_ID => 0
    ];

    /** @var array Список ставок НДС */
    static $nds_list = [
        0,
        10,
        18
    ];
}