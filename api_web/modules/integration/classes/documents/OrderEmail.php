<?php

namespace api_web\modules\integration\classes\documents;

use api_web\classes\DocumentWebApi;
use api_web\modules\integration\interfaces\DocumentInterface;

class OrderEmail extends Order implements DocumentInterface
{
    /**
     * Порлучение данных из модели
     *
     * @return mixed
     */
    public function prepare()
    {
        $return = parent::prepare();
        $return['type'] = DocumentWebApi::TYPE_ORDER_EMAIL;
        $return['replaced_order_id'] = (int)$this->replaced_order_id ?? null;
        $return['doc_date'] = (!empty($this->invoice) ? date("Y-m-d H:i:s T", strtotime($this->invoice->date)) : null);

        return $return;
    }
}