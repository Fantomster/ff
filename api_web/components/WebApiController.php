<?php

namespace api_web\components;

/**
 * Class WebApiController
 *
 * @package api\modules\v1\modules\web\components
 */

use api_web\classes\UserWebApi;
use api_web\helpers\Logger;
use common\models\licenses\License;
use common\models\Organization;
use yii\web\HttpException;
use \api_web\components\MyCompositeAuth;

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
     * @var array
     */
    public $not_log_actions = [];

    /**
     * @var integer id Service
     */
    public $license_service_id = 0;

    /**
     * Description
     *
     * @var bool
     */
    public $enableCsrfValidation = false;

    /**
     * Класс экземпляр которого поместим в $this->classWebApi
     *  Например ChatWebApi::class
     */
    public $className = null;

    /**
     * Экземпляр класса из $this->className
     */
    protected $classWebApi;

    /**
     * @throws HttpException
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\web\UnauthorizedHttpException
     */
    public function init()
    {
        $this->addHeaders();
        $this->checkOptionsHeader();
        $this->authUser();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $my['authenticator'] = [
            'class'       => MyCompositeAuth::class,
            'no_auth'     => \Yii::$app->params['allow_methods'],
            'authMethods' => [
                WebApiAuth::class,
                \yii\filters\auth\HttpBearerAuth::class,
                \yii\filters\auth\QueryParamAuth::class,
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
            'class'   => \yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON
            ]
        ];

        $behaviors = array_merge($behaviors, $my);
        return $behaviors;
    }

    /**
     * @param $action
     * @return bool
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        if (isset($this->className) && !is_null($this->className)) {
            $this->classWebApi = new $this->className();
        }

        if (parent::beforeAction($action)) {
            $this->authUser();
            if (strstr(\Yii::$app->request->contentType, 'multipart/form-data') !== false) {
                $this->request = [
                    'post' => \Yii::$app->request->post()
                ];
                if (!empty($_FILES)) {
                    $this->request['files'] = $_FILES;
                }
            }

            if (!in_array($action->id, $this->not_log_actions)) {
                try {
                    Logger::getInstance()::setUser($this->user);
                    Logger::getInstance()::request($this->request);
                } catch (\Exception $e) {
                }
            }

            if (isset($this->request)) {
                //Глобально ограничиваем page_size
                if (isset($this->request['pagination'])) {
                    if (isset($this->request['pagination']['page_size'])) {
                        if ($this->request['pagination']['page_size'] > 200) {
                            throw new \yii\web\BadRequestHttpException('param_value_to_large|page_size|200');
                        }
                    }
                }
                return true;
            } else {
                throw new \yii\web\BadRequestHttpException('Bad request, data request is empty');
            }
        }
        return false;
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed            $result
     * @return array|string
     * @throws \Exception
     */
    public function afterAction($action, $result)
    {
        parent::afterAction($action, $result);
        $this->checkLicense();

        if (!empty($this->response)) {
            if (!in_array($action->id, $this->not_log_actions)) {
                Logger::getInstance()::response($this->response);
            }
            $headers = \Yii::$app->response->headers;
            $headers->add('Backend-Time-Generation', round(\Yii::getLogger()->getElapsedTime(), 5));
            return $this->response;
        } else {
            return [];
        }
    }

    /**
     * Добавление заголовкой CORS
     */
    private function addHeaders()
    {
        $headers = \Yii::$app->response->headers;
        $headers->add('Access-Control-Allow-Origin', '*');
        $headers->add('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $headers->add('Access-Control-Allow-Headers', 'Content-Type, Authorization, GMT');
        $headers->add('Access-Control-Expose-Headers', 'License-Expire, License-Manager-Phone');
    }

    /**
     * @throws \yii\base\ExitException
     */
    private function checkOptionsHeader()
    {
        if (\Yii::$app->request->isOptions) {
            \Yii::$app->response->headers->add('Access-Control-Max-Age', 86400);
            \Yii::$app->response->statusCode = 200;
            \Yii::$app->response->content = ' ';
            \Yii::$app->response->send();
            \Yii::$app->end(200, \Yii::$app->response);
        }
    }

    /**
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\web\UnauthorizedHttpException
     */
    private function authUser()
    {
        if (!empty($this->user)) {
            return;
        }

        $user = \Yii::$app->request->getBodyParam('user');
        /**
         * Язык системы
         */
        \Yii::$app->language = \Yii::$app->language ?? 'ru';
        if (isset($user['language'])) {
            \Yii::$app->language = mb_strtolower($user['language']);
        }
        /**
         * Авторизуемся
         */
        if (isset($user['token']) && \Yii::$app->user->isGuest) {
            $identity = (new WebApiAuth())->authenticate(\Yii::$app->getUser(), \Yii::$app->request, \Yii::$app->response);
            if (!empty($identity)) {
                \Yii::$app->user->setIdentity($identity);
            }
        }

        $userWebApi = new UserWebApi();
        $this->user = $userWebApi->getUser();
        /**
         * Проверка лицензии только если это пользователь
         **/
        $this->checkLicense();

        $this->request = \Yii::$app->request->getBodyParam('request');
        \Yii::$app->setTimeZone('Etc/GMT' . $userWebApi->checkGMTFromDb());
    }

    /**
     * @throws HttpException
     * @throws \yii\base\InvalidConfigException
     */
    private function checkLicense()
    {
        if (!empty($this->user)) {
            //Проверяем лицензию только для ресторанов
            if ($this->user->organization->type_id == Organization::TYPE_RESTAURANT) {
                //Методы к которым пускаем без лицензии
                $allow_methods_without_license = \Yii::$app->params['allow_methods_without_license'] ?? [];
                //Если метода нет в разрешенных, проверяем лицензию
                if (!in_array(\Yii::$app->request->getUrl(), $allow_methods_without_license)) {
                    License::checkEnterLicenseResponse($this->user->organization_id);
                    if (isset($this->license_service_id) && !is_null($this->license_service_id)) {
                        License::checkLicense($this->user->organization_id, $this->license_service_id);
                    }
                }
            }
        }
    }

    /**
     * Устанавливаем сервисы лицензии которых необходимо проверить
     *
     * @param null $service_id
     */
    public function setLicenseServiceId($service_id = null)
    {
        if ($this->user) {
            if (is_null($service_id)) {
                $service_id = $this->user->integration_service_id;
            }
            $this->license_service_id = $service_id;
        } else {
            $this->license_service_id = null;
        }
    }
}
