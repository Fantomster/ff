<?php
/**
 * Date: 24.10.2018
 * Author: Mike N.
 * Time: 12:23
 */

namespace api_web\behaviors;

use common\models\WaybillContent;
use yii\base\{Behavior, InvalidArgumentException};
use yii\db\ActiveRecord;

/**
 * Class OrderContentBehavior
 *
 * @package api_web\behaviors
 */
class OrderContentBehavior extends Behavior
{
    /** @var \common\models\OrderContent $model */
    public $model;
    /**
     * @var array
     */
    private $updateAttributes = [];

    /**
     * @return array
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND    => 'substitutionValuePriceQuantity',
            ActiveRecord::EVENT_AFTER_UPDATE  => 'substitutionValuePriceQuantity',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'changeValuePriceQuantityFromWaybill'
        ];
    }

    /**
     * Подмена значений цены и количества, если есть накладные
     *
     * @param $event
     * @return bool
     */
    public function substitutionValuePriceQuantity($event): bool
    {
        if (!empty($this->model->waybillContent)) {
            $this->model->price = $this->model->waybillContent->price_without_vat;
            $this->model->quantity = $this->model->waybillContent->quantity_waybill;
            $this->model->vat_product = $this->model->waybillContent->vat_waybill;
        }
        return true;
    }

    /**
     * Если редактируем позицию для которой есть накладная, во всех накладных меняем значение
     * а в заказе оставлям прежнее
     *
     * @param $event
     * @throws InvalidArgumentException
     * @return bool
     */
    public function changeValuePriceQuantityFromWaybill($event): bool
    {
        if (!empty($this->model->waybillContent)) {
            #Проверяем, менялась ли цена
            $this->checkAttribute('price', 'price_without_vat');
            #Проверяем менялось ли количество
            $this->checkAttribute('quantity', 'quantity_waybill');
            #Проверяем менялось ли ставка НДС
            $this->checkAttribute('vat_product', 'vat_waybill');
            #Если что то менялось, обновляем все накладные и сохраняем модель
            if (!empty($this->updateAttributes)) {
                //Получаем список накладных которые нужно обновить
                $waybills = WaybillContent::findAll(['order_content_id' => $this->model->id]);
                foreach ($waybills as $waybill) {
                    //Меняем атрибуты
                    $waybill->setAttributes($this->updateAttributes);
                    //Сохраняем модель, сработает авто пересчет суммы в накладной
                    $waybill->save();
                }
            }
        }
        return true;
    }

    /**
     * Проверка изменения атрибута
     *
     * @param $attribute
     * @param $waybill_attribute
     * @throws InvalidArgumentException
     */
    private function checkAttribute($attribute, $waybill_attribute): void
    {
        //Если атрибут был изменен
        if ($this->model->isAttributeChanged($attribute)) {
            //запоминаем что его обновили
            $this->updateAttributes[$waybill_attribute] = $this->model->getAttribute($attribute);
            //Меняем значение в OrderContent моделе на старое, чтобы не заменилось
            $this->model->setAttribute($attribute, $this->model->getOldAttribute($attribute));
        }
    }
}
