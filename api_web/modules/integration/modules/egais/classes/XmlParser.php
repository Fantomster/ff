<?php

namespace api_web\modules\integration\modules\egais\classes;

use yii\helpers\ArrayHelper;

class XmlParser
{
    /**
     * @param $xml
     * @return array
     */
    public function parseIncomingDocs($xml)
    {
        $xml_parser = simplexml_load_string($xml);

        $result = [];
        foreach ($xml_parser->url as $doc) {
            $url = explode('/', (string)$doc);
            $id = $url[count($url) - 1];
            $type = $url[count($url) - 2];
            array_push($result, [
                'fileId' => empty($doc->attributes()->fileId) || ($doc->attributes()->fileId == 'NODOCID')
                    ? null
                    : (string)$doc->attributes()->fileId,
                'replyId' => empty($doc->attributes()->replyId)
                    ? null
                    : (string)$doc->attributes()->replyId,
                'timestamp' => empty($doc->attributes()->timestamp)
                    ? null
                    : (string)$doc->attributes()->timestamp,
                'type' => $type,
                'id' => (int)$id
            ]);
        }

        return $result;
    }

    /**
     * @param $xml
     * @return array
     */
    public function parseTicket($xml)
    {
        $xml_parser = simplexml_load_string($xml);
        $namespaces = $xml_parser->getNamespaces(true);
        $data = $xml_parser
            ->children($namespaces['ns'])
            ->Document
            ->Ticket
            ->children($namespaces['tc']);

        $result = [];
        foreach ($data as $filed => $value) {

            if (empty($value)) {
                $value = null;
            } elseif ($filed != 'OperationResult' && $filed != 'Result') {
                $value = (string)$value;
            }

            $result[$filed] = $value;
        }

        return $result;
    }

    public function parseReplyRests($xml)
    {
        $xml_parser = simplexml_load_string($xml);
        $namespaces = $xml_parser->getNamespaces(true);
        $data = $xml_parser
            ->children($namespaces['ns'])
            ->Document
            ->ReplyRests
            ->children($namespaces['rst']);

        $result = [
            'RestsDate' => (string)$data->RestsDate,
            'Products' => [
                "StockPosition" => []
            ]
        ];

        foreach ($data->Products->StockPosition as $product) {
            $productFields = $product
                ->Product
                ->children($namespaces['pref']);
            array_push($result['Products']['StockPosition'], [
                'Quantity' => (string)$product->Quantity,
                'InformARegId' => (string)$product->InformARegId,
                'InformBRegId' => (string)$product->InformBRegId,
                'Product' => [
                    'FullName' => (string)$productFields->FullName,
                    'AlcCode' => (string)$productFields->AlcCode,
                    'Capacity' => (string)$productFields->Capacity,
                    'AlcVolume' => (string)$productFields->AlcVolume,
                    'ProductVCode' => (string)$productFields->ProductVCode,
                    'Producer' => $productFields
                        ->Producer
                        ->children($namespaces['oref']),
                ]
            ]);
        }

        return $result;
    }

    /* Возвращает replyId входящего документа */
    /**
     * @param $xml
     * @return string
     */
    public function parseEgaisQuery($xml)
    {
        $xml_parser = simplexml_load_string($xml);

        return (string)$xml_parser->url;
    }

    /* Возвращает массив с id и type входящего документа */
    /**
     * @param $xml
     * @return array|bool
     */
    public function parseUrlDoc($xml)
    {
        $xml_parser = simplexml_load_string($xml);
        if (!empty((string)$xml_parser->url)) {
            $url = explode('/', (string)$xml_parser->url);
            $id = $url[count($url) - 1];
            $type = $url[count($url) - 2];

            return [
                'id' => $id,
                'type' => $type
            ];
        }

        return false;
    }

