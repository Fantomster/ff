<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercService;
use api\common\models\merc\MercVsd;
use frontend\modules\clientintegr\modules\merc\helpers\api\cerber\cerberApi;
use frontend\modules\clientintegr\modules\merc\helpers\MultiModel;
use frontend\modules\clientintegr\modules\merc\models\transportVsd\step1Form;
use frontend\modules\clientintegr\modules\merc\models\transportVsd\step2Form;
use frontend\modules\clientintegr\modules\merc\models\transportVsd\step3Form;
use frontend\modules\clientintegr\modules\merc\models\transportVsd\step4Form;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\web\Response;

class TransportVsdController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;

    public function beforeAction($action)
    {
        $lic = mercService::getLicense();

        if (!isset($lic) && ($this->getRoute() != 'clientintegr/merc/default/nolic')) {
            $this->redirect(['nolic']);
            return false;
        }

        if (!mercDicconst::checkSettings()) {
            $this->redirect(['/clientintegr/merc/settings']);
            return false;
        }

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function actionNolic()
    {
        return $this->render('/default/_nolic');
    }


    public function actionStep1()
    {
        $session = Yii::$app->session;
        if(Yii::$app->request->isGet) {
            $get = Yii::$app->request->get();
            if (isset($get['selected'])) {
                $selected = Yii::$app->request->get('selected');
                $session->remove('TrVsd_step1');
            }
            else
            {
                $selected = $session->get('TrVsd_step1');
                $attributes = $selected;
                $session->remove('TrVsd_step1');
                $selected = implode(",", array_keys($selected));
                $list = step1Form::find()->where("id in ($selected)")->all();
                foreach ($list as $key => $item)
                {
                    $list[$key]->attributes = $attributes[$item->id];
                }
            }
        }
        else {
            $post = Yii::$app->request->post('step1Form');
            $res = [];
            foreach ($post as $item)
            {
                $res[] = $item['id'];
            }
            $selected = implode(",", $res);
        }

        if(!isset($list))
            $list = step1Form::find()->where("id in ($selected)")->all();
        if (MultiModel::loadMultiple($list, Yii::$app->request->post()) && empty(ActiveForm::validateMultiple($list))) {
            $attributes = [];
            foreach ($list as $item)
            {
                $attributes[$item->id] = $item->getAttributes(['product_name','select_amount']);
            }
                $session->set('TrVsd_step1', $attributes);
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return (['success' => true]);
                }
                return $this->redirect(['step-2']);

        }

        if (Yii::$app->request->isAjax)
            return $this->renderAjax('step-1', ['list' => $list]);
        return $this->render('step-1', ['list' => $list]);
    }

    public function actionStep2()
    {
        $session = Yii::$app->session;
        $model = new step2Form();
        $model->attributes = $session->get('TrVsd_step2');
        $session->remove('TrVsd_step2');

        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            if ($model->validate()) {
                $session->set('TrVsd_step2', $model->attributes);
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return (['success' => true]);
                }
                return $this->redirect(['step-3']);
            }
        }
        return $this->render('step-2', ['model' => $model]);
    }

    public function actionStep3()
    {
        $session = Yii::$app->session;
        $model = new step3Form();
        $model->attributes = $session->get('TrVsd_step3');
        $session->remove('TrVsd_step3');

        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            if ($model->isTTN)
                $model->setScenario('isTTN');
            if ($model->validate()) {
                $session->set('TrVsd_step3', $model->attributes);
                if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return (['success' => true]);
                }
                return $this->redirect(['step-4']);
            }
        }
        return $this->render('step-3', ['model' => $model]);
    }

    public function actionStep4()
    {
        $session = Yii::$app->session;
        $model = new step4Form();
        $model->attributes = $session->get('TrVsd_step4');
        $session->remove('TrVsd_step4');

        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            if ($model->validate()) {
                $session->set('TrVsd_step4', $model->attributes);
                var_dump(1); exit();
                /*if (Yii::$app->request->isAjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return (['success' => true]);
                }*/
                //return $this->redirect(['step-3']);
            }
        }
        return $this->render('step-4', ['model' => $model]);
    }

    public function actionAutocomplete($type = 1) {
        if (Yii::$app->request->get('term')) {
            $term = Yii::$app->request->get('term');

            switch ($type){
                case 2 : $column = 'trailer_number'; break;
                case 3 : $column = 'container_number'; break;
                default : $column = 'vehicle_number';
            }

                $data = MercVsd::find()
                    ->select([$column.' as label', $column.' as id'])
                    ->where(['like', $column, $term])
                    ->asArray()
                    ->all();
            //Yii::$app->response->format = Response::FORMAT_JSON;
            //echo json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
            return json_encode($data);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    public function actionGetHc($recipient_guid)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        try {
            $hc = cerberApi::getInstance()->getEnterpriseByGuid($recipient_guid);
            $hc = cerberApi::getInstance()->getBusinessEntityByUuid($hc->enterprise->owner->uuid);
        }catch (\SoapFault $e)
        {
            return (['result' => false, 'name'=>'Не удалось загрузить Фирму-получателя']);
        }
        return (['result' => true, 'name' => $hc->businessEntity->name.', ИНН:'.$hc->businessEntity->inn, 'uuid' => $hc->businessEntity->uuid]);
    }

    private function getErrorText($e)
    {
        if ($e->getCode() == 600)
            return "При обращении к api Меркурий возникла ошибка. Ошибка зарегистрирована в журнале за номером №" . $e->getMessage() . ". Если ошибка повторяется обратитесь в техническую службу.";
        else
            return "При обращении к api Меркурий возникла ошибка. Если ошибка повторяется обратитесь в техническую службу.";
    }
}
