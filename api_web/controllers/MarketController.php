<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class MarketController
 * @package api_web\controllers
 */
class MarketController extends WebApiController
{
    /**
     * @SWG\Post(path="/market/products",
     *     tags={"Market"},
     *     summary="Список товаров на маркете",
     *     description="Получить список товаров с Маркета",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  ref="#/definitions/UserWebApiDefinition"
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "search":{
     *                                   "product":"искомая строка"
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               },
     *                               "sort":"-product"
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *{
     *          "headers":{
     *              {
     *                  "id": "ID",
    "product": "Название"
     *              }
     *          }
     *          ,
     *          "products":{
     *          {
     *              "id": 95940,
    "product":"Ячменное фирменное св. фильтрованное паст. (20 шт.)",
    "catalog_id":652,
    "price":995.5,
    "rating":3.3,
    "supplier":"ООО АН-ПРИНТ",
    "brand":"",
    "article":"843",
    "currency":"RUB",
    "ed":"шт",
    "image":"https://mixcart.ru/fmarket/images/image-category/51.jpg",
    "in_basket":0
     *          }}
     *          ,
     *          "pagination":{
     *              "page":1,
     *              "total_page":17,
     *              "page_size":12
     *          },
     *          "sort":"-product"
     *     }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     */
    public function actionProducts()
    {
        $this->response = $this->container->get('MarketWebApi')->products($this->request);
    }

    /**
     * @SWG\Post(path="/market/categories",
     *     tags={"Market"},
     *     summary="Список категорий товаров на маркете",
     *     description="Получить список категорий товаров с Маркета",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *{
     *          "Мясо": {
    {
    "id": 2,
    "name": "Баранина"
    }
     *         }}
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     */
    public function actionCategories()
    {
        $this->response = $this->container->get('MarketWebApi')->categories($this->request);
    }

    /**
     * @SWG\Post(path="/market/product",
     *     tags={"Market"},
     *     summary="Информация о товаре",
     *     description="Получить полную информацию о товаре с Маркета",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  type="object",
     *                  default={"token":"123123", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={"id":1}
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default=
     *{
     *       "product": {
     *          "id": 95940,
    "product":"Ячменное фирменное св. фильтрованное паст. (20 шт.)",
    "catalog_id":652,
    "price":995.5,
    "rating":3.3,
    "supplier":"ООО АН-ПРИНТ",
    "brand":"",
    "article":"843",
    "currency":"RUB",
    "ed":"шт",
    "image":"https://mixcart.ru/fmarket/images/image-category/51.jpg",
    "in_basket":0
     *       }
     *}
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     */
    public function actionProduct()
    {
        $this->response['product'] = $this->container->get('MarketWebApi')->product($this->request);
    }

    /**
     * @SWG\Post(path="/market/organizations",
     *     tags={"Market"},
     *     summary="Список организаций на маркете",
     *     description="Получить список организаций с Маркета",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(
     *                  property="user",
     *                  ref="#/definitions/UserWebApiDefinition"
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                               "search":{
     *                                   "name":"искомая строка"
     *                               },
     *                               "pagination":{
     *                                   "page":1,
     *                                   "page_size":12
     *                               },
     *                               "sort":"-name",
     *                               "type_id":2
     *                           }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                               "headers":{
     *                                   {
     *                                       "name": "Название организации",
     *                                       "phone": "Телефон"
     *                                   }
     *                               }
     *                               ,
     *                               "organizations":{
     *                               {
     *                                      "id": 3551,
     *                                       "name": "PIXAR STUDIO",
     *                                       "phone": "89162802800",
     *                                       "email": "test@test.ru",
     *                                       "address": "улица Ленина",
     *                                       "image": "https://s3-eu-west-1.amazonaws.com/static.f-keeper.gif",
     *                                       "type_id": 2,
     *                                       "type": "Поставщик",
     *                                       "rating": 2.9,
     *                                       "city": "Ханты-Мансийск",
     *                                       "administrative_area_level_1": "Ханты-Мансийский автономный округ",
     *                                       "country": "Россия",
     *                                       "about": "О компании"
     *                               }}
     *                               ,
     *                               "pagination":{
     *                                   "page":1,
     *                                   "total_page":17,
     *                                   "page_size":12
     *                               },
     *                               "sort":"-name"
     *                      }
     *          ),
     *     ),
     *     @SWG\Response(
     *         response = 400,
     *         description = "BadRequestHttpException"
     *     ),
     *     @SWG\Response(
     *         response = 401,
     *         description = "error"
     *     )
     * )
     */
    public function actionOrganizations()
    {
        $this->response = $this->container->get('MarketWebApi')->organizations($this->request);
    }
}