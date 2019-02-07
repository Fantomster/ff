<?php

namespace frontend\modules\clientintegr\modules\email\controllers;

use common\models\IntegrationSettingFromEmail;
use common\models\Organization;
use common\models\User;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * @var $user         User
 * @var $organization Organization
 */
class SettingController extends Controller
{

    public function actionIndex()
    {
        /**
         * @var $user User
         */
        $user = \Yii::$app->user->identity;
        $organization = $user->organization;
        $models = IntegrationSettingFromEmail::find()->where(['organization_id' => $organization->id, 'version' => 1])->all();

        return $this->render('index', ['models' => $models]);
    }

    /**
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new IntegrationSettingFromEmail();

        if ($model->load(\Yii::$app->request->post()) && $model->validate()) {
            $model->save();

            return $this->redirect(['setting/index']);
        }

        return $this->render('edit', [
            'model' => $model,
            'user'  => \Yii::$app->user->identity
        ]);
    }

    /**
     * @return string
     * @throws ForbiddenHttpException
     */
    public function actionEdit()
    {
        $user = \Yii::$app->user->identity;
        $organization = $user->organization;

        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();
            $id = $post['IntegrationSettingFromEmail']['id'];
        } else {
            $id = \Yii::$app->request->get('setting_id');
        }

        $model = IntegrationSettingFromEmail::find()->where(['id' => $id, 'organization_id' => $organization->id, 'version' => 1])->one();
        if (isset($model) && isset($user)) {

            if (isset($post)) {
                $model->load($post);
                if ($model->validate()) {
                    $model->save();
                } else {
                    print_r($model);
                }
            }

            return $this->render('edit', [
                'model' => $model,
                'user'  => $user
            ]);
        } else {
            throw new ForbiddenHttpException('Not access this setting.');
        }
    }

    /**
     * @throws ForbiddenHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete()
    {
        $user = \Yii::$app->user->identity;
        $organization = $user->organization;
        $id = \Yii::$app->request->get('setting_id');

        $model = IntegrationSettingFromEmail::find()->where(['id' => $id, 'organization_id' => $organization->id])->one();
        if ($model) {
            $model->delete();
            $this->redirect('/clientintegr/email/setting');
        } else {
            throw new ForbiddenHttpException('Not access this setting.');
        }
    }
}
