<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 16.05.2018
 * Time: 16:41
 */

namespace frontend\modules\clientintegr\modules\merc\models;


class receiveApplicationResultRequest extends BaseRequest
{

    public $apiKey;
    public $issuerId;
    public $applicationId;

    public function rules()
    {
        return [
            [['apiKey', 'issuerId', 'applicationId'], 'string', 'max' => 255],
            [['apiKey', 'issuerId', 'applicationId'], 'safe'],
        ];
    }

    public function getXML()
    {
        $xml = '<?xml version = "1.0" encoding = "UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
                          xmlns:ws="http://api.vetrf.ru/schema/cdm/application/ws-definitions">
            <soapenv:Header/>
            <soapenv:Body>
                <ws:receiveApplicationResultRequest>
                    <ws:apiKey>'.$this->apiKey.'</ws:apiKey>
                    <ws:issuerId>'.$this->issuerId.'</ws:issuerId>
                    <ws:applicationId>'.$this->applicationId.'</ws:applicationId>
                </ws:receiveApplicationResultRequest>
            </soapenv:Body>
        </soapenv:Envelope>';

        return $xml;
    }
}