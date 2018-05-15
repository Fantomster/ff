<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.05.2018
 * Time: 20:01
 */

namespace frontend\modules\clientintegr\modules\merc\models;

class getVetDocumentListRequest extends BaseRequest
{

    public $localTransactionId;
    public $initiator;
    public $listOptions;
    public $vetDocumentType;
    public $vetDocumentStatus;
    public $enterpriseGuid;

    const DOC_TYPE_INCOMMING = 'INCOMING';
    const DOC_TYPE_OUTGOING = 'OUTGOING';
    const DOC_TYPE_PRODUCTIVE = 'PRODUCTIVE';
    const DOC_TYPE_RETURNABLE = 'RETURNABLE';
    const DOC_TYPE_TRANSPORT = 'TRANSPORT';

    public $types = [
        self::DOC_TYPE_INCOMMING => 'Входящий ВСД',
        self::DOC_TYPE_OUTGOING => 'Исходящий ВСД',
        self::DOC_TYPE_PRODUCTIVE => 'Производственный ВСД',
        self::DOC_TYPE_RETURNABLE => 'Возвратный ВСД',
        self::DOC_TYPE_TRANSPORT => 'Транспортный ВСД',
    ];

    const DOC_STATUS_CONFIRMED = 'CONFIRMED';
    const DOC_STATUS_WITHDRAWN = 'WITHDRAWN';
    const DOC_STATUS_UTILIZED = 'UTILIZED';

    public $statuses = [
        self::DOC_STATUS_CONFIRMED => 'Оформлен',
        self::DOC_STATUS_WITHDRAWN => 'Аннулирован',
        self::DOC_STATUS_UTILIZED => 'Погашен',
    ];

    public function rules()
    {
        return [
            [['localTransactionId', 'vetDocumentType', 'vetDocumentStatus', 'enterpriseGuid'], 'safe'],
        ];
    }
}