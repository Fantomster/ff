<?php

namespace common\components\edi;

use yii\base\Component;
use common\models\Catalog;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\Currency;
use common\models\EdiOrder;
use common\models\EdiOrderContent;
use common\models\EdiOrganization;
use common\models\Order;
use common\models\OrderContent;
use common\models\OrderStatus;
use common\models\Organization;
use common\models\RelationSuppRest;
use common\models\User;
use frontend\controllers\OrderController;
use yii\db\Expression;
use Yii;

class EDIProvidersClass extends Component
{

    public function getDateData($order): array
    {
        $arr = [];
        $arr['created_at'] = $this->formatDate($order->created_at ?? '');
        $arr['requested_delivery_date'] = $this->formatDate($order->requested_delivery ?? '');
        $arr['requested_delivery_time'] = $this->formatTime($order->requested_delivery ?? '');
        $arr['actual_delivery_date'] = $this->formatDate($order->actual_delivery ?? '');
        $arr['actual_delivery_time'] = $this->formatTime($order->actual_delivery ?? '');
        return $arr;
    }

    private function formatDate(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('Y-m-d');
    }

    private function formatTime(String $dateString): String
    {
        $date = new \DateTime($dateString);
        return $date->format('H:i');
    }
}