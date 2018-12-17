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

    public static function actChargeOnV2($fsrar_id, $parameters)
    {
        $xmlFile = <<<XMLHEADER
<?xml version="1.0" encoding="UTF-8"?>
            <ns:Documents Version="1.0"
                      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                      xmlns:ns="http://fsrar.ru/WEGAIS/WB_DOC_SINGLE_01"
                      xmlns:oref="http://fsrar.ru/WEGAIS/ClientRef_v2"
                      xmlns:pref="http://fsrar.ru/WEGAIS/ProductRef_v2"
                      xmlns:iab="http://fsrar.ru/WEGAIS/ActInventoryF1F2Info"
                      xmlns:ainp="http://fsrar.ru/WEGAIS/ActChargeOn_v2"
                      xmlns:ce="http://fsrar.ru/WEGAIS/CommonEnum">
            <ns:Owner>
                <ns:FSRAR_ID>{$fsrar_id}</ns:FSRAR_ID>
            </ns:Owner>
            <ns:Document>
                <ns:ActChargeOn_v2>
                    <ainp:Header>
                        <ainp:Number>{$parameters['number']}</ainp:Number>
                        <ainp:ActDate>{$parameters['date']}</ainp:ActDate>
                        <ainp:Note>{$parameters['note']}</ainp:Note>
                        <ainp:TypeChargeOn>{$parameters['type']}</ainp:TypeChargeOn>
                    </ainp:Header>
                    <ainp:Content>
XMLHEADER;

        foreach ($parameters['items'] as $item) {
            $product = $item['product'];
            $producer = $item['product']['producer'];
            $address = $item['product']['producer']['address'];
            $inform_f1 = $item['inform_f1'];
            $mark_code_info = $item['mark_code_info'];

            $xmlFile .= <<<XMLBODY
                <ainp:Position>
                    <ainp:Identity>{$item['identity']}</ainp:Identity>
                    <ainp:Product>
                        <pref:FullName>{$product['full_name']}</pref:FullName>
                        <pref:AlcCode>{$product['alc_code']}</pref:AlcCode>
                        <pref:Capacity>{$product['capacity']}</pref:Capacity>
                        <pref:UnitType>{$product['unit_type']}</pref:UnitType>
                        <pref:AlcVolume>{$product['alc_volume']}</pref:AlcVolume>
                        <pref:ProductVCode>{$product['product_v_code']}</pref:ProductVCode>
                        <pref:Producer>
                            <oref:UL>
                                <oref:ClientRegId>{$producer['client_reg_id']}</oref:ClientRegId>
                                <oref:INN>{$producer['inn']}</oref:INN>
                                <oref:KPP>{$producer['kpp']}</oref:KPP>
                                <oref:FullName>{$producer['full_name']}</oref:FullName>
                                <oref:ShortName>{$producer['short_name']}</oref:ShortName>
                                <oref:address>
                                    <oref:Country>{$address['country']}</oref:Country>
                                    <oref:RegionCode>{$address['region_code']}</oref:RegionCode>
                                    <oref:description>{$address['description']}</oref:description>
                                </oref:address>
                            </oref:UL>
                        </pref:Producer>
                    </ainp:Product>
                    <ainp:Quantity>{$item['quantity']}</ainp:Quantity>
                    <ainp:InformF1F2>
                        <ainp:InformF1F2Reg>
                            <ainp:InformF1>
                                <iab:Quantity>{$inform_f1['quantity']}</iab:Quantity>
                                <iab:BottlingDate>{$inform_f1['bottling_date']}</iab:BottlingDate>
                                <iab:TTNNumber>{$inform_f1['ttn_number']}</iab:TTNNumber>
                                <iab:TTNDate>{$inform_f1['ttn_date']}</iab:TTNDate>
                                <iab:EGAISFixNumber>{$inform_f1['egais_fix_number']}</iab:EGAISFixNumber>
                                <iab:EGAISFixDate>{$inform_f1['egais_fix_date']}</iab:EGAISFixDate>
                            </ainp:InformF1>
                        </ainp:InformF1F2Reg>
                    </ainp:InformF1F2>
                    <ainp:MarkCodeInfo>
                        <MarkCode>{$mark_code_info['mark_code_first']}</MarkCode>
                        <MarkCode>{$mark_code_info['mark_code_second']}</MarkCode>
                    </ainp:MarkCodeInfo>
                </ainp:Position>
XMLBODY;
        }

        $xmlFile .= <<<XMLFOOTER
                        </ainp:Content>
                    </ns:ActChargeOn_v2>
                </ns:Document>
            </ns:Documents>
XMLFOOTER;

        return $xmlFile;
    }

    public static function actWriteOffV3($fsrar_id, $parameters)
    {
        $xmlFile = <<<XMLHEADER
<?xml version='1.0' encoding='UTF-8'?>
            <ns:Documents Version='1.0'
            xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'
            xmlns:ns='http://fsrar.ru/WEGAIS/WB_DOC_SINGLE_01'
            xmlns:pref='http://fsrar.ru/WEGAIS/ProductRef_v2'
            xmlns:awr='http://fsrar.ru/WEGAIS/ActWriteOff_v3'
            xmlns:ce='http://fsrar.ru/WEGAIS/CommonV3'>
            <ns:Owner>
                <ns:FSRAR_ID>{$fsrar_id}</ns:FSRAR_ID>
            </ns:Owner>
            <ns:Document>
                <ns:ActWriteOff_v3>
                    <awr:Identity>{$parameters['identity']}</awr:Identity>
                    <awr:Header>
                        <awr:ActNumber>{$parameters['number']}</awr:ActNumber>
                        <awr:ActDate>{$parameters['date']}</awr:ActDate>
                        <awr:TypeWriteOff>{$parameters['type_write_off']}</awr:TypeWriteOff>
                        <awr:Note>{$parameters['note']}</awr:Note>
                    </awr:Header>
                    <awr:Content>
XMLHEADER;

        foreach ($parameters['positions'] as $position) {
            $xmlFile .= <<<XMLBODY
                <awr:Position>
                    <awr:Identity>{$position['identity']}</awr:Identity>
                    <awr:Quantity>{$position['quantity']}</awr:Quantity>
                    <awr:SumSale>{$position['sum_sale']}</awr:SumSale>
                    <awr:InformF1F2>
                        <awr:InformF2>
                            <pref:F2RegId>{$position['inform_f2']['f2_reg_id']}</pref:F2RegId>
                        </awr:InformF2>
                    </awr:InformF1F2>
                    <awr:MarkCodeInfo>
                        <ce:amc>{$position['mark_code_info']['mark_code_first']}</ce:amc>
                        <ce:amc>{$position['mark_code_info']['mark_code_second']}</ce:amc>
                    </awr:MarkCodeInfo>
                </awr:Position>
XMLBODY;
        }
        
        $xmlFile .= <<<XMLFOOTER
                    </awr:Content>
                </ns:ActWriteOff_v3>
            </ns:Document>
        </ns:Documents>
XMLFOOTER;

        return $xmlFile;
    }
}