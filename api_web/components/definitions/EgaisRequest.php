<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object")
 */
class ActWriteOffV3
{
    /**
     * @SWG\Property(@SWG\Xml(name="xml"), example="<?xml version='1.0' encoding='UTF-8'?>
    <ns:Documents Version='1.0'
    xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
    xmlns:ns='http://fsrar.ru/WEGAIS/WB_DOC_SINGLE_01'
    xmlns:pref='http://fsrar.ru/WEGAIS/ProductRef_v2'
    xmlns:awr='http://fsrar.ru/WEGAIS/ActWriteOff_v3'
    xmlns:ce='http://fsrar.ru/WEGAIS/CommonV3'>
    <ns:Owner>
    <ns:FSRAR_ID>030000443640</ns:FSRAR_ID>
    </ns:Owner>
    <ns:Document>
    <ns:ActWriteOff_v3>
    <awr:Identity>456</awr:Identity>
    <awr:Header>
    <awr:ActNumber>13</awr:ActNumber>
    <awr:ActDate>2018-11-02</awr:ActDate>
    <awr:TypeWriteOff>Реализация</awr:TypeWriteOff>
    <awr:Note>текст комментария</awr:Note>
    </awr:Header>
    <awr:Content>
    <awr:Position>
    <awr:Identity>1</awr:Identity>
    <awr:Quantity>2</awr:Quantity>
    <awr:SumSale>123.00</awr:SumSale>
    <awr:InformF1F2>
    <awr:InformF2>
    <pref:F2RegId>TEST-FB-000000036821312</pref:F2RegId>
    </awr:InformF2>
    </awr:InformF1F2>
    <awr:MarkCodeInfo>
    <ce:amc>53N000004928QEWZ9Z334A1309090032244121011104020215019325183103168250</ce:amc>
    <ce:amc>54N000004928QEWZ9Z334A1309090032244121011104020215019325183103168250</ce:amc>
    </awr:MarkCodeInfo>
    </awr:Position>
    <awr:Position>
    <awr:Identity>2</awr:Identity>
    <awr:Quantity>2</awr:Quantity>
    <awr:SumSale>123.00</awr:SumSale>
    <awr:InformF1F2>
    <awr:InformF2>
    <pref:F2RegId>TEST-FB-000000036821313</pref:F2RegId>
    </awr:InformF2>
    </awr:InformF1F2>
    <awr:MarkCodeInfo>
    <ce:amc>55N000004928QEWZ9Z334A1309090032244121011104020215019325183103168250</ce:amc>
    <ce:amc>56N000004928QEWZ9Z334A1309090032244121011104020215019325183103168250</ce:amc>
    </awr:MarkCodeInfo>
    </awr:Position>
    </awr:Content>
    </ns:ActWriteOff_v3>
    </ns:Document>
    </ns:Documents>")
     * @var string
     */
    public $xml;
}