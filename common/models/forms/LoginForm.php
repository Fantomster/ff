<?php

namespace common\models\forms;

use Yii;

/**
 * Description of LoginForm
 *
 * @author sharaf
 */
class LoginForm extends \amnah\yii2\user\models\forms\LoginForm {
    /**
     * Validate user
     */
    public function validateUser()
    {
        // check for valid user or if user registered using social auth
        $user = $this->getUser();
        if (!$user || !$user->password) {
            if ($this->module->loginEmail && $this->module->loginUsername) {
                $attribute = "Email / Username";
            } else {
                $attribute = $this->module->loginEmail ? "Email" : "Username";
            }
            $this->addError("email", Yii::t("user", "$attribute not found"));

            // do we need to check $user->userAuths ???
        }

        // check if user is banned
        if ($user && $user->banned_at) {
            $this->addError("email", Yii::t("user", "User is banned - {banReason}", [
                "banReason" => $user->banned_reason,
            ]));
        }

        // check status and resend email if inactive
        if ($user && $user->status == $user::STATUS_INACTIVE) {
            /** @var \amnah\yii2\user\models\UserToken $userToken */
//            $userToken = $this->module->model("UserToken");
//            $userToken = $userToken::generate($user->id, $userToken::TYPE_EMAIL_ACTIVATE);
//            $user->sendEmailConfirmation($userToken);
//            $this->addError("email", Yii::t("user", "Confirmation email resent"));
            $this->addError("email", 'Учетная запись не активирована!');
        }
    }
}
