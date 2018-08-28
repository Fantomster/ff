<?php

namespace api_web\classes;

use api_web\components\WebApi;
use common\components\SimpleChecker;
use common\models\Order;
use yii\web\BadRequestHttpException;
use Yii;


/**
 * Class AnalyticsWebApi
 * @package api_web\classes
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-28
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */
class AnalyticsWebApi extends WebApi
{

    var $rules = [
        'client-goods' => [
            'type' => [
                'wholeInt' => [
                    'search' => ['vendor_id', 'employee_id', 'order_status_id', 'currency_id'],
                    'pagination' => ['page', 'page_size'],
                ],
                'dateDMYY' => [
                    'search' => [
                        'date' => ['from', 'to'],
                    ],
                ],
            ],
            'required' => [
                'pagination' => ['page', 'page_size'],
            ],
        ],
    ];

    /**
     * Валидация параметров
     * @param $post array
     * @param $rules array
     * @throws BadRequestHttpException
     */
    private function validateRules(array $post = [], array $rules = [])
    {

        // валидация обязательных параметров
        foreach ($rules['required'] as $key1 => $v) {
            foreach ($v as $key2 => $keys3) {
                if (is_array($keys3)) {
                    foreach ($keys3 as $key3) {
                        if (empty($post[$key1][$key2][$key3])) {
                            throw new BadRequestHttpException('parameter_was_not_specified: ' . $key1 . '[' . $key2 . '][' . $key3 . ']');
                        }
                    }
                } elseif (empty($post[$key1][$keys3])) {
                    throw new BadRequestHttpException('parameter_was_not_specified: ' . $key1 . '[' . $keys3 . ']');
                }
            }
        }

        // валидация типов данных ()
        foreach ($rules['type'] as $k => $v) {
            if ($k == 'dateDMYY') {
                foreach ($v as $key1 => $vv) {
                    foreach ($vv as $key2 => $keys3) {
                        foreach ($keys3 as $key3) {
                            if (isset($post[$key1][$key2][$key3])) {
                                $date = explode('.', $post[$key1][$key2][$key3]);
                                for ($i = 0; $i < 3; $i++) {
                                    if (
                                        !isset($date[$i]) || !SimpleChecker::validateWholeNumerExactly($date[$i])
                                    ) {
                                        throw new BadRequestHttpException('bad_request_parameter (must be DD.MM.YYYY!): ' . $key1 . '[' . $key2 . '][' . $key3 . ']');
                                    }
                                }
                                if (!checkdate($date[1], $date[0], $date[2])) {
                                    throw new BadRequestHttpException('bad_request_parameter (must be DD.MM.YYYY): ' . $key1 . '[' . $key2 . '][' . $key3 . ']');
                                }
                            }
                        }
                    }
                }
            } elseif ($k == 'wholeInt') {
                foreach ($v as $key1 => $vv) {
                    foreach ($vv as $key2) {
                        if (isset($post[$key1][$key2]) && !SimpleChecker::validateWholeNumerExactly($post[$key1][$key2])) {
                            throw new BadRequestHttpException('bad_request_parameter (must be integer): ' . $key1 . '[' . $key2 . ']');
                        }
                    }
                }
            }
        }
    }

    /**
     * Ресторан: Статистика по товарам
     * @param $post
     * @return array
     * @throws BadRequestHttpException
     */
    public function clientGoods($post)
    {

        // загрузка правил валидации
        $rules = $this->rules['client-goods'];

        $this->validateRules($post, $rules);
        // проверка правил валидации

        // использование параметров пагинации
        $limit = ($post['pagination']['page'] - 1) * $post['pagination']['page_size'] . ', ' . $post['pagination']['page_size'];

        $queryParams = [
            ':org_id' => $this->user->organization->id,
        ];

        // фильтр - вендор ай ди
        $filterVendor = NULL;
        if (isset($post['search']['vendor_id'])) {
            $queryParams[':vendor_id'] = $post['search']['vendor_id'];
            $filterVendor = ' AND `order`.`vendor_id` = :vendor_id ';
        }

        // фильтр - менеджер ай ди
        $filterEmployee = NULL;
        $joinEmployee = NULL;
        if (isset($post['search']['employee_id'])) {
            $queryParams[':employee_id'] = $post['search']['employee_id'];
            $joinEmployee = '
            LEFT JOIN `order_assignment` ON `order`.`id` = `order_assignment`.`order_id`';
            $filterEmployee = ' AND `order_assignment`.`assigned_to` = :employee_id ';
        }

        // фильтр - статус заказа
        $statuses = [
            Order::STATUS_AWAITING_ACCEPT_FROM_VENDOR,
            Order::STATUS_AWAITING_ACCEPT_FROM_CLIENT,
            Order::STATUS_PROCESSING,
            Order::STATUS_DONE,
            Order::STATUS_FORMING,
        ];
        $filterStatus = ' AND `order`.`status` IN ('.implode(', ', $statuses).') ';
        if (isset($post['search']['order_status_id']) && in_array($post['search']['order_status_id'], $statuses)) {
            $queryParams[':status_id'] = $post['search']['order_status_id'];
            $filterStatus = ' AND `order`.`status` = :status_id ';
        }

        // фильтр - валюта
        if (isset($post['search']['currency_id'])) {
            $queryParams[':currency_id'] = $post['search']['currency_id'];
            $filterStatus = ' AND `currency`.`id` = :currency_id ';
        }

        // фильтр - даты
        $dateFromStatus = NULL;
        if (isset($post['search']['date']['from'])) {
            $date_from = explode('.', $post['search']['date']['from']);
            $queryParams[':date_from'] = $date_from[2].'-'.$date_from[1].'-'.$date_from[0].' 00:00:00';
            $dateFromStatus = ' AND `order`.`created_at` >= :date_from ';
        }
        $dateToStatus = NULL;
        if (isset($post['search']['date']['to'])) {
            $date_to = explode('.', $post['search']['date']['to']);
            $queryParams[':date_to'] = $date_to[2].'-'.$date_to[1].'-'.$date_to[0].' 23:59:59';
            $dateToStatus = ' AND `order`.`created_at` >= :date_to ';
        }

        $query = '
            SELECT 
	          `catalog_base_goods`.product AS `name`,
              FORMAT(SUM(`order_content`.`quantity`), 2) AS `count`,
	          FORMAT(SUM(`order_content`.`quantity` * `order_content`.`price`), 2) AS total,
              `order`.`currency_id` AS currency_id,
              `currency`.`symbol` AS currency
            FROM 
              order_content 
            LEFT JOIN `order` ON `order`.`id` = `order_content`.`order_id` '.$joinEmployee.'
            LEFT JOIN `catalog_base_goods` ON `catalog_base_goods`.`id` = `order_content`.`product_id`
            LEFT JOIN `currency` ON `currency`.`id` = `order`.`currency_id`
            WHERE (
                `order`.`client_id` = :org_id '.$filterVendor.$filterEmployee.$filterStatus.$dateFromStatus.$dateToStatus.'
            ) 
            GROUP BY product_id
            ORDER BY total DESC
            LIMIT '.$limit.';';

        return Yii::$app->db->createCommand($query, $queryParams)->queryAll();
    }

}