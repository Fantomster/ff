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

/**
 * @SWG\Definition(type="object")
 */
class ActChargeOnV2
{
    /**
     * @SWG\Property(@SWG\Xml(name="xml"), example="<?xml version='1.0' encoding='UTF-8'?>
    <ns:Documents Version='1.0'
    xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
    xmlns:ns='http://fsrar.ru/WEGAIS/WB_DOC_SINGLE_01'
    xmlns:oref='http://fsrar.ru/WEGAIS/ClientRef_v2'
    xmlns:pref='http://fsrar.ru/WEGAIS/ProductRef_v2'
    xmlns:iab='http://fsrar.ru/WEGAIS/ActInventoryF1F2Info'
    xmlns:ainp='http://fsrar.ru/WEGAIS/ActChargeOn_v2'
    xmlns:ce='http://fsrar.ru/WEGAIS/CommonEnum'>
    <ns:Owner>
    <ns:FSRAR_ID>030000443640</ns:FSRAR_ID>
    </ns:Owner>
    <ns:Document>
    <ns:ActChargeOn_v2>
    <ainp:Header>
    <ainp:Number>8</ainp:Number>
    <ainp:ActDate>2018-11-02</ainp:ActDate>
    <ainp:Note>Найдена не учтенная продукция</ainp:Note>
    <ainp:TypeChargeOn>Производство_Сливы</ainp:TypeChargeOn>
    </ainp:Header>
    <ainp:Content>
    <ainp:Position>
    <ainp:Identity>1</ainp:Identity>
    <ainp:Product>
    <pref:FullName>Водка 'Журавли'</pref:FullName>
    <pref:AlcCode>0150325000001195171</pref:AlcCode>
    <pref:Capacity>0.7000</pref:Capacity>
    <pref:UnitType>Packed</pref:UnitType>
    <pref:AlcVolume>40.000</pref:AlcVolume>
    <pref:ProductVCode>200</pref:ProductVCode>
    <pref:Producer>
    <oref:UL>
    <oref:ClientRegId>010000000467</oref:ClientRegId>
    <oref:INN>5038002790</oref:INN>
    <oref:KPP>503801001</oref:KPP>
    <oref:FullName>Акционерное общество 'Ликеро-водочный завод
    'Топаз'
    </oref:FullName>
    <oref:ShortName>АО 'ЛВЗ 'Топаз'</oref:ShortName>
    <oref:address>
    <oref:Country>643</oref:Country>
    <oref:RegionCode>50</oref:RegionCode>
    <oref:description>РОССИЯ,141201,МОСКОВСКАЯ ОБЛ,,Пушкино
    г,,Октябрьская ул,46 (за исключением литера Б17, 1 этаж, № на плане 6, литера Б,
    1 этаж, № на
    плане 8) |
    </oref:description>
    </oref:address>
    </oref:UL>
    </pref:Producer>
    </ainp:Product>
    <ainp:Quantity>2</ainp:Quantity>
    <ainp:InformF1F2>
    <ainp:InformF1F2Reg>
    <ainp:InformF1>
    <iab:Quantity>20</iab:Quantity>
    <iab:BottlingDate>2014-11-20</iab:BottlingDate>
    <iab:TTNNumber>Т-000430</iab:TTNNumber>
    <iab:TTNDate>2015-04-06</iab:TTNDate>
    <iab:EGAISFixNumber>91000013637931</iab:EGAISFixNumber>
    <iab:EGAISFixDate>2015-04-06</iab:EGAISFixDate>
    </ainp:InformF1>
    </ainp:InformF1F2Reg>
    </ainp:InformF1F2>
    <ainp:MarkCodeInfo>
    <MarkCode>53N000004928QEWZ9Z804A1309090032244121011104020215019325183103168250</MarkCode>
    <MarkCode>54N000004928QEWZ9Z804A1309090032244121011104020215019325183103168250</MarkCode>
    </ainp:MarkCodeInfo>
    </ainp:Position>
    <ainp:Position>
    <ainp:Identity>2</ainp:Identity>
    <ainp:Product>
    <pref:FullName>Водка особая 'ЗЕЛЕНАЯ МАРКА КЕДРОВАЯ'</pref:FullName>
    <pref:AlcCode>0000000000018264023</pref:AlcCode>
    <pref:Capacity>0.3750</pref:Capacity>
    <pref:UnitType>Packed</pref:UnitType>
    <pref:AlcVolume>40.000</pref:AlcVolume>
    <pref:ProductVCode>200</pref:ProductVCode>
    <pref:Producer>
    <oref:UL>
    <oref:ClientRegId>010000000467</oref:ClientRegId>
    <oref:INN>5038002790</oref:INN>
    <oref:KPP>503801001</oref:KPP>
    <oref:FullName>Акционерное общество 'Ликеро-водочный завод
    'Топаз'
    </oref:FullName>
    <oref:ShortName>АО 'ЛВЗ 'Топаз'</oref:ShortName>
    <oref:address>
    <oref:Country>643</oref:Country>
    <oref:RegionCode>50</oref:RegionCode>
    <oref:description>РОССИЯ,141201,МОСКОВСКАЯ ОБЛ,,Пушкино
    г,,Октябрьская ул,46 (за исключением литера Б17, 1 этаж, № на плане 6, литера Б,
    1 этаж, № на
    плане 8) |
    </oref:description>
    </oref:address>
    </oref:UL>
    </pref:Producer>
    </ainp:Product>
    <ainp:Quantity>2</ainp:Quantity>
    <ainp:InformF1F2>
    <ainp:InformF1F2Reg>
    <ainp:InformF1>
    <iab:Quantity>20</iab:Quantity>
    <iab:BottlingDate>2014-11-20</iab:BottlingDate>
    <iab:TTNNumber>Т-000430</iab:TTNNumber>
    <iab:TTNDate>2015-04-06</iab:TTNDate>
    <iab:EGAISFixNumber>91000013637931</iab:EGAISFixNumber>
    <iab:EGAISFixDate>2015-04-06</iab:EGAISFixDate>
    </ainp:InformF1>
    </ainp:InformF1F2Reg>
    </ainp:InformF1F2>
    <ainp:MarkCodeInfo>
    <MarkCode>55N000004928QEWZ9Z804A1309090032244121011104020215019325183103168250</MarkCode>
    <MarkCode>56N000004928QEWZ9Z804A1309090032244121011104020215019325183103168250</MarkCode>
    </ainp:MarkCodeInfo>
    </ainp:Position>
    </ainp:Content>
    </ns:ActChargeOn_v2>
    </ns:Document>
    </ns:Documents>")
     * @var string
     */
    public $xml;
}