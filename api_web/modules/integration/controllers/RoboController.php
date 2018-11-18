<?php

namespace api_web\modules\integration\controllers;

use api_web\components\Registry;

/**
 * Class RoboController
 * Робот парсер рассылок
 *
 * @package api_web\modules\integration\controllers
 */
class RoboController extends \api_web\components\WebApiController
{
    public $license_service_id = Registry::VENDOR_DOC_MAIL_SERVICE_ID;

    /**
     * @SWG\Post(path="/integration/robo/list",
     *     tags={"Integration/robo"},
     *     summary="Список email роботов",
     *     description="Список email роботов",
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
     *
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "result": {
     *                          "name": "капотник",
     *                          "user": "kapotnik2017@yandex.ru",
     *                          "is_active": 1,
     *                          "updated_at": "2018-10-25 12:39:36"
     *                  }
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
     * )
     * @throws \Exception
     */

    public function actionList()
    {
        $this->response = $this->container->get('EmailRoboWebApi')->list($this->request);
    }

    /**
     * @SWG\Post(path="/integration/robo/get",
     *     tags={"Integration/robo"},
     *     summary="Получение настроек конкретного робота",
     *     description="Получение настроек конкретного робота",
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
     *                      "id": 1
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  "result": {
     *                      "id": 2,
     *                      "organization_id": 3768,
     *                      "server_type": "imap",
     *                      "server_host": "imap.yandex.ru",
     *                      "server_port": 993,
     *                      "server_ssl": 1,
     *                      "user": "kapotnik2017@yandex.ru",
     *                      "password": "35478933547893q",
     *                      "is_active": 1,
     *                      "created_at": "2018-03-02 06:49:17",
     *                      "updated_at": "2018-04-24 08:20:29",
     *                      "language": "ru"
     *                  }
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
     * )
     * @throws \Exception
     */

    public function actionGet()
    {
        $this->response = $this->container->get('EmailRoboWebApi')->getSetting($this->request);
    }

    /**
     * @SWG\Post(path="/integration/robo/update",
     *     tags={"Integration/robo"},
     *     summary="Изменение настроек конкретного робота",
     *     description="Изменение настроек конкретного робота",
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
     *                      "id": 1,
     *                      "org_id": 1,
     *                      "server_type": "imap",
     *                      "server_host": "imap.yandex.ru",
     *                      "server_port": 993,
     *                      "server_ssl": 1,
     *                      "user": "kapotnik2017@yandex.ru",
     *                      "password": "35478933547893q",
     *                      "is_active": 1,
     *                      "language": "ru"
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                   "result": {
     *                      "id": 2,
     *                      "organization_id": 3768,
     *                      "server_type": "imap",
     *                      "server_host": "imap.yandex.ru",
     *                      "server_port": 993,
     *                      "server_ssl": 1,
     *                      "user": "kapotnik2017@yandex.ru",
     *                      "password": "35478933547893q",
     *                      "is_active": 1,
     *                      "created_at": "2018-03-02 06:49:17",
     *                      "updated_at": "2018-10-25 12:39:36",
     *                      "language": "ru"
     *                  }
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
     * )
     * @throws \Exception
     */
    public function actionUpdate()
    {
        $this->response = $this->container->get('EmailRoboWebApi')->update($this->request);
    }

    /**
     * @SWG\Post(path="/integration/robo/add",
     *     tags={"Integration/robo"},
     *     summary="Добавление робота",
     *     description="Добавление робота",
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
     *                      "org_id": 1,
     *                      "server_type": "imap",
     *                      "server_host": "imap.yandex.ru",
     *                      "server_port": 993,
     *                      "server_ssl": 1,
     *                      "user": "kapotnik2017@yandex.ru",
     *                      "password": "35478933547893q",
     *                      "is_active": 1,
     *                      "language": "ru"
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                   "result": {
     *                      "id": 1,
     *                      "organization_id": 3768,
     *                      "server_type": "imap",
     *                      "server_host": "imap.yandex.ru",
     *                      "server_port": 993,
     *                      "server_ssl": 1,
     *                      "user": "kapotnik2017@yandex.ru",
     *                      "password": "35478933547893q",
     *                      "is_active": 1,
     *                      "language": "ru"
     *                   }
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
     * )
     * @throws \Exception
     */
    public function actionAdd()
    {
        $this->response = $this->container->get('EmailRoboWebApi')->add($this->request);
    }

    /**
     * @SWG\Post(path="/integration/robo/delete",
     *     tags={"Integration/robo"},
     *     summary="Удаление робота",
     *     description="Удаление робота",
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
     *                      "id": 1
     *                  }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                   "result": true
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
     * )
     * @throws \Exception
     */
    public function actionDelete()
    {
        $this->response = $this->container->get('EmailRoboWebApi')->delete($this->request);
    }
}
