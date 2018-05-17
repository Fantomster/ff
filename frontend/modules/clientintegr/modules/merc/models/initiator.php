<?php

namespace frontend\modules\clientintegr\modules\merc\models;


class initiator extends BaseRequest
{
    public $UUID;
    public $login;

    public $soap_namespaces = 'xmlns:com="http://api.vetrf.ru/schema/cdm/argus/common"';

    public function rules()
    {
        return [
            [['UUID', 'login'], 'safe'],
        ];
    }

    public function getXML()
    {
            $xml = '<merc:initiator>'.PHP_EOL;
            if (isset($thist->initiator->UUID))
                $xml .= '<com:UUID>' . $this->initiator->UUID . '</com:UUID>'.PHP_EOL;

            if (isset($this->initiator->login))
                $xml .= '<com:login>' . $this->initiator->login . '</com:login>'.PHP_EOL;

            $xml .= '</merc:initiator>'.PHP_EOL;
        return $xml;
    }
}