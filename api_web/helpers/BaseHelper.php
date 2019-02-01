<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2019-02-01
 * Time: 09:55
 */

namespace api_web\helpers;

/**
 * Class BaseHelper
 * Contain base methods which using in many places of App
 *
 * @package api_web\helpers
 */
class BaseHelper
{
    /**
     * @param      $param
     * @param null $default
     * @return null
     */
    public function isSetDef($param, $default = null)
    {
        if (isset($param) && !empty($param)) {
            return $param;
        }
        return $default;
    }

    /**
     * Set properties to object if his names sending in $arLabels
     * Using if you try write something like this:
     *    if (isset($post['phone']) && $post['phone'] !== null) {
     *        $model->phone = $post['phone'];
     *    }
     *    if (isset($post['email']) && $post['email'] !== null) {
     *        $model->email = $post['email'];
     *    }
     *    if (isset($post['name']) && $post['name'] !== null) {
     *        $model->name = $post['name'];
     *    }
     *  To:
     *     $this->helper->set($obj, $arParams, ['acquirer_id', 'type', 'status', 'sender_guid', 'product_name',
     *     'date']);
     *
     * @param object $obj
     * @param array  $arParams With all params, maybe contain non usage elements
     * @param        $arLabels
     * @return array
     */
    public function set(&$obj, $arParams, $arLabels)
    {
        $arGoodParams = [];
        foreach ($arLabels as $label) {
            if (isset($arParams[$label]) && !empty($arParams[$label])) {
                if ($label == 'date') {
                    $this->set($obj, $arParams[$label], ['from', 'to']);
                } else {
                    $obj->{$label} = $arParams[$label];
                }
                $arGoodParams[$label] = $arParams[$label];
            }
        }

        return $arGoodParams;
    }
}
