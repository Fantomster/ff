<?php

namespace frontend\controllers;

use common\components\edi\EDIIntegration;
use common\components\EComIntegration;
use common\models\EcomIntegrationConfig;
use common\models\edi\EdiOrganization;
use common\models\notifications\EmailNotification;
use common\models\notifications\SmsNotification;
use common\models\Organization;
use console\controllers\CronController;
use Yii;
use common\components\AccessRule;
use yii\base\ErrorException;
use yii\base\ExitException;
use yii\db\Exception;
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
class SettingsController extends DefaultController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => [
                            'notifications',
                            'ajax-add-email',
                            'ajax-delete-email',
                            'ajax-change-email-notification',
//                            'test'
                        ],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_RESTAURANT_MANAGER,
                            Role::ROLE_ONE_S_INTEGRATION,
                            Role::ROLE_RESTAURANT_EMPLOYEE,
                            Role::ROLE_SUPPLIER_MANAGER,
                            Role::ROLE_SUPPLIER_EMPLOYEE,
                            Role::ROLE_FKEEPER_MANAGER,
                            Role::ROLE_ADMIN,
                            Role::getFranchiseeEditorRoles(),
                        ],
                    ],
                ],
            ],
        ];
    }

    //метод для теста - симуляция запуска крон
//    public function actionTest()
//    {
//        $cron = new CronController(1, 1);
//        $cron->actionHandleFiles();
//        $cron->actionHandleFilesQueue();
//        echo 'success';
//    }

    public function actionUser()
    {
        $profile = $this->currentUser->profile;
        return $this->render("user", compact('profile'));
    }

    public function actionAjaxChangeAvatar()
    {
        $profile = $this->currentUser->profile;

        $loadedPost = $profile->load(Yii::$app->request->post());

        if ($loadedPost && $profile->validate() && isset($profile->dirtyAttributes['avatar']) && $profile->avatar) {
            $profile->save();
            Yii::$app->session->setFlash('success', Yii::t('message', 'frontend.controllers.settings.avatar', ['ru' => 'Аватар изменен!']));
        }

        return $this->renderAjax('/settings/user/_change-avatar', compact('profile'));
    }

    public function actionAjaxDeleteAvatar()
    {
        $profile = $this->currentUser->profile;
        $profile->avatar = 'delete';
        if ($profile->save()) {
            return $profile->avatarUrl;
        }
    }

    public function actionNotifications()
    {
        $emailNotification = $this->currentUser->emailNotification;
        $smsNotification = $this->currentUser->smsNotification;

        $user = $this->currentUser;

        /* $emailNotification = ($emailNotification->id == null ) ? new EmailNotification() : $emailNotification;
          $smsNotification = ($smsNotification->id == null ) ? new SmsNotification() : $smsNotification;

          if($emailNotification && $smsNotification){
          if($emailNotification->isNewRecord)
          $emailNotification->loadDefaultValues();
          if($smsNotification->isNewRecord)
          $smsNotification->loadDefaultValues(); */
        if ($emailNotification->load(Yii::$app->request->post()) && $smsNotification->load(Yii::$app->request->post()) && $user->load(Yii::$app->request->post())) {

            $emailNotification->rel_user_org_id = $this->currentUser->relationUserOrganization->id;
            $smsNotification->rel_user_org_id = $this->currentUser->relationUserOrganization->id;
            $emailNotification->user_id = $this->currentUser->id;
            $smsNotification->user_id = $this->currentUser->id;
            if ($emailNotification->validate() && $smsNotification->validate() && $user->validate()) {
                $emailNotification->save();
                $smsNotification->save();
                $user->save();
            }
//                else
//                    {var_dump($emailNotification->errors);}
        }
        //}
        //Получаем список дополнительных емайлов
        $additional_email = new ArrayDataProvider([
            'allModels' => $this->currentUser->organization->additionalEmail,
        ]);

        return $this->render('notifications', compact('user', 'emailNotification', 'smsNotification', 'additional_email'));
    }

    /**
     * Удаление дополнительного Email адреса
     *
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
                $model->delete();
            } else {
                throw new \Exception('Model not found.');
            }
        } catch (\Exception $e) {
            throw new HttpException(418, $e->getMessage());
        }
    }

    /**
     * Добавляем дополнительный Емайл
     *
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
                    $model->sendConfirmationEmail();
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
     *
     * @throws HttpException
     */
    public function actionAjaxChangeEmailNotification()
    {
        try {
            if (!Yii::$app->request->isAjax) {
                throw new \Exception('Ajax only');
            }
            if ($id = Yii::$app->request->post('id', null)) {
                $model = AdditionalEmail::findOne(['id' => $id, 'confirmed' => true]);
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
