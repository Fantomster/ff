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
     *    {
     *          "headers":{
     *              {
     *                  "id": "ID",
     *                  "product": "Название",
     *                  "catalog_id": "Catalog Id",
     *                  "category_id ": "Category Id",
     *                  "price": "Цена",
     *                  "discount_price": "common.models.discount_price",
     *                  "rating": "Рейтинг",
     *                  "supplier": "Поставщик",
     *                  "supplier_id": "Supplier Id",
     *                  "brand": "Производитель",
     *                  "article": "Артикул",
     *                  "ed": "Единица измерения",
     *                  "units": "Кратность",
     *                  "currency": "Currency",
     *                  "image": "Картинка продукта",
     *                  "in_basket": "In Basket"
     *              }
     *          }
     *          ,
     *          "products":
     *          {
     *              {
     *                  "id": 110350,
     *                  "product": "Ячневая крупа ГЛОБАЛ ФУД 600гр/12шт",
     *                  "catalog_id": 706,
     *                  "category_id ": 129,
     *                  "price": 17.82,
     *                  "discount_price": 0,
     *                  "rating": 0.7,
     *                  "supplier": "ООО Глобал Фуд",
     *                  "supplier_id": 754,
     *                  "brand": "",
     *                  "article": "49",
     *                  "ed": "шт",
     *                  "units": 1,
     *                  "currency": "RUB",
     *                  "image": "https://mixcart.ru/fmarket/images/product_placeholder.jpg",
     *                  "in_basket": 0
     *              }
     *          }
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
     *              {
     *                  "Мясо": {
     *                           {
     *                              "id": 2,
     *                              "name": "Баранина",
     *                              "image": "http://web.mixcart.local/fmarket/images/product_placeholder.jpg"
     *                           }
     *                   }
     *              }
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
     *              default={
     *                "product":{
     *                    "id": 95940,
     *                    "product": "Ячменное фирменное св. фильтрованное паст. (20 шт.)",
     *                    "catalog_id": 652,
     *                    "category_id ": 56,
     *                    "price": 995.5,
     *                    "discount_price": 0,
     *                    "rating": 3.3,
     *                    "supplier": "ООО АН-ПРИНТ",
     *                    "supplier_id": 935,
     *                    "brand": "",
     *                    "article": "843",
     *                    "ed": "упаковка",
     *                    "units": 1,
     *                    "currency": "RUB",
     *                    "image": "https://mixcart.ru/fmarket/images/product_placeholder.jpg",
     *                    "in_basket": 0
     *                }
     *             }
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
     *                                       "id": "ID",
     *                                       "name": "Название организации",
     *                                       "phone": "Телефон",
     *                                       "email": "Email организации",
     *                                       "address": "Адрес",
     *                                       "image": "Image",
     *                                       "type_id": "Тип бизнеса",
     *                                       "type": "Type",
     *                                       "rating": "Rating",
     *                                       "city": "Город",
     *                                       "administrative_area_level_1": "Область",
     *                                       "country": "Страна",
     *                                       "about": "Информация об организации"
     *                                   }
     *                               }
     *                               ,
     *                               "organizations":{
     *                               {
     *                                      "id": 88,
     *                                      "name": "Фрутти Рум",
     *                                      "phone": "+7 926 844-31-82",
     *                                      "email": "fruttiroom@mail.ru",
     *                                      "address": "Новохохловская улица, 14, Москва, Москва, Россия",
     *                                      "image": "https://fkeeper.s3.amazonaws.com/org-picture/7a845f0eaf944721f11a53bc06640268.jpg",
     *                                      "type_id": 2,
     *                                      "type": "Поставщик",
     *                                      "rating": 5,
     *                                      "city": "Москва",
     *                                      "administrative_area_level_1": "Москва",
     *                                      "country": "Россия",
     *                                      "about": "Доставка овощей и фруктов, а также высококачественных продуктов, для кафе, баров, ресторанов, столовых, линий фудкортов, служб кейтеринговых услуг и др."
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