<?php

namespace frontend\controllers;


use Yii;
use common\components\AccessRule;
use yii\filters\AccessControl;
use common\models\Role;
use yii\data\ArrayDataProvider;
use common\models\AdditionalEmail;
use yii\web\HttpException;

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
                        'actions' => [
                            'notifications',
                            'ajax-add-email',
                            'ajax-delete-email',
                            'ajax-change-email-notification'
                        ],
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

        //Получаем список дополнительных емайлов
        $additional_email = new ArrayDataProvider([
            'allModels' => $this->currentUser->organization->additionalEmail,
        ]);

        return $this->render('notifications', compact('emailNotification', 'smsNotification', 'additional_email'));
    }

    /**
     * Удаление дополнительного Email адреса
     * @param $id
     * @return false|int
     * @throws HttpException
     */
    public function actionAjaxDeleteEmail($id)
    {
        try {
            if (!Yii::$app->request->isAjax) {
                throw new \Exception('Ajax only');
            }
            if ($model = AdditionalEmail::findOne($id)) {
                return $model->delete();
            } else {
                throw new \Exception('Model not found.');
            }
        } catch (\Exception $e) {
            throw new HttpException(418, $e->getMessage());
        }
    }

    /**
     * Добавляем дополнительный Емайл
     * @throws HttpException
     */
    public function actionAjaxAddEmail()
    {
        try {
            if (!Yii::$app->request->isAjax) {
                throw new \Exception('Ajax only');
            }
            if ($email = Yii::$app->request->post('email', null)) {
                $model = new AdditionalEmail();
                $model->email = $email;
                $model->organization_id = $this->currentUser->organization->id;
                if ($model->validate()) {
                    $model->save();
                } else {
                    throw new \Exception($model->getFirstErrors());
                }
            }
        } catch (\Exception $e) {
            throw new HttpException(418, $e->getMessage());
        }
    }

    /**
     * Меняем значения флагов у дополнительного емайла
     * @throws HttpException
     */
    public function actionAjaxChangeEmailNotification()
    {
        try {
            if (!Yii::$app->request->isAjax) {
                throw new \Exception('Ajax only');
            }
            if ($id = Yii::$app->request->post('id', null)) {
                $model = AdditionalEmail::findOne($id);
                $attribute = Yii::$app->request->post('attribute', null);
                $model->$attribute = Yii::$app->request->post('value', 0);
                if ($model->validate()) {
                    $model->save();
                } else {
                    throw new \Exception($model->getFirstErrors());
                }
            }
        } catch (\Exception $e) {
            throw new HttpException(418, $e->getMessage());
        }
    }
}
