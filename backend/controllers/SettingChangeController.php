<?php

namespace backend\controllers;

use api_web\components\FireBase;
use api_web\exceptions\ValidationException;
use api_web\helpers\WebApiHelper;
use common\models\IntegrationSettingValue;
use common\models\Organization;
use Yii;
use common\models\IntegrationSettingChange;
use common\models\IntegrationSettingChangeSearch;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * SettingChangeController implements the CRUD actions for IntegrationSettingChange model.
 */
class SettingChangeController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all IntegrationSettingChange models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new IntegrationSettingChangeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionConfirm(int $id): Response
    {
        $settingChange = $this->findModelSettingChange($id);

        $transaction = Yii::$app->db_api->beginTransaction();
        try {
            $settingValue = IntegrationSettingValue::findOne([
                'org_id'     => $settingChange->org_id,
                'setting_id' => $settingChange->integration_setting_id
            ]);

            if (empty($settingValue)) {
                $settingValue = new IntegrationSettingValue();
            }

            $settingValue->setAttributes([
                'org_id'     => $settingChange->org_id,
                'setting_id' => $settingChange->integration_setting_id,
                'value'      => $settingChange->new_value,
            ]);

            $settingChange->setAttributes([
                'confirmed_user_id' => \Yii::$app->user->identity->getId(),
                'confirmed_at'      => gmdate("Y-m-d H:i:s"),
                'is_active'         => 0
            ]);
            $settingValue->save();
            $settingChange->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollback();
            throw new ForbiddenHttpException($e->getMessage());
        }
        $organization = Organization::findOne($settingChange->org_id);
        foreach ($organization->users as $user) {
            FireBase::getInstance()->update([
                'user'          => $user->id,
                'organization'  => $organization->id,
                'notifications' => uniqid(),
            ], [
                'body' => Yii::t('api_web', 'frontend.controllers.settings.change.updated', ['ru' => 'Настройки успешно обновлены!']),
                'date' => WebApiHelper::asDatetime(),
            ]);
        }

        return $this->redirect(['setting-change/index']);
    }

    /**
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     * @throws ValidationException
     */
    public function actionCancel(int $id): Response
    {
        $settingChange = $this->findModelSettingChange($id);
        $settingChange->setAttributes([
            'rejected_user_id' => \Yii::$app->user->identity->getId(),
            'rejected_at'      => gmdate("Y-m-d H:i:s"),
            'is_active'        => 0
        ]);

        if (!$settingChange->save()) {
            throw new ValidationException($settingChange->getFirstErrors());
        }

        return $this->redirect(['setting-change/index']);
    }

    /**
     * Finds the IntegrationSettingChange model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return IntegrationSettingChange the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModelSettingChange(int $id): IntegrationSettingChange
    {
        if (($model = IntegrationSettingChange::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
