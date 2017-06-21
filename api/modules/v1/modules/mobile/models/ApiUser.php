<?php
namespace api\modules\v1\modules\mobile\models;

use Yii;
use common\models\forms\LoginForm;

class ApiUser
{
     public static function validate($username, $password)
     {
        $model = new LoginForm();
        $model->email = $username;
        $model->password = $password;
        return $model->validate()
               ? $model->getUser()
               : null;
     }
}