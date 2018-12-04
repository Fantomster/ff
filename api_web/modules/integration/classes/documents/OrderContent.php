<?php

namespace api_web\modules\integration\classes\documents;

use api_web\helpers\CurrencyHelper;
use api_web\modules\integration\interfaces\DocumentInterface;
use common\models\OrderContent as BaseOrderContent;
use common\models\IntegrationSettingValue;

/**
 * Class OrderContent
 *
 * @package api_web\modules\integration\classes\documents
 */
class OrderContent extends BaseOrderContent implements DocumentInterface
{
    /**
     * @var
     */
    public static $serviceId;
    private static $isChildOrganization = null;

    /**
     * Порлучение данных из модели
     *
     * @return mixed
     */
    public function prepare()
    {
        if (empty($this->attributes)) {
            return [];
        }

        $return = [
            "id"                            => $this->id,
            "product_id"                    => $this->product_id,
            "edi_number"                    => $this->edi_number,
            "product_name"                  => $this->product->product,
            "quantity"                      => $this->quantity,
            "unit"                          => $this->product->ed,
            "sum_with_vat"                  => CurrencyHelper::asDecimal($this->price * $this->quantity),
            "merc_uuid"                     => $this->merc_uuid ?? null,
            "is_comparised"                 => $this->isComparised(self::$serviceId),
            "is_child_organization_for_map" => $this->getExistsMainOrg()
        ];

        return $return;
    }

    /**
     * Загрузка модели и получение данных
     *
     * @param $key
     * @return $array
     */
    public static function prepareModel($key)
    {
        $model = self::findOne(['id' => $key]);
        if ($model === null) {
            return [];
        }
        return $model->prepare();
    }

    /**
     * Есть ли главный бизнес у заказа
     */
    public function getExistsMainOrg()
    {
        if (is_null(self::$isChildOrganization)) {
            if (IntegrationSettingValue::getSettingsByServiceId(self::$serviceId, $this->order->client_id, ['main_org'])) {
                self::$isChildOrganization = true;
            } else {
                self::$isChildOrganization = false;
            }
        }
        return self::$isChildOrganization;
    }
}