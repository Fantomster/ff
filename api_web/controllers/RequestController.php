<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class RequestController
 * @package api_web\controllers
 */
class RequestController extends WebApiController
{
    /**
     * @SWG\Post(path="/request/list",
     *     tags={"Request"},
     *     summary="Список заявок",
     *     description="Список заявок",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "search":{
     *                                   "status": 1,
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               }
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"result":{
     *          {
     *                       "id": 74,
     *                       "name": "цукен",
     *                       "status": 1,
     *                       "created_at": "30.06.2017 14:17",
     *                       "category": "Мебель",
     *                       "category_id": 231,
     *                       "client": {
     *                       "id": 1,
     *                           "name": "Космическая пятница",
     *                           "phone": "",
     *                           "email": "investor@f-keeper.ru",
     *                           "address": "Бакалейная ул., 50А, Казань, Респ. Татарстан, Россия, 420095",
     *                           "image": "https://fkeeper.s3.amazonaws.com/org-picture/8f060fc32d84198ec60212d7595191a0.jpg",
     *                           "type_id": 1,
     *                           "type": "Ресторан",
     *                           "rating": 0,
     *                           "city": "Казань",
     *                           "administrative_area_level_1": "Республика Татарстан",
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "vendor": {
     *                           "id": 4,
     *                           "name": "ООО Рога и Копыта",
     *                           "phone": "",
     *                           "email": "",
     *                           "address": "ул. Госпитальный Вал, Москва, Россия",
     *                           "image": "https://fkeeper.s3.amazonaws.com/org-picture/c49766f11fe1908675cb4c2808126ee8.jpg",
     *                           "type_id": 2,
     *                           "type": "Поставщик",
     *                           "rating": 3.7,
     *                          "city": "Москва",
     *                           "administrative_area_level_1": null,
     *                           "country": "Россия",
     *                           "about": ""
     *                       },
     *                       "hits": 0,
     *                       "count_callback": 2,
     *                       "urgent": 1
     *           }
     *     },
     *              "pagination": {
     *                   "page": 1,
     *                   "page_size": 12,
     *                   "total_page": 1
     *               }
     *          }),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionList()
    {
        $this->response = $this->container->get('RequestWebApi')->getList($this->request);
    }

    /**
     * @SWG\Post(path="/request/category-list",
     *     tags={"Request"},
     *     summary="Список категорий",
     *     description="Список категорий",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={{"id": 74, "name": "цукен"}}
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException||ValidationException"
     *     )
     * )
     */
    public function actionCategoryList()
    {
        $this->response = $this->container->get('RequestWebApi')->getCategoryList();
    }
}