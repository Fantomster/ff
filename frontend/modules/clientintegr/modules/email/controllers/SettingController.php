<?php

namespace frontend\modules\clientintegr\modules\email\controllers;


use Aws\S3\Exception\AccessDeniedException;
use common\models\IntegrationSettingFromEmail;
use common\models\Organization;
use common\models\User;
use yii\web\Controller;

/**
 * @var $user User
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
        $models = IntegrationSettingFromEmail::find()->where(['organization_id' => $organization->id])->all();

        return $this->render('index', ['models' => $models]);
    }

    public function actionCreate()
    {
        $user = \Yii::$app->user->identity;
        $model = new IntegrationSettingFromEmail();

        if (\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();
            if (isset($post)) {
                $model->load($post);
                if ($model->validate()) {
                    $model->save();
                    $this->redirect(\yii\helpers\Url::to(['setting/edit', 'setting_id' => $model->id]));
                } else {
                    print_r($model);
                }
            }
        }

        return $this->render('edit', [
            'model' => $model,
            'user' => $user
        ]);
    }

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

        $model = IntegrationSettingFromEmail::find()->where(['id' => $id, 'organization_id' => $organization->id])->one();
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
                'user' => $user
            ]);
        } else {
            throw new AccessDeniedException('Not access this setting.');
        }
    }

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
            throw new AccessDeniedException('Not access this setting.');
        }
    }
}