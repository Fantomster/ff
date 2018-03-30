<?php

namespace api_web\components;

/**
 * Class WebApiController
 * @package api\modules\v1\modules\web\components
 */

use yii\web\UploadedFile;

/**
 * @SWG\Swagger(
 *     schemes={"https", "http"},
 *     @SWG\SecurityScheme(
 *         securityDefinition="Bearer",
 *         type="apiKey",
 *         name="Authorization",
 *         in="header",
 *         description="Bearer {token}"
 *     ),
 *     basePath="/"
 * )
 *
 *
 * @SWG\Info(
 *     title="MixCart API WEB - Документация",
 *     description = "Взаимодействие с сервисом MixCart",
 *     version="1.0",
 *     contact={
 *          "name": "MixCart",
 *          "email": "narzyaev@yandex.ru"
 *     }
 * )
 */
class WebApiController extends \yii\rest\Controller
{
    /**
     * @var \common\models\User $user
     */
    protected $user;
    /**
     * @var array $request
     */
    protected $request;
    /**
     * @var array $response
     */
    protected $response;
    /**
     * @var \yii\di\Container $container
     */
    public $container;

    /**
     * Получаем контейнер
     */
    public function init()
    {
        $this->container = (new WebApi())->container;
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $my['authenticator'] = [
            'class' => \api_web\components\MyCompositeAuth::className(),
            'no_auth' => \Yii::$app->params['allow_methods'],
            'authMethods' => [
                WebApiAuth::className(),
                \yii\filters\auth\HttpBearerAuth::className(),
                \yii\filters\auth\QueryParamAuth::className(),
                /*[
                    'class' => \yii\filters\auth\HttpBasicAuth::className(),
                    'auth' => function ($username, $password) {
                        $model = new \common\models\forms\LoginForm();
                        $model->email = $username;
                        $model->password = $password;
                        return ($model->validate()) ? $model->getUser() : null;
                    }
                ]*/
            ]
        ];

        $my['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::className(),
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON
            ]
        ];

        $behaviors = array_merge($behaviors, $my);
        return $behaviors;
    }

    /**
     * @param \yii\base\Action $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $headers = \Yii::$app->response->headers;
        $headers->add('Access-Control-Allow-Origin', '*');
        $headers->add('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $headers->add('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        if (\Yii::$app->request->isOptions) {
            \Yii::$app->response->statusCode = 200;
            \Yii::$app->response->content = ' ';
            \Yii::$app->response->send();
            \Yii::$app->end(200, \Yii::$app->response);
        }

        $this->enableCsrfValidation = false;
        $user = \Yii::$app->request->getBodyParam('user');

        if (isset($user['language'])) {
            \Yii::$app->language = mb_strtolower($user['language']);
        }

        if (isset($user['token']) && \Yii::$app->user->isGuest) {
            $identity = (new WebApiAuth())->authenticate(\Yii::$app->getUser(), \Yii::$app->request, \Yii::$app->response);
            if (!empty($identity)) {
                \Yii::$app->user->setIdentity($identity);
            }
        }

        if (parent::beforeAction($action)) {
            $this->user = $this->container->get('UserWebApi')->getUser();
            $this->request = \Yii::$app->request->getBodyParam('request');

            if (strstr(\Yii::$app->request->contentType, 'multipart/form-data') !== false) {
                $this->request = [
                    'post' => \Yii::$app->request->post()
                ];

                if (!empty($_FILES)) {
                    $this->request['files'] = $_FILES;
                }
            }

            if (isset($this->request)) {
                return true;
            } else {
                throw new \yii\web\BadRequestHttpException('Некорректный запрос отсутствует request');
            }
        }
        return false;
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return array|string
     */
    public function afterAction($action, $result)
    {
        parent::afterAction($action, $result);
        if (!empty($this->response)) {
            return \api_web\helpers\WebApiHelper::response($this->response);
        } else {
            return [];
        }
    }
}
