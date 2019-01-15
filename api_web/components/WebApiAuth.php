<?php

namespace api_web\components;

use common\models\User;
use yii\filters\auth\AuthMethod;
use common\models\forms\LoginForm;
use yii\web\UnauthorizedHttpException;

/**
 * Class WebApiAuth
 *
 * @package api_web\components
 */
class WebApiAuth extends AuthMethod
{
    private $keyCache;

    /**
     * Авторизация по токену в BODY
     *
     * @param \yii\web\User     $user
     * @param \yii\web\Request  $request
     * @param \yii\web\Response $response
     * @return \amnah\yii2\user\models\User|null|\yii\web\IdentityInterface
     * @throws UnauthorizedHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function authenticate($user, $request, $response)
    {
        $identity = null;
        $params = $request->getBodyParams();

        if (isset($params['request']['email']) && isset($params['request']['password'])) {
            $this->keyCache = 'auth_' . md5($params['request']['email']);
            $attempt = \Yii::$app->cache->get($this->keyCache);
            if ($attempt >= 10) {
                throw new UnauthorizedHttpException('You enter the password too often, rest for 10 minutes.');
            }
        }

        if (!empty($params)) {
            if (isset($params['user']['token']) || isset($params['request']['email'])) {
                //Авторизация по токену
                if (isset($params['user']['token'])) {
                    //Для методов неподтвержденного поставщика используем JWT
                    try {
                        $jwtToken = \Yii::$app->jwt->getParser()->parse((string)$params['user']['token']);
                        $identity = User::getByJWTToken(\Yii::$app->jwt, $jwtToken);
                    } catch (\Exception $e) {
                        $this->handleFailure($e->getMessage());
                    }
                }
                //Авторизация по логину и паролю в параметрах запроса
                if (isset($params['request']['email']) && isset($params['request']['password'])) {
                    $model = new LoginForm([
                        'email'    => $params['request']['email'],
                        'password' => $params['request']['password']
                    ]);
                    $identity = ($model->validate()) ? $model->getUser() : null;
                }
                //Если авторизовали пользователя
                if ($identity !== null) {
                    $user->switchIdentity($identity);
                    $user->login($identity);
                    if (isset($params['user']['location'])) {
                        if (isset($params['user']['location']['city'])) {
                            \Yii::$app->session->set('city', trim($params['user']['location']['city']));
                        }
                        if (isset($params['user']['location']['region'])) {
                            \Yii::$app->session->set('region', trim($params['user']['location']['region']));
                        }
                        if (isset($params['user']['location']['country'])) {
                            \Yii::$app->session->set('country', trim($params['user']['location']['country']));
                        }
                    }
                    \Yii::$app->cache->delete($this->keyCache);
                    return $identity;
                } else {
                    $this->handleFailure($response);
                }
            }
        }
        return null;
    }

    /**
     * @param $response
     * @throws UnauthorizedHttpException
     */
    public function handleFailure($response)
    {
        $attempt = \Yii::$app->cache->get($this->keyCache) ?? 0;
        \Yii::$app->cache->set($this->keyCache, ($attempt + 1), 600);
        throw new UnauthorizedHttpException('auth_failed', 401);
    }
}
