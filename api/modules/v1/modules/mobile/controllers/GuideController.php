<?php

namespace api\modules\v1\modules\mobile\controllers;

use api\modules\v1\modules\mobile\resources\CatalogBaseGoods;
use api\modules\v1\modules\mobile\resources\GuideProduct;
use common\models\User;
use Yii;
use yii\data\SqlDataProvider;
use yii\db\Query;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use api\modules\v1\modules\mobile\resources\Guide;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GuideController extends ActiveController
{

    /**
     * @var string
     */
    public $modelClass = 'api\modules\v1\modules\mobile\resources\Guide';

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors = array_merge($behaviors, $this->module->controllerBehaviors);

        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'index'  => [
                'class'               => 'yii\rest\IndexAction',
                'modelClass'          => $this->modelClass,
                'prepareDataProvider' => [$this, 'prepareDataProvider']
            ],
            'create' => [
                'class'      => 'yii\rest\CreateAction',
                'modelClass' => 'common\models\guides\Guide',
            ],
            'update' => [
                'class'       => 'yii\rest\UpdateAction',
                'modelClass'  => 'common\models\guides\Guide',
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }

    /**
     * @param $id
     * @return Guide
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = Guide::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException;
        }

        return $model;
    }

    /**
     * @return SqlDataProvider
     * @throws \Throwable
     */
    public function prepareDataProvider()
    {
        $params = new Guide();
        $params->setAttributes(Yii::$app->request->queryParams);
        /** @var User $user */
        $user = Yii::$app->user->getIdentity();
        $org = $user->organization;

        $product = (new Query())
            ->select(['cbg.product'])
            ->from(['gp' => GuideProduct::tableName()])
            ->leftJoin(['cbg' => CatalogBaseGoods::tableName()], 'cbg.id = gp.cbg_id')
            ->innerJoin(['guide' => Guide::tableName()], 'gp.guide_id = guide.id')
            ->where([
                'cbg.status'  => 1,
                'cbg.deleted' => 0
            ])
            ->limit(1);

        $product1 = $product->createCommand()
            ->getRawSql();
        $product2 = $product->offset(1)
            ->createCommand()
            ->getRawSql();
        $product3 = $product->offset(2)
            ->createCommand()
            ->getRawSql();

        $count = (new Query())
            ->select([
                "count(id) as count",
                "guide_id",
                "cbg_id"
            ])
            ->from(['gp' => GuideProduct::tableName()])
            ->groupBy('guide_id')
            ->createCommand()
            ->getRawSql();

        $query = Guide::find()
            ->select([
                'guide.*',
                "({$product1}) AS " . 'product1',
                "({$product2}) AS " . 'product2',
                "({$product3}) AS " . 'product3',
            ])
            ->leftJoin("({$count}) AS gp", 'gp.guide_id = guide.id')
            ->andWhere('gp.count is not null')
            ->andFilterWhere([
                'client_id'  => $org->id,
                'id'         => $params->id,
                'type'       => $params->type,
                'name'       => $params->name,
                'deleted'    => $params->deleted,
                'created_at' => $params->created_at,
                'updated_at' => $params->updated_at,
            ]);

        return new SqlDataProvider([
            'sql'        => $query->createCommand()->getRawSql(),
            'pagination' => [
                'page'     => isset($params->page) ? ($params->page - 1) : 0,
                'pageSize' => isset($params->count) ? $params->count : null,
            ],
        ]);
    }

    /**
     * @param $id
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function actionDelete($id)
    {
        $guide = Guide::findOne(['id' => $id]);
        $this->checkAccess('delete', $guide);
        $guide->delete();
        Yii::$app->getResponse()->setStatusCode(204);
    }

    /**
     * Checks the privilege of the current user.
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string          $action the ID of the action to be executed
     * @param \yii\base\Model $model  the model to be accessed. If `null`, it means no specific model is being accessed.
     * @param array           $params additional parameters
     * @throws \yii\web\ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // check if the user can access $action and $model
        // throw ForbiddenHttpException if access should be denied
        if ($action === 'update' || $action === 'delete') {
            $user = Yii::$app->user->identity;

            if ($model->client_id !== $user->organization_id)
                throw new \yii\web\ForbiddenHttpException(sprintf('You can only %s articles that you\'ve created.', $action));
        }
    }
}
