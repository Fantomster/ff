<?php

namespace api_web\modules\integration\controllers;

class DefaultController extends \api_web\components\WebApiController
{
    /**
     * @SWG\Post(path="/integration/default/service-list",
     *     tags={"Integration"},
     *     summary="Список сервисов интерграции",
     *     description="Список сервисов интерграции",
     *     produces={"application/json"},
     *     @SWG\Parameter(
     *         name="post",
     *         in="body",
     *         required=true,
     *         @SWG\Schema (
     *              @SWG\Property(property="user", ref="#/definitions/User"),
     *              @SWG\Property(
     *                  property="request",
     *                  default={}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/IntegrationServiceList"),
     *     )
     * )
     */
    public function actionServiceList()
    {
        $this->response = $this->container->get('IntegrationWebApi')->list($this->request);
    }


    /**
     * @SWG\Post(path="/integration/default/check-license-by-service",
     *     tags={"Integration"},
     *     summary="Метод проверки лицензии по организации и service_id",
     *     description="Метод проверки лицензии по организации и service_id",
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
     *                              "org_id": 5770,
     *                              "service_id": 8
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  {
     *                       "license_id": 1121,
     *                       "td": "2018-08-11 00:00:00",
     *                       "object_id": null,
     *                       "status_id": 1
     *                       },
     *                       {
     *                       "license_id": 1122,
     *                       "td": "2018-08-11 00:00:00",
     *                       "object_id": null,
     *                       "status_id": 1
     *                       },
     *                       {
     *                       "license_id": 1123,
     *                       "td": "2018-08-02 00:00:00",
     *                       "object_id": null,
     *                       "status_id": 1
     *                       }
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
    public function actionCheckLicenseByService()
    {
        $this->response = $this->container->get('IntegrationWebApi')->checkLicenseByService($this->request);
    }


    /**
     * @SWG\Post(path="/integration/default/check-license-by-license-id",
     *     tags={"Integration"},
     *     summary="Метод проверки лицензии по организации и license_id",
     *     description="Метод проверки лицензии по организации и license_id",
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
     *                              "org_id": 5770,
     *                              "license_id": 1121
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                  {
     *                       "license_id": 1121,
     *                       "td": "2018-08-11 00:00:00",
     *                       "object_id": null,
     *                       "status_id": 1
     *                       },
     *                       {
     *                       "license_id": 1122,
     *                       "td": "2018-08-11 00:00:00",
     *                       "object_id": null,
     *                       "status_id": 1
     *                       },
     *                       {
     *                       "license_id": 1123,
     *                       "td": "2018-08-02 00:00:00",
     *                       "object_id": null,
     *                       "status_id": 1
     *                       }
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
    public function actionCheckLicenseByLicenseId()
    {
        $this->response = $this->container->get('IntegrationWebApi')->checkLicenseByLicenseID($this->request);
    }


    /**
     * @SWG\Post(path="/integration/default/get-licenses-by-service-id",
     *     tags={"Integration"},
     *     summary="Метод получения лицензии по сервису",
     *     description="Метод получения лицензии по сервису",
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
     *                              "service_id": 8
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                      {
     *                           "id": "1107",
     *                           "name": "1C-ресторан",
     *                           "is_active": "1",
     *                           "created_at": "2018-10-04T12:07:42+00:00",
     *                           "updated_at": "2018-10-04T12:07:42+00:00",
     *                           "login_allowed": "1",
     *                           "licenseOrganizations": {
     *                           "id": "1001",
     *                           "license_id": "1107",
     *                           "org_id": "5405",
     *                           "fd": "2018-04-01 00:00:00",
     *                           "td": "2018-12-05 00:00:00",
     *                           "created_at": "2018-10-04T12:07:42+00:00",
     *                           "updated_at": "2018-10-04T12:07:42+00:00",
     *                           "object_id": null,
     *                           "outer_user": null,
     *                           "outer_name": null,
     *                           "outer_address": null,
     *                           "outer_phone": null,
     *                           "outer_last_active": null,
     *                           "status_id": "1",
     *                           "is_deleted": null
     *                      },
     *                           "licenseServices":
     *                        {
     *                           "id": "1093",
     *                           "license_id": "1107",
     *                           "service_id": "8",
     *                           "created_at": "2018-10-04T12:07:42+00:00",
     *                           "updated_at": "2018-10-04T12:07:42+00:00"
     *                        }
     *                     }
     *     }
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
    public function actionGetLicensesByServiceId()
    {
        $this->response = $this->container->get('IntegrationWebApi')->getLicensesByServiceId($this->request);
    }


    /**
     * @SWG\Post(path="/integration/default/get-licenses-by-license-id",
     *     tags={"Integration"},
     *     summary="Метод получения сервисов по лицензии",
     *     description="Метод получения сервисов по лицензии",
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
     *                              "license_id": 1107
     *                          }
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *            @SWG\Schema(
     *              default={
     *                       "service_id": "8"
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
    public function actionGetLicensesByLicenseId()
    {
        $this->response = $this->container->get('IntegrationWebApi')->getLicensesByLicenseId($this->request);
    }

}