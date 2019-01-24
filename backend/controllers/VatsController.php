<?php

namespace backend\controllers;

use backend\models\VatsSearch;
use common\models\CountryVat;
use Yii;
use common\models\Role;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use common\components\AccessRule;

/**
 * GoodsController implements the CRUD actions for CountryVat model.
 */
class VatsController extends Controller
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
                        'actions' => ['index', 'update', 'create'],
                        'allow'   => true,
                        'roles'   => [
                            Role::ROLE_ADMIN,
//                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all CountryVat models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new VatsSearch();
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);

        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays general settings
     *
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = CountryVat::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.request.page_error', ['ru' => 'The requested page does not exist.']));
        }

        if (Yii::$app->request->post()) {
            $country = Yii::$app->request->post('CountryVat');
            $country['vats'] = str_replace(' ', '', $country['vats']);
            $country['vats'] = str_replace(',', '.', $country['vats']);
            $pattern = '/^[0-9]{1,2}[.]?[0-9]{0,2}([;]{1}[0-9]{1,2}[.]?[0-9]{0,2})*$/';
            if (!preg_match($pattern, $country['vats'])) {
                throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.vats.not.correct', ['ru' => 'Перечень ставок налогов введён некорректно!']));
            }
            $model->vats = $country['vats'];
            $model->updated_by_id = Yii::$app->user->identity->id;
            if (!$model->save()) throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.vats.not.save', ['ru' => 'Сохранить ставки налогов не удалось']));
            return $this->redirect(['/vats/index']);
        } else {
            return $this->render('/vats/update', [
                'model' => $model,
            ]);
        }
    }

    public function actionCreate()
    {
        $count = CountryVat::getCountNotVatCountries();
        $model = new CountryVat();
        if (!$model) {
            throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.request.page_error', ['ru' => 'The requested page does not exist.']));
        }

        if (Yii::$app->request->post()) {
            $country = Yii::$app->request->post('CountryVat');
            $country['vats'] = str_replace(' ', '', $country['vats']);
            $country['vats'] = str_replace(',', '.', $country['vats']);
            $pattern = '/^[0-9]{1,2}[.]?[0-9]{0,2}([;]{1}[0-9]{1,2}[.]?[0-9]{0,2})*$/';
            if (!preg_match($pattern, $country['vats'])) {
                throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.vats.not.correct', ['ru' => 'Перечень ставок налогов введён некорректно!']));
            }
            $model->uuid = $country['country'];
            $model->vats = $country['vats'];
            $model->created_by_id = Yii::$app->user->identity->id;
            if (!$model->save()) throw new NotFoundHttpException(Yii::t('error', 'backend.controllers.vats.not.save', ['ru' => 'Сохранить ставки налогов не удалось']));
            return $this->redirect(['/vats/index']);
        } else {
            return $this->render('/vats/create', [
                'model' => $model,
                'count' => $count
            ]);
        }
    }

}
