<?php

namespace api\modules\v1\modules\mobile\components;

use Yii;
use amnah\yii2\user\components\User as BaseUser;

/**
 * User component
 */
class User extends BaseUser
{
    /**
     * @inheritdoc
     */
    public function getIsGuest()
    {
        /** @var \amnah\yii2\user\models\User $user */

        // check if user is banned. if so, log user out and redirect home
        // https://github.com/amnah/yii2-user/issues/99
        $user = $this->getIdentity();
        if ($user && $user->banned_at) {
            $this->logout();
            throw new \yii\web\HttpException(401);
        }

        return $user === null;
    }

}    
