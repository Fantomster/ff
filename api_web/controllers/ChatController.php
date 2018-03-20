<?php

namespace api_web\controllers;

use api_web\components\WebApiController;

/**
 * Class ChatController
 * @package api_web\controllers
 */
class ChatController extends WebApiController
{
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
     *              @SWG\Property(
     *                  property="request",
     *                  type="object",
     *                  default={
     *                      "search":{"recipient_id":1},
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
     *                      "count_message": 9,
     *                      "unread_message": 2,
     *                      "last_message":"Последнее сообщение",
     *                      "last_message_date": "2016-10-17 06:59:29"
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
     */
    public function actionDialogList()
    {
        $this->response = $this->container->get('ChatWebApi')->getDialogList($this->request);
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     */
    public function actionDialogMessages()
    {
        $this->response = $this->container->get('ChatWebApi')->getDialogMessages($this->request);
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     */
    public function actionRecipientList()
    {
        $this->response = $this->container->get('ChatWebApi')->getRecipientList($this->request);
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
     *              @SWG\Property(
     *                  property="user",
     *                  default= {"token":"111222333", "language":"RU"}
     *              ),
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
     */
    public function actionDialogAddMessage()
    {
        $this->response = $this->container->get('ChatWebApi')->addMessage($this->request);
    }
}