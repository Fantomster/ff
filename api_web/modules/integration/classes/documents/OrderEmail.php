<?php

namespace api_web\modules\integration\classes\documents;

use api_web\classes\DocumentWebApi;
use api_web\helpers\WebApiHelper;
use api_web\modules\integration\interfaces\DocumentInterface;

/**
 * Class OrderEmail
 *
 * @package api_web\modules\integration\classes\documents
 */
class OrderEmail extends Order implements DocumentInterface
{
    /**
     * Порлучение данных из модели
     *
     * @throws \Exception
     * @return mixed
     */
    public function prepare()
    {
        $return = parent::prepare();
        $return['type'] = DocumentWebApi::TYPE_ORDER_EMAIL;
        $return['replaced_order_id'] = (int)$this->replaced_order_id ?? null;
        $return['doc_date'] = (!empty($this->invoice) ? WebApiHelper::asDatetime($this->invoice->date) : null);

        return $return;
    }
}