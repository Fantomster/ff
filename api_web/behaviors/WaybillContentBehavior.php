<?php
/**
 * Date: 25.10.2018
 * Author: Mike N.
 * Time: 11:09
 */

namespace api_web\behaviors;

use yii\base\Behavior;
use yii\elasticsearch\ActiveRecord;

class WaybillContentBehavior extends Behavior
{
    /** @var \common\models\WaybillContent $model */
    public $model;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'recalculateOrderTotalPrice',
            ActiveRecord::EVENT_AFTER_DELETE => 'recalculateOrderTotalPrice',
            ActiveRecord::EVENT_AFTER_UPDATE => 'recalculateOrderTotalPriceUpdate'
        ];
    }

    /**
     * Пересчет total_price в заказе, привязанной к накладной
     *
     * @param $event
     * @return bool
     */
    public function recalculateOrderTotalPriceUpdate($event)
    {
        //Смотрим, были ли, изменены аттрибуты, изза которых стоит пересчитывать стоимость заказа
        if (isset($event->changedAttributes['sum_without_vat'])) {
            $this->recalculateOrderTotalPrice($event);
        }
        return true;
    }

    /**
     * Пересчет total_price в заказе, привязанной к накладной
     *
     * @param $event
     * @return bool
     */
    public function recalculateOrderTotalPrice($event)
    {
        $orderContent = $this->model->orderContent;
        //Если есть связь с заказом пересчитываем его total_price
        if (!empty($orderContent)) {
            $orderContent->order->calculateTotalPrice();
        }
        return true;
    }
}