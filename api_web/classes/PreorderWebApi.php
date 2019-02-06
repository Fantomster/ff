<?php
/**
 * Date: 04.02.2019
 * Author: Mike N.
 * Time: 14:35
 */

namespace api_web\classes;

use api_web\components\WebApi;
use common\models\Order;

/**
 * Class PreorderWebApi
 *
 * @package api_web\classes
 */
class PreorderWebApi extends WebApi
{

    /**
     * Создание предзаказа из корзины
     *
     * @param $post
     * @return array
     */
    public function create($post)
    {
        return ['STATUS_PREORDER' => Order::STATUS_PREORDER];
    }
}
