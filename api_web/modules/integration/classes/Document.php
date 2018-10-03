<?php
namespace api_web\modules\integration\classes;
use api_web\modules\integration\classes\documents\Order;
use api_web\modules\integration\classes\documents\OrderContent;
use api_web\modules\integration\classes\documents\Waybill;
use api_web\modules\integration\classes\documents\WaybillContent;
use yii\web\BadRequestHttpException;

class Document
{
    /**константа типа документа - заказ*/
    const TYPE_ORDER = 'order';
    /**константа типа документа - накладная*/
    const TYPE_WAYBILL = 'waybill';
    /** накладная поставщика **/
    const TYPE_ORDER_EMAIL = 'order_email';
    /** заказ из EDI */
    const TYPE_ORDER_EDI = 'order_edi';

    /**статический список типов документов*/
    public static $TYPE_LIST = [self::TYPE_ORDER, self::TYPE_WAYBILL, self::TYPE_ORDER_EMAIL, self::TYPE_ORDER_EDI];

    private static $models = [
        self::TYPE_WAYBILL => Waybill::class,
        self::TYPE_ORDER => Order::class,
        //self::TYPE_ORDER_EMAIL => IntegrationInvoice::class,
        //self::TYPE_ORDER_EDI => EdiOrder::class,
    ];

    private static $modelsContent = [
        self::TYPE_WAYBILL => WaybillContent::class,
        self::TYPE_ORDER => OrderContent::class,
        /*self::TYPE_ORDER_EMAIL => IntegrationInvoiceContent::class,
        self::TYPE_ORDER_EDI => EdiOrderContent::class,*/
    ];

    /**
     * Метод получения шапки документа
     * @param $document_id
     * @param $type
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function getHeader(array $post)
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

        $className = self::$models[$post['type']];
        return $className::prepareModel($post['document_id']);
    }

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

        $className = self::$modelsContent[$post['type']];
        return $className::prepareModel($post['document_id']);
    }
}