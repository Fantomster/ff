<?php

namespace api_web\controllers;

use api_web\classes\CartWebApi;
use api_web\components\Registry;
use api_web\components\WebApiController;
use common\models\Order;
use yii\filters\AccessControl;

/**
 * Class CartController
 *
 * @property CartWebApi $classWebApi
 * @package api_web\controllers
 */
class CartController extends WebApiController
{
    public $className = CartWebApi::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $access['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'add',
                        'items',
                        'clear',
                        'product-comment',
                    ],
                    'roles'      => [Registry::PROCUREMENT_INITIATOR],
                    'roleParams' => ['user' => $this->user]
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'registration',
                    ],
                    'roles'      => [Registry::PURCHASER_RESTAURANT],
                    'roleParams' => ['user' => $this->user]
                ],
                [
                    'allow'      => true,
                    'actions'    => [
                        'check-recipient',
                        'product-comment',
                    ],
                    'roles'      => [Registry::OPERATOR],
                    'roleParams' => ['user' => $this->user]
                ],
            ],
        ];

        $behaviors = array_merge($behaviors, $access);

        return $behaviors;
    }

    /**
     * @SWG\Post(path="/cart/add",
     *     tags={"Cart"},
     *     summary="Добавить/Удалить товар в корзине",
     *     description="Добавляем или удаляем товар в корзине с помощью параметра quantity",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={{"product_id":1, "quantity":10}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/CartItems"),
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
     * @throws
     */
    public function actionAdd()
    {
        $this->response = $this->classWebApi->add($this->request);
    }

    /**
     * @SWG\Post(path="/cart/items",
     *     tags={"Cart"},
     *     summary="Список товаров в корзине",
     *     description="Получить список всех товаров в корзине",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/CartItems"),
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
     * @throws \Exception
     */
    public function actionItems()
    {
        $this->response = $this->classWebApi->items();
    }

    /**
     * @SWG\Post(path="/cart/clear",
     *     tags={"Cart"},
     *     summary="Полная очистка корзины",
     *     description="Полная очистка корзины",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description = "Если request пустой, удаляются все заказы",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default= {"vendor_id": 3803}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={}
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
     * @throws
     */
    public function actionClear()
    {
        $this->response = $this->classWebApi->clear($this->request);
    }

    /**
     * @SWG\Post(path="/cart/registration",
     *     tags={"Cart"},
     *     summary="Зарегистрировать заказ из корзины",
     *     description="Зарегистрировать заказ из корзины",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default= {{"id":1, "delivery_date":"d.m.Y", "comment":"comment order"}}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"success":1, "error":0}
     *          ),
     *     )
     * )
     * @throws
     */
    public function actionRegistration()
    {
        $this->response = $this->classWebApi->registration($this->request);
    }

    /**
     * @SWG\Post(path="/cart/check-recipient",
     *     tags={"Cart"},
     *     summary="проверка емейлов, подписанных на заказ",
     *     description="проверка емейлов, подписанных на заказ",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default= {"o":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={"success":1, "error":0}
     *          ),
     *     )
     * )
     */
    public function actionCheckRecipient()
    {
        $o = Order::findOne($this->request['o']);
        $_ = $o->getRecipientsList();
        var_dump($_['emails']);
        exit();
    }

    /**
     * @SWG\Post(path="/cart/product-comment",
     *     tags={"Cart"},
     *     summary="Добавить комментарий к позиции",
     *     description="Добавить комментарий к позиции",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default= {"product_id": 1, "comment":"New comment!"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={}
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
     * @throws \Exception
     */
    public function actionProductComment()
    {
        $this->response = $this->classWebApi->productComment($this->request);
    }
}
