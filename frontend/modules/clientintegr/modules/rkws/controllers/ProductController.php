<?php

namespace frontend\modules\clientintegr\modules\rkws\controllers;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */
class ProductController extends \frontend\modules\clientintegr\controllers\DefaultController
{

    public $enableCsrfValidation = false;

    protected $authenticated = false;

    private $sessionId = '';
    private $username;
    private $password;

    public function actionIndex()
    {

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index');
        } else {
            return $this->render('index');
        }

    }

    public function actionView($id)
    {
        return $this->render('view', [
            'dataProvider' => $this->findModel($id),
        ]);
    }

    public function actionGetws()
    {

        $res = new \frontend\modules\clientintegr\modules\rkws\components\ProductHelper();
        $res->getProduct();

        if ($res) {
            $this->redirect('/clientintegr/rkws/default');
        }

    }

    protected function findModel($id)
    {
        if (($dmodel = \api\common\models\RkDic::findOne($id)) !== null) {

            $model = \api\common\models\RkProduct::find()->andWhere('acc = :acc', [':acc' => $dmodel->org_id])->andWhere(['is_active' => 1]);

            $dataProvider = new ActiveDataProvider([
                'query' => $model,
                'sort'  => false]);

            return $dataProvider;
        } else {
            throw new NotFoundHttpException(Yii::t('error', 'requested.page.does.not.exist', ['ru' => 'Запрашиваемая страница не существует.']));
        }
    }

}
