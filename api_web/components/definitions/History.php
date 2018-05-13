<?php

namespace api_web\components\definitions;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="OrderHistory"))
 */
class OrderHistory {
    /**
     * @SWG\Property(@SWG\Xml(name="id"), example=1)
     * @var integer
     */
    public $id;

    /**
     * @SWG\Property(@SWG\Xml(name="created_at"), example="10.10.2016")
     * @var string
     */
    public $created_at;

    /**
     * @SWG\Property(@SWG\Xml(name="completion_date"), example="20.10.2016")
     * @var string
     */
    public $completion_date;

    /**
     * @SWG\Property(@SWG\Xml(name="status"), example=1)
     * @var integer
     */
    public $status;

    /**
     * @SWG\Property(@SWG\Xml(name="status_text"), example="Ожидает подтверждения поставщика")
     * @var string
     */
    public $status_text;

    /**
     * @SWG\Property(@SWG\Xml(name="vendor"), example="POSTAVOK.NET CORPORATION")
     * @var string
     */
    public $vendor;

    /**
     * @SWG\Property(@SWG\Xml(name="create_user"), example="Admin")
     * @var string
     */
    public $create_user;

    /**
     * @SWG\Property(@SWG\Xml(name="currency_id"), example=1)
     * @var integer
     */
    public $currency_id;
}

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="History"))
 */
class History
{
    /**
     * @SWG\Property(@SWG\Xml(name="headers"), type="object", example={
            "id": "Номер заказа",
            "created_at": "Дата создания",
            "completion_date": "Completion Date",
            "status": "Статус",
            "status_text": "Статус",
            "vendor": "Поставщик",
            "currency_id": "Currency Id",
            "create_user": "Заказ создал"
        })
     */
    public $headers;

    /**
     * @SWG\Property(@SWG\Xml(name="orders"), type="array", @SWG\Items(ref="#/definitions/OrderHistory"))
     */
    public $orders;

    /**
     * @SWG\Property(@SWG\Xml(name="orders"), ref="#/definitions/Pagination")
     */
    public $pagination;

    /**
     * @SWG\Property(@SWG\Xml(name="sort"), example="-name")
     * @var string
     */
    public $sort;
}

