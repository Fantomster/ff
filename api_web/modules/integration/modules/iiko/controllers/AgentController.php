<?php

namespace api_web\modules\integration\modules\iiko\controllers;

use api_web\components\WebApiController;
use api_web\modules\integration\modules\iiko\models\iikoOrder;
use api_web\modules\integration\modules\iiko\models\iikoService;

class AgentController extends WebApiController
{

    /**
     * @SWG\Post(path="/integration/iiko/agent/list",
     *     tags={"OLD  Integration_iiko"},
     *     summary="Список контрагентов синхронизированных из внешней системы",
     *     description="Список контрагентов синхронизированных из внешней системы",
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
     *                  "pagination":{
     *                              "page": 1,
     *                              "page_size": 12
     *                          }
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                       "agents": {
     *                                   "id": 1,
     *                                   "uuid": "91e0dd93-0923-4509-9435-6cc6224768af",
     *                                   "org_id": 1024,
     *                                   "org_denom": "РАВШАН",
     *                                   "denom": "РАВШАН-IIKO",
     *                                   "store_denom": "РАВШАН-СКЛАД",
     *                                   "vendor_name": "ООО ”Рога и Копыта”",
     *                                   "is_active": 1,
     *                                   "comment": "Comment"
     *                                },
     *                      "pagination": {
     *                                      "page": 1,
     *                                      "total_page": 17,
     *                                      "page_size": 12
     *                                  }
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
     */
    public function actionList()
    {
        $this->response = $this->container->get('IikoWebApi')->getAgentsList($this->request);
    }


    /**
     * @SWG\Post(path="/integration/iiko/agent/update",
     *     tags={"OLD  Integration_iiko"},
     *     summary="Обновление данных для связи контрагента",
     *     description="Обновление данных для связи контрагента",
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
     *                              "agent_id": 1,
     *                              "vendor_id": 256,
     *                              "store_id": 1
     *                    }
     *              )
     *         )
     *     ),
     *    @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                "success": true,
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
     */
    public function actionUpdate()
    {
        $this->response = $this->container->get('IikoWebApi')->updateAgentData($this->request);
    }
}