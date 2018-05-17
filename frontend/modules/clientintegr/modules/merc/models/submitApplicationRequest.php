<?php

namespace frontend\modules\clientintegr\modules\merc\models;

class submitApplicationRequest extends BaseRequest
{
    public $apiKey;

    private $soap_namespaces = [];
    private $application;

    public function rules()
    {
        return [
            [['apiKey'], 'string', 'max' => 255],
            [['application'], 'safe'],
        ];
    }

    public function setApplication($application)
    {
        $this->application = $application;
        $this->soap_namespaces = $application->soap_namespaces;
    }

    public function getSoap_namespaces()
    {
        return $this->soap_namespaces;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function getXML()
    {
        $xml =  '<' . '?xml version="1.0" encoding="UTF-8"?' . '>'.PHP_EOL.
        '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"'.PHP_EOL.
                  'xmlns:ws="http://api.vetrf.ru/schema/cdm/application/ws-definitions"'.PHP_EOL.
                  'xmlns:app="http://api.vetrf.ru/schema/cdm/application"'.PHP_EOL;

        foreach ($this->soap_namespaces as $namespace)
            $xml .= $namespace.PHP_EOL;

        $xml .= '>'.PHP_EOL.'<soapenv:Header/>'.PHP_EOL.
        '<soapenv:Body>'.PHP_EOL.
        '<ws:submitApplicationRequest>'.PHP_EOL;
        $xml .= '<ws:apiKey>' . $this->apiKey . '</ws:apiKey>'.PHP_EOL;
        if (isset($this->application)) {
            $xml .= '<app:application>'.PHP_EOL;
            if (isset($this->application->applicationId))
                $xml .= '<app:applicationId>' . $this->application->applicationId . '</app:applicationId>'.PHP_EOL;

            if (isset($this->application->status))
                $xml .= '<app:status>' . $this->application->status . '</app:status>'.PHP_EOL;

            if (isset($this->application->serviceId))
                $xml .= '<app:serviceId>' . $this->application->serviceId . '</app:serviceId>'.PHP_EOL;

            if (isset($this->application->issuerId))
                $xml .= '<app:issuerId>' . $this->application->issuerId . '</app:issuerId>'.PHP_EOL;

            if (isset($this->application->issueDate))
                $xml .= '<app:issueDate>' . $this->application->issueDate . '</app:issueDate>'.PHP_EOL;

            if (isset($this->application->rcvDate))
                $xml .= '<app:rcvDate>' . $this->application->rcvDate . '</app:rcvDate>'.PHP_EOL;

            if (isset($this->application->prdcRsltDate))
                $xml .= '<app:prdcRsltDate>' . $this->application->prdcRsltDate . '</app:prdcRsltDate>'.PHP_EOL;

            if (isset($this->application->data)) {
                $xml .= '<app:data>';
                foreach ($this->application->data as $key => $item)
                    $xml .= $item->getXML().PHP_EOL;

                $xml .= '</app:data>'.PHP_EOL;
            }
            $xml .= '</app:application>'.PHP_EOL;
        }
        $xml .= '</ws:submitApplicationRequest>'.PHP_EOL.
    '</soapenv:Body>'.PHP_EOL.
'</soapenv:Envelope>';
        return $xml;
    }
}