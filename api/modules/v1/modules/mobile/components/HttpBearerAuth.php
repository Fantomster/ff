<?php
namespace api\modules\v1\modules\mobile\components;

/**
 * HttpBearerAuth is an action filter that supports the authentication method based on HTTP Bearer token.
 *
 * You may use HttpBearerAuth by attaching it as a behavior to a controller or module, like the following:
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'bearerAuth' => [
 *             'class' => \yii\filters\auth\HttpBearerAuth::className(),
 *         ],
 *     ];
 * }
 * ```
 */
use Yii;
use yii\filters\auth\HttpBearerAuth as BaseHttpBearerAuth;
use common\models\User;

class HttpBearerAuth extends BaseHttpBearerAuth {

    /**
     * @inheritdoc
     */
    public function authenticate($user, $request, $response) {
        $authHeader = $request->getHeaders()->get('Authorization');
        if ($authHeader !== null && preg_match('/^Bearer\s+(.*?)$/', $authHeader, $matches)) {
            $identity = User::findIdentityByAccessToken($matches[1]);
            if ($identity === null) {
                $this->handleFailure($response);
            }
            Yii::$app->user->login($identity, 0);
            return $identity;
        }

        return null;
    }

}
