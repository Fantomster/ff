<?php
/**
 * Date: 25.10.2018
 * Author: Mike N.
 * Time: 11:09
 */

namespace api_web\behaviors;

use api_web\components\Registry;
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
            //Пересчет стоимости заказа
            ActiveRecord::EVENT_AFTER_DELETE => 'recalculateOrderTotalPrice',
            //Пересчет стоимости заказа и обновление статуса
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
    private function changeStatusWaybill($event)
    {
        $contents = $this->model->waybill->waybillContents;
        /** @var \common\models\WaybillContent $waybillContent */
        //Проверяем все позиции накладной, что они готовы к выгрузке
        //Если хоть одна не готова, статус не меняем
        foreach ($contents as $waybillContent) {
            if ($waybillContent->readyToExport === false) {
                return true;
            }
        }
        //Если дошли сюда
        //И накладная в статусе "Cформирована" или "Сброшена"
        if (in_array($this->model->waybill->status_id, [Registry::WAYBILL_FORMED, Registry::WAYBILL_RESET])) {
            //то ставим статус накладной "Сопоставлена"
            $this->model->waybill->status_id = Registry::WAYBILL_COMPARED;
            return $this->model->waybill->save();
        }
    }
}