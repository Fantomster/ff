<?php
/**
 * Date: 25.10.2018
 * Author: Mike N.
 * Time: 11:09
 */

namespace api_web\behaviors;

use yii\base\Behavior;
use yii\base\Event;
use yii\elasticsearch\ActiveRecord;

class WaybillContentBehavior extends Behavior
{
    /** @var \common\models\WaybillContent $model */
    public $model;

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterUpdate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate'
        ];
    }

    /**
     * @param $event
     * @return bool
     */
    public function afterUpdate($event)
    {
        $this->recalculateOrderTotalPriceUpdate($event);
        $this->changeStatusWaybill($event);
        return true;
    }

    /**
     * Пересчет total_price в заказе, привязанной к накладной
     *
     * @param $event Event
     * @return bool
     */
    private function recalculateOrderTotalPriceUpdate($event)
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

    /**
     * Смена статуса накладной в "сопоставленно"
     *
     * @param $event
     * @return bool
     */
    public function changeStatusWaybill($event)
    {
        //Если нет агента в накладной, нечего там проверять
        return $this->model->waybill->changeStatusToCompared();
    }
}