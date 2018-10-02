<?php

namespace api_web\modules\integration\controllers;

class DocumentController extends \api_web\components\WebApiController
{
    /**
     * @SWG\Post(path="/integration/document/document-content",
     *     tags={"Integration"},
     *     summary="Детальная часть документа",
     *     description="Детальная часть документа",
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
     *                      "document_id": 2,
     *                      "type": "order"}
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response = 200,
     *         description = "success",
     *         @SWG\Schema(ref="#/definitions/IntegrationDocumentContent"),
     *     )
     * )
     */
    public function actionDocumentContent()
    {
        $this->response = $this->container->get('IntegrationWebApi')->list($this->request);
    }
}