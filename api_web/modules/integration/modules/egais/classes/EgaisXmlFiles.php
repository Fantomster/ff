<?php

namespace api_web\modules\integration\modules\egais\classes;

class EgaisXmlFiles
{
    public function queryRests($fsrar_id)
    {
        return "<?xml version='1.0' encoding='UTF-8'?>
        <ns:Documents Version='1.0'
        xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
        xmlns:ns='http://fsrar.ru/WEGAIS/WB_DOC_SINGLE_01'
        xmlns:qp='http://fsrar.ru/WEGAIS/QueryParameters'>
        <ns:Owner>
        <ns:FSRAR_ID>{$fsrar_id}</ns:FSRAR_ID>
        </ns:Owner>
        <ns:Document>
        <ns:QueryRests></ns:QueryRests>
        </ns:Document>
        </ns:Documents>";
    }
}