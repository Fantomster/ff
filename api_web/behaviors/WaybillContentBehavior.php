<?php
/**
 * Date: 25.10.2018
 * Author: Mike N.
 * Time: 11:09
 */

namespace api_web\behaviors;

use api_web\components\Registry;
use yii\base\Behavior;
use yii\elasticsearch\ActiveRecord;

class WaybillContentBehavior extends Behavior
{
    /** @var \common\models\WaybillContent $model */
    public $model;

    public function events()
    {
        return [
            //Пересчет стоимости заказа
            ActiveRecord::EVENT_AFTER_INSERT => 'recalculateOrderTotalPrice',
            ActiveRecord::EVENT_AFTER_DELETE => 'recalculateOrderTotalPrice',
            ActiveRecord::EVENT_AFTER_UPDATE => 'recalculateOrderTotalPriceUpdate',
            //Меняет статус накладной на "Сопоставлена"
            ActiveRecord::EVENT_AFTER_UPDATE => 'changeStatusWaybill',
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

    /**
     * Смена статуса накладной в "сопоставленно"
     *
     * @param $event
     * @return bool
     */
    public function changeStatusWaybill($event)
    {
        //Если накладная в статусе "Cформирована"
        if ($this->model->waybill->status_id == Registry::WAYBILL_FORMED) {
            $contents = $this->model->waybill->waybillContents;
            /** @var \common\models\WaybillContent $waybillContent */
            //Проверяем все позиции накладной, что они готовы к выгрузке
            foreach ($contents as $waybillContent) {
                if ($waybillContent->readyToExport === false) {
                    return true;
                }
            }
            //Если дошли сюда
            //то ставим статус накладной "Сопоставлена"
            $this->model->waybill->status_id = Registry::WAYBILL_COMPARED;
            return $this->model->waybill->save();
        }
    }
}