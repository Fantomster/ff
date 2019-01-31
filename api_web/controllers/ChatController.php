<?php

namespace api_web\controllers;

use api_web\classes\ChatWebApi;
use api_web\components\WebApiController;

/**
 * Class ChatController
 *
 * @property ChatWebApi $classWebApi
 * @package api_web\controllers
 */
class ChatController extends WebApiController
{

    public $className = ChatWebApi::class;

    /**
     * Список методов которые не нужно логировать
     *
     * @var array
     */
    public $not_log_actions = [
        'dialog-unread-count'
    ];

    /**
     * @SWG\Post(path="/chat/dialog-list",
     *     tags={"Chat"},
     *     summary="Список диалогов",
     *     description="Получить список всех дилогов по заказам",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         description="recipient_id = int or empty",
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                      "search":{
     *                          "recipient_id":1,
     *                          "order_id":14
     *                      },
     *                      "pagination":{
     *                          "page":1,
     *                          "page_size":12
     *                      }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *              "result":{
     *                 {
     *                      "dialog_id":1,
     *                      "client": "Космическая пятница",
     *                      "client_id": 1,
     *                      "vendor":"OOO Unity",
     *                      "vendor_id": 4,
     *                      "image": "http://mixcar.ru/image.jpg",
     *                      "count_message": 9,
     *                      "unread_message": 2,
     *                      "last_message":"Последнее сообщение",
     *                      "last_message_date": "2016-10-17 06:59:29",
     *                      "is_edi": true
     *                 }
     *              },
     *              "pagination":{
     *                  "page":1,
     *                  "page_size":12,
     *                  "total_page":3
     *              }
     *         }),
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
    public function actionDialogList()
    {
        $this->response = $this->classWebApi->getDialogList($this->request);
    }

    /**
     * @SWG\Post(path="/chat/dialog-messages",
     *     tags={"Chat"},
     *     summary="Список сообщений диалога",
     *     description="Получить список всех сообщений дилога",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                      "dialog_id":1,
     *                      "pagination":{
     *                          "page":1,
     *                          "page_size":12
     *                      }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *              "result":{
     *                 {
     *                      "message_id": 6328,
     *                      "message": "El postavshik подтвердил заказ!",
     *                      "sender": "MixCart Bot",
     *                      "recipient_name": "Космическая пятница",
     *                      "recipient_id": 1,
     *                      "is_my_message": false,
     *                      "is_system": true,
     *                      "viewed": true,
     *                      "date": "2018-02-12",
     *                      "time": "06:33:16"
     *                 }
     *              },
     *              "pagination":{
     *                  "page":1,
     *                  "page_size":12,
     *                  "total_page":3
     *              }
     *         }),
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
    public function actionDialogMessages()
    {
        $this->response = $this->classWebApi->getDialogMessages($this->request);
    }

    /**
     * @SWG\Post(path="/chat/recipient-list",
     *     tags={"Chat"},
     *     summary="Список получателей",
     *     description="Получить список всех получателей, с кем когда либо вели диалог.",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                      "search":{
     *                          "name":"часть имени получателя"
     *                      },
     *                      "pagination":{
     *                          "page":1,
     *                          "page_size":12
     *                      }
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *              "result":{
     *                 {
     *                      "recipient_id": 1,
     *                      "name": "El postavshik",
     *                 }
     *              },
     *              "pagination":{
     *                  "page":1,
     *                  "page_size":12,
     *                  "total_page":3
     *              }
     *         }),
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
    public function actionRecipientList()
    {
        $this->response = $this->classWebApi->getRecipientList($this->request);
    }

    /**
     * @SWG\Post(path="/chat/dialog-add-message",
     *     tags={"Chat"},
     *     summary="Добавить сообщение",
     *     description="Добавить сообщение в диалог",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                      "dialog_id":1,
     *                      "message": "Текст сообщения"
     *                  }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(
     *              default={
     *              "result":{
     *                 {
     *                      "message_id": 6328,
     *                      "message": "El postavshik подтвердил заказ!",
     *                      "sender": "MixCart Bot",
     *                      "recipient_name": "Космическая пятница",
     *                      "recipient_id": 1,
     *                      "is_my_message": false,
     *                      "is_system": true,
     *                      "viewed": true,
     *                      "date": "2018-02-12",
     *                      "time": "06:33:16"
     *                 }
     *              },
     *              "pagination":{
     *                  "page":1,
     *                  "page_size":12,
     *                  "total_page":3
     *              }
     *         }),
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
    public function actionDialogAddMessage()
    {
        $this->response = $this->classWebApi->addMessage($this->request);
    }

    /**
     * @SWG\Post(path="/chat/dialog-read",
     *     tags={"Chat"},
     *     summary="Отметить сообщения диалога прочитаными",
     *     description="Отметить сообщения диалога прочитаными",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={"dialog_id":1}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(default={"result":1}),
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
    public function actionDialogRead()
    {
        $this->response = $this->classWebApi->readMessages($this->request);
    }

    /**
     * @SWG\Post(path="/chat/dialog-read-all",
     *     tags={"Chat"},
     *     summary="Отметить все сообщения прочитаными",
     *     description="Отметить все сообщения прочитаными",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(default={"result":1}),
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
    public function actionDialogReadAll()
    {
        $this->response = $this->classWebApi->readAllMessages();
    }

    /**
     * @SWG\Post(path="/chat/dialog-unread-count",
     *     tags={"Chat"},
     *     summary="Количество диалогов с новыми сообщениями",
     *     description="Количество диалогов с новыми сообщениями",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(default={"result":1}),
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
    public function actionDialogUnreadCount()
    {
        $this->response = $this->classWebApi->dialogUnreadCount();
    }
}
