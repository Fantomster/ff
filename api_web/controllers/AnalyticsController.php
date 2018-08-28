<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class AnalyticsController
 * @package api\modules\v1\modules\web\controllers
 * @createdBy Basil A Konakov
 * @createdAt 2018-08-28
 * @author Mixcart
 * @module WEB-API
 * @version 2.0
 */
class AnalyticsController extends WebApiController
{

    /**
     * @SWG\Post(path="/analytics/client-goods",
     *     tags={"Analytics"},
     *     summary="Ресторан: Статистика по товарам",
     *     description="Ресторан: Статистика по товарам",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *             @SWG\Property(property="user", ref="#/definitions/User"),
     *             @SWG\Property(
     *                 property="request",
     *                 type="object",
     *                 default={
     *                     "id": 1,
     *                     "email": "neo@neo.com"
     *                 }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *             default={
     *                 "vendor_id": 1,
     *                 "employee_id": 21,
     *                 "order_status_id": 4,
     *                 "curency_id": 1,
     *                 "date": {
     *                     "from": "23.08.2018",
     *                     "to": "24.08.2018"
     *                 }
     *             }
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "UnauthorizedHttpException"
     *     )
     * )
     */
    public function actionClientGoods()
    {
        $this->response = $this->container->get('AnalyticsWebApi')->clientGoods($this->request);
    }

}