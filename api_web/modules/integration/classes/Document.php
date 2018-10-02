<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 10:55 AM
 */

namespace api_web\modules\integration\classes;

use api\common\models\iiko\iikoAgent;
use api\common\models\RkAgent;
use api_web\modules\integration\modules\one_s\models\one_sAgent;
use common\models\Waybill;
use yii\web\BadRequestHttpException;

class Document
{
    /**константа типа документа - заказ*/
    const TYPE_ORDER = 'order';
    /**константа типа документа - накладная*/
    const TYPE_WAYBILL = 'waybill';

    /**статический список типов документов*/
    public static $TYPE_LIST = [self::TYPE_ORDER, self::TYPE_WAYBILL];

    private static $agents = [
      1 => RkAgent::class,
      2 => iikoAgent::class,
      8 => one_sAgent::class,
    ];

    /**
     * Метод получения детальной части документа
     * @param $document_id
     * @param $type
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getContent(array $post)
    {
        if (!isset($post['type'])) {
            throw new BadRequestHttpException("empty_param|type");
        }
        if (empty($post['document_id'])) {
            throw new BadRequestHttpException("empty_param|document_id");
        }

        if (!in_array(strtolower($post['type']), self::$TYPE_LIST)) {
            throw new BadRequestHttpException('dont support this type');
        }

        $method = strtolower($post['type']) . 'Content';
        return $this->{$method}($post['document_id']);
    }

    /**
     * Содержимое корзины
     * @return array
     */
    public function orderContent($order_id)
    {
        $client = $this->user->organization;
        //Корзина теущего клиента
        $content = $client->_getCart();

        if (empty($content)) {
            return [];
        }
        $return = [];
        $items = [];
        /**
         * @var CartContent $row
         */
        foreach ($content as $row) {
            $items[$row->vendor->id][] = $this->prepareProduct($row);
            if (!isset($return[$row->vendor->id])) {
                $return[$row->vendor->id] = [
                    'id' => $row->vendor->id,
                    'delivery_price' => $this->getCart()->calculateDelivery($row->vendor_id),
                    'for_min_cart_price' => $this->getCart()->forMinCartPrice($row->vendor_id),
                    'for_free_delivery' => $this->getCart()->forFreeDelivery($row->vendor_id),
                    'total_price' => $this->getCart()->calculateTotalPrice($row->vendor_id),
                    'vendor' => WebApiHelper::prepareOrganization($row->vendor),
                    'currency' => $items[$row->vendor->id][0]['currency'],
                    'items' => $items[$row->vendor->id]
                ];
            } else {
                $return[$row->vendor->id]['items'] = $items[$row->vendor->id];
            }
        }

        return array_values($return);
    }

    public function waybillHeader($id)
    {
        $waybill = Waybill::findOne(['id' => $id]);

        if (empty($waybill)) {
            return [];
        }

            $agent_class = self::$agents[$waybill->service_id];
            $agent_class::findOne([''])

        }

        $return = [
            "id" => 22666,
            "type" => self::TYPE_WAYBILL,
            "status_id" => $waybill->bill_status_id,
            "status_text" => "",
            "agent" => [
                "uid" => "11232123",
                "name" => "Опт Холод",
                "difer" => false,
            ],
            "vendor" => [
                "id" => 3489,
                "name" => "Halal Organic Food",
                "difer" => false,
            ],
            "is_mercury_cert" => $waybill->getIsMercuryCert(),
            "count" => $waybill->getTotalCount(),
            "total_price" => $waybill->getTotalPrice(),
            "doc_date" => date("Y-m-d H:i:s T", strtotime($waybill->doc_date)),
        ];
    }
}