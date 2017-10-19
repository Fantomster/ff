<?php

namespace frontend\controllers;

use Yii;
use common\components\AccessRule;
use yii\filters\AccessControl;
use common\models\Role;

/**
 * Description of SettingsController
 *
 * @author sharaf
 */
class SettingsController extends DefaultController {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['notifications'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function actionUser() {
        $profile = $this->currentUser->profile;
        return $this->render("user", compact('profile'));
    }

    public function actionAjaxChangeAvatar() {
        $profile = $this->currentUser->profile;

        $loadedPost = $profile->load(Yii::$app->request->post());

        if ($loadedPost && $profile->validate() && isset($profile->dirtyAttributes['avatar']) && $profile->avatar) {
            $profile->save();
            Yii::$app->session->setFlash('success', 'Аватар изменен!');
        }

        return $this->renderAjax('/settings/user/_change-avatar', compact('profile'));
    }

    public function actionAjaxDeleteAvatar() {
        $profile = $this->currentUser->profile;
        $profile->avatar = 'delete';
        if ($profile->save()) {
            return $profile->avatarUrl;
        }
    }

    public function actionNotifications() {
        $emailNotification = $this->currentUser->emailNotification;
        $smsNotification = $this->currentUser->smsNotification;
        if($emailNotification && $smsNotification){
            if ($emailNotification->load(Yii::$app->request->post()) && $smsNotification->load(Yii::$app->request->post())) {
                if ($emailNotification->validate() && $smsNotification->validate()) {
                    $emailNotification->save();
                    $smsNotification->save();
                }
            }
        }
        return $this->render('notifications', compact('emailNotification', 'smsNotification'));
    }

}
