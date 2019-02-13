<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 04.10.2018
 * Time: 11:56
 */

namespace api_web\controllers;

use api_web\classes\IntegrationWebApi;
use api_web\components\Registry;
use api_web\components\WebApiController;
use api_web\helpers\WaybillHelper;
use api_web\helpers\WebApiHelper;
use yii\filters\AccessControl;

/**
 * Class WaybillController
 *
 * @property IntegrationWebApi $classWebApi
 * @package api_web\controllers
 */
class WaybillController extends WebApiController
{
    public $className = IntegrationWebApi::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $access['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow'      => true,
                    'actions'    => [
                        'create-waybill',
                        'update-waybill-content',
                        'regenerate-by-order',
                        'move-order-content-to-waybill',
                        'delete-waybill',
                        'reset-waybill-content',
                        'show-waybill-content',
                        'create-waybill-content',
                        'delete-waybill-content',
                        'create-and-send-waybill-async',
                    ],
                    'roles'      => [
                        Registry::MANAGER_RESTAURANT,
                        Registry::BOOKER_RESTAURANT,
                    ],
                    'roleParams' => ['user' => $this->user]
                ],
            ],
        ];

        $behaviors = array_merge($behaviors, $access);

        return $behaviors;
    }

    /**
     * @SWG\Post(path="/waybill/regenerate-by-order",
     *     tags={"Waybill"},
     *     summary="Создание накладной по заказу",
     *     description="Создание накладной по заказу",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "order_id": 3674
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      "result": true
     *                  }
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
    public function actionRegenerateByOrder()
    {
        $this->response = (new WaybillHelper())->createWaybillForApi($this->request);
    }

    /**
     * @SWG\Post(path="/waybill/move-order-content-to-waybill",
     *     tags={"Waybill"},
     *     summary="Привязка order content к waybill content",
     *     description="Привязка order content к waybill content",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                      "service_id": 1,
     *                      "waybill_id": 5,
     *                      "order_content_id": 123
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *                      "result": true
     *                  }
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
     * @throws \Throwable
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionMoveOrderContentToWaybill()
    {
        $this->response = (new WaybillHelper())->moveOrderContentToWaybill($this->request);
    }

    /**
     * @SWG\Post(path="/waybill/create-waybill",
     *     tags={"Waybill"},
     *     summary="Создание накладной к заказу или в конкретном сервисе у.с",
     *     description="Создание накладной к заказу или в конкретном сервисе у.с",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "order_id": 1,
     *                              "service_id": 1
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true,
     *                "waybill_id": 1,
     *              }
     *          )
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
    public function actionCreateWaybill()
    {
        $this->response = $this->classWebApi->handleWaybill($this->request);
    }

    /**
     * @SWG\Post(path="/waybill/delete-waybill",
     *     tags={"Waybill"},
     *     summary="Накладная - Удалить",
     *     description="Накладная - Удалить",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "waybill_id": 5,
     *                              "service_id":1
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true
     *              }
     *          )
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
    public function actionDeleteWaybill()
    {
        $this->response = $this->classWebApi->deleteWaybill($this->request);
    }

    /**
     * @SWG\Post(path="/waybill/reset-waybill-content",
     *     tags={"Waybill"},
     *     summary="Сброс данных позиции, на значения из заказа",
     *     description="Сброс данных позиции, на значения из заказа",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "waybill_content_id": 14822
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true
     *              }
     *          )
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
    public function actionResetWaybillContent()
    {
        $this->response = $this->classWebApi->resetWaybillContent($this->request);
    }

    /**
     * @SWG\Post(path="/waybill/show-waybill-content",
     *     tags={"Waybill"},
     *     summary="Позиция накладной - Детальная информация",
     *     description="Позиция накладной - Детальная информация",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "waybill_content_id": 14822
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                          "id": 1,
     *                           "waybill_id": 11,
     *                           "order_content_id": 14822,
     *                           "outer_product_id": 4822,
     *                           "quantity_waybill": 1,
     *                           "vat_waybill": 0,
     *                           "merc_uuid": "745663-6454-4657-234775",
     *                           "unload_status": 1,
     *                           "sum_with_vat": 333299999,
     *                           "sum_without_vat": 333299999,
     *                           "price_with_vat": 333299999,
     *                           "price_without_vat": 333299999,
     *                           "koef": 1,
     *                           "serviceproduct_id": 777,
     *                           "store_rid": 111,
     *                           "outer_product_name": "Редиска",
     *                           "outer_product_id": 555,
     *                           "product_id_equality": true,
     *                           "outer_store_name": "Склад 1",
     *                           "outer_store_id": 222,
     *                           "store_id_equality": true,
     *                           "outer_unit_name": "кг",
     *                           "outer_unit_id": 333
     *                       }
     *          )
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
    public function actionShowWaybillContent()
    {
        $this->response = $this->classWebApi->showWaybillContent($this->request);
    }

    /**
     * @SWG\Post(path="/waybill/update-waybill-content",
     *     tags={"Waybill"},
     *     summary="Накладные - Обновление детальной информации позиции накладной",
     *     description="Накладные - Обновление детальной информации позиции накладной",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "waybill_content_id": 5,
     *                              "koef": 1.55,
     *                              "quantity_waybill": 1,
     *                              "outer_product_id": 4822,
     *                              "price_without_vat": 35000,
     *                              "vat_waybill": 18,
     *                              "outer_unit_id": 5
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true,
     *                "koef": 0.2,
     *                "quantity": 1
     *              }
     *          )
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
    public function actionUpdateWaybillContent()
    {
        $this->response = $this->classWebApi->updateWaybillContent($this->request);
    }

    /**
     * @SWG\Post(path="/waybill/create-waybill-content",
     *     tags={"Waybill"},
     *     summary="Накладная (привязана к заказу) - Добавление позиции",
     *     description="Накладная (привязана к заказу) - Добавление позиции",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "waybill_id": 5,
     *                              "koef": 1.55,
     *                              "outer_product_id": 4352,
     *                              "quantity_waybill": 1,
     *                              "price_without_vat": 35000,
     *                              "vat_waybill": 18
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true,
     *                "waybill_content_id": 5
     *              }
     *          )
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
    public function actionCreateWaybillContent()
    {
        $this->response = $this->classWebApi->createWaybillContent($this->request);
    }

    /**
     * @SWG\Post(path="/waybill/delete-waybill-content",
     *     tags={"Waybill"},
     *     summary="Накладная - Удалить/Убрать позицию",
     *     description="Накладная - Удалить/Убрать позицию",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={
     *                              "waybill_content_id": 5
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true
     *              }
     *          )
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
    public function actionDeleteWaybillContent()
    {
        $this->response = $this->classWebApi->deleteWaybillContent($this->request);
    }

    /**
     * Асинхронный метод создания и отправки накладных
     *
     * @throws \api_web\exceptions\ValidationException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreateAndSendWaybillAsync()
    {
        WebApiHelper::setAsyncResponseHeader();
        $this->request['action_id'] = $this->action->id;
        (new WaybillHelper())->sendWaybillAsync($this->request);
    }
}
