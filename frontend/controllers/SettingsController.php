<?php

namespace frontend\controllers;

use Yii;
use common\components\AccessRule;
use yii\filters\AccessControl;

/**
 * Description of SettingsController
 *
 * @author sharaf
 */
class SettingsController extends DefaultController {
    
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
}