    /**
     * @param $xml
     * @return array
     */
    public function parseWayBill_v2($xml)
    {
        $xml_parser = simplexml_load_string($xml);
        $namespaces = $xml_parser->getNamespaces(true);
        $complexTypes = $xml_parser
            ->children($namespaces['xs'])
            ->complexType;
        $simpleTypes = $xml_parser
            ->children($namespaces['xs'])
            ->simpleType;

        $resultComplexTypes = [];
        foreach ($complexTypes as $complexType) {
            $name = (string)$complexType->attributes()->name;
            $data = [
                'name' => $name,
                'documentation' => (string)$complexType->annotation->documentation,
            ];
            switch ($name) {
                case 'WayBillType_v2':
                    $merge = ArrayHelper::merge($data, $this->wayBillV2($complexType->sequence));
                    break;
                case 'PositionType':
                case 'TransportType':
                    $merge = ArrayHelper::merge($data, $this->parseElement($complexType->all));
                    break;
                default:
                    $merge = [];
                    break;
            }
            array_push($resultComplexTypes, $merge);
        }

        $resultSimpleTypes = [];
        foreach ($simpleTypes as $simpleType) {
            array_push($resultSimpleTypes, [
                'name' => (string)$simpleType->attributes()->name,
                'documentation' => (string)$simpleType->annotation->documentation,
                'restriction' => [
                    'base' => (string)$simpleType->restriction->attributes()->base,
                    'enumeration' => [
                        'value' => ArrayHelper::getColumn($simpleType->restriction->enumeration, function ($enumeration) {
                            return (string)$enumeration->attributes()->value;
                        }, false),
                    ]
                ]
            ]);
        }

        return ArrayHelper::merge($resultComplexTypes, $resultSimpleTypes);
    }

    // Вспомогательные методы для парсинга большого xml WayBill_v2 документа по блокам
    private function wayBillV2(\SimpleXMLElement $data)
    {
        return [
            'sequence' => ArrayHelper::getColumn($data, function ($block) {
                return ArrayHelper::getColumn($block->element, function ($element) {
                    $elements = [
                        'name' => (string)$element->attributes()->name,
                        'documentation' => (string)$element->annotation->documentation ?: null,
                    ];

                    switch ($elements['name']) {
                        case 'Header':
                            $elements = ArrayHelper::merge($elements, $this->parseElement($element->complexType->all));
                            break;
                        case 'Content':
                            $elements = ArrayHelper::merge($elements, $this->wayBillTypeV2Content($element));
                            break;
                    }

                    return $elements;
                }, false);
            }, false) ?: null
        ];
    }

    // Парсинг тегов element
    private function parseElement(\SimpleXMLElement $data)
    {
        return [
            'elements' => !empty($data)
                ? ArrayHelper::getColumn($data->element, function ($item) {
                    $attr = $item->attributes();

                    return [
                        'name' => (string)$attr->name,
                        'documentation' => (string)$item->annotation->documentation ?: null,
                        'type' => (string)$attr->type ?: null,
                        'default' => (string)$attr->default ?: null,
                        'minOccurs' => (string)$attr->minOccurs ?: null,
                        'maxOccurs' => (string)$attr->maxOccurs ?: null,
                        'nillable' => (string)$attr->nillable ?: null,
                        'simpleType' => !empty($item->simpleType->restriction)
                            ? [
                                'base' => (string)$item->simpleType->restriction->attributes()->base,
                                'maxLength' => (string)$item
                                    ->simpleType
                                    ->restriction
                                    ->maxLength
                                    ->attributes()
                                    ->value
                            ]
                            : null
                    ];
                }, false)
                : null
        ];
    }

    // блок WayBillType_v2 элемент Content
    private function wayBillTypeV2Content(\SimpleXMLElement $data)
    {
        $complexType = $data->complexType->sequence->element;
        $unique = $data->unique;

        return [
            'complexType' => [
                'name' => (string)$complexType->attributes()->name,
                'documentation' => (string)$complexType->annotation->documentation,
                'type' => (string)$complexType->attributes()->type,
                'minOccurs' => (string)$complexType->attributes()->minOccurs,
                'maxOccurs' => (string)$complexType->attributes()->maxOccurs,
            ],
            'unique' => [
                'name' => (string)$unique->attributes()->name,
                'selector' => (string)$unique->selector->attributes()->xpath,
                'field' => (string)$unique->field->attributes()->xpath
            ]
        ];
    }
}