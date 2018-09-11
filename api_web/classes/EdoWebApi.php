<?php

namespace api_web\classes;

use api_web\components\WebApi;
use common\models\AllService;

/**
 * Class EdoWebApi
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-09-11
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */
class EdoWebApi extends WebApi
{

    /**
     * История заказов
     * @param array $post
     * @return array
     */
    public function orderHistory(array $post)
    {
        $post['search']['service_id'] = (AllService::findOne(['denom' => 'EDI']))->id;
        return $this->container->get('OrderWebApi')->getHistory($post);
    }

}