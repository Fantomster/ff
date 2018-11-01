<?php
/**
 * Date: 23.10.2018
 * Time: 15:07
 */

namespace api_web\helpers;

class CurrencyHelper
{
    /**
     * Приведение числа к формату 9999.99
     *
     * @param        $value
     * @param int    $decimal
     * @param string $defaultValue
     * @return string
     */
    public static function asDecimal($value, $decimal = 2, $defaultValue = "0.00")
    {
        \Yii::$app->formatter->nullDisplay = $defaultValue;
        return \Yii::$app->formatter->asDecimal($value, $decimal);
    }
}