<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 11.05.2018
 * Time: 19:18
 */

namespace frontend\modules\clientintegr\modules\merc\models;

class application extends BaseRequest
{
    public $applicationId;
    public $status;
    public $serviceId;
    public $issuerId;
    public $issueDate;
    public $rcvDate;
    public $prdcRsltDate;
    public $data;
    public $result;
    public $errors;

    const ACCEPTED = 'ACCEPTED';
    const IN_PROCESS = 'IN_PROCESS';
    const COMPLETED = 'COMPLETED';
    const REJECTED = 'REJECTED';

    public $statuses = [
        self::ACCEPTED => 'Заявка принята',
        self::IN_PROCESS => 'Заявка обрабатывается',
        self::COMPLETED => 'Заявка успешно обработана',
        self::REJECTED => 'Заявка отклонена',
        ];

    public function rules()
    {
        return [
            [['applicationId'], 'number', 'numberPattern' => '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}'],
            [['applicationId', 'status', 'serviceId','issuerId', 'issueDate', 'rcvDate', 'prdcRsltDate',  'data', 'result', 'errors'], 'safe'],
        ];
    }
}