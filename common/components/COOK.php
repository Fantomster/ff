<?php

namespace common\components;

use \Yii;
use yii\web\Cookie;

/**
 * Helper for working with cookie
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-05
 * @author Mixcart
 * @module Frontend
 * @version 1.0
 */
class COOK
{

    const YEAR_IN_SECONDS = 31536000;

    const ORDER_GUIDE_CURRENT = 'order_guide_current';
    const ORDER_GUIDE_SELECTED_PRODUCTS = 'order_guide_selected_products';
    const ORDER_GUIDE_SEARCH_VENDOR = 'order_guide_search_vendor';
    const ORDER_GUIDE_SEARCH_CATALOG = 'order_guide_search_catalog';
    const ORDER_GUIDE_SEARCH_PROODUCTS = 'order_guide_search_products';
    const ORDER_GUIDE_SORT_PRODUCTS = 'order_guide_sort_products';
    const ORDER_GUIDE_SELECTED_VENDOR = 'order_guide_selected_vendor';

    const DELIMITER_VALUE = ';';

    /**
     * Set web-domain cookie indexed by $key - lifetime is one year
     * @param $key string
     * @param $value string
     * @return bool
     * */
    public static function set(string $key = NULL, string $value = ''): bool
    {

        if (!$key) {
            return FALSE;
        }
        Yii::$app->response->cookies->add(new Cookie([
            'name' => $key,
            'value' => $value,
            'expire' => (time() + self::YEAR_IN_SECONDS),
        ]));
        return TRUE;

    }

    /**
     * Remove web-domain cookie recognized as ORDER GUIDE COOKIE params if we edit|view another order guide
     * @param $id int
     * @return bool
     */
    public static function removeOrderGuideParamsIfOrderGuideIsNotCurrent(int $id = NULL): bool
    {
        if (!$id || $id != COOK::get(COOK::ORDER_GUIDE_CURRENT)) {
            COOK::remove(COOK::ORDER_GUIDE_CURRENT);
            COOK::remove(COOK::ORDER_GUIDE_SELECTED_PRODUCTS);
            COOK::remove(COOK::ORDER_GUIDE_SEARCH_VENDOR);
            COOK::remove(COOK::ORDER_GUIDE_SEARCH_CATALOG);
            COOK::remove(COOK::ORDER_GUIDE_SEARCH_PROODUCTS);
            COOK::remove(COOK::ORDER_GUIDE_SORT_PRODUCTS);
            COOK::remove(COOK::ORDER_GUIDE_SELECTED_VENDOR);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Remove web-domain cookie indexed by $key
     * @param $key string
     * @return bool
     * */
    public static function remove(string $key = NULL): bool
    {

        if (!$key) {
            return FALSE;
        }
        Yii::$app->response->cookies->remove($key);
        return TRUE;

    }

    /**
     * Get web-domain cookie indexed by $key
     * @param $key string
     * @return string?
     * */
    public static function get(string $key = NULL): ?string
    {
        if (!$key) {
            return NULL;
        }
        return Yii::$app->request->cookies->get($key);
    }

    /**
     * Get web-domain cookie indexed by $key
     * @param $prefix string?
     * @return bool
     * */
    public static function removeByPrefix(string $prefix = NULL): bool
    {
        if (!$prefix) {
            return FALSE;
        }
        $cookie = Yii::$app->request->cookies->toArray();
        foreach ($cookie as $k => $v) {
            if (substr_count($k, $prefix) && strpos($k, $prefix) == 0) {
                self::remove($k);
            }
        }
        return TRUE;
    }

}