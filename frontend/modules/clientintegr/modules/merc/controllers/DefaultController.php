<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercService;
use api\common\models\merc\MercVisits;
use api\common\models\merc\MercVsd;
use api\common\models\merc\search\mercVSDSearch;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\mercuryApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentDone;
use frontend\modules\clientintegr\modules\merc\helpers\api\mercury\VetDocumentsChangeList;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentByUUIDRequest;
use frontend\modules\clientintegr\modules\merc\models\rejectedForm;
use Yii;

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController
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

        if(!mercDicconst::checkSettings()) {
            $this->redirect(['/clientintegr/merc/settings']);
            return false;
        }

        if (($action->actionMethod == 'actionIndex') || ($action->actionMethod == 'actionView'))
            $this->updateVSDList();
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function actionIndex()
    {
        Yii::$app->cache->flush();
        $lic = mercService::getLicense();
        $searchModel = new mercVSDSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $params = ['searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'lic' => $lic];

        if (Yii::$app->request->isPjax) {
            return $this->renderPartial('index', $params);
        } else {
            return $this->render('index', $params);
        }
    }

    public function actionNolic()
    {
        return $this->render('/default/_nolic');
    }

    public function actionView($uuid)
    {
        try {
            $document = new getVetDocumentByUUIDRequest();
            $document->getDocumentByUUID($uuid);
        }catch (\Error $e) {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        }
        catch (\Exception $e){
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        }
        $params = ['document' => $document];
        if (Yii::$app->request->isAjax) {
            return $this->renderAjax('_ajaxView', $params);
        } else {
            return $this->render('view', $params);
        }
    }

    public function actionDone($uuid)
    {
        try {
            $api = mercuryApi::getInstance();

            if(!$api->getVetDocumentDone($uuid))
                throw new \Exception('Done error');

            $cache = \Yii::$app->cache;
            $cache->delete('vetDocRaw_' . $uuid);
            $cache->delete('vetDoc_' . $uuid);

        } catch (\Error $e)
        {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        catch (\Exception $e){
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }

        Yii::$app->session->setFlash('success', 'ВСД успешно погашен!');
        $this->updateVSDList();
            if (Yii::$app->request->isAjax) {
                return true;
            } else {
                return $this->redirect(['view', 'uuid' => $uuid]);
            }
    }

    public function actionDonePartial($uuid, $reject = false)
    {
        $model = new rejectedForm();
        if($reject)
            $model->decision = VetDocumentDone::RETURN_ALL;
        else
            $model->decision = VetDocumentDone::PARTIALLY;

       try {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $api = mercuryApi::getInstance();

                if(!$api->getVetDocumentDone($uuid, $model->attributes))
                    throw new \Exception('Done error');

                $cache = \Yii::$app->cache;
                $cache->delete('vetDocRaw_' . $uuid);
                $cache->delete('vetDoc_' . $uuid);
                Yii::$app->session->setFlash('success', 'ВСД успешно обработан');
                $this->updateVSDList();

                if (Yii::$app->request->isAjax)
                    return true;
                return $this->redirect(['view', 'uuid' => $uuid]);
           }
        } catch (\Error $e)
        {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        catch (\Exception $e)
        {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }

        try {
            $document = new getVetDocumentByUUIDRequest();
            $document->getDocumentByUUID($uuid);
        }catch (\Error $e)
        {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        catch (\Exception $e)
        {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }

        if (Yii::$app->request->isAjax)
            return $this->renderAjax('rejected/_ajaxForm', [
                'model' => $model,
                'volume' => $document->batch[4]['value']
            ]);

        return $this->render('rejected/rejectedAct', [
            'model' => $model,
            'volume' => $document->batch[4]['value']
        ]);
    }

    public function actionDoneAll()
    {
        $selected = Yii::$app->request->get('selected');

        try {
            $selected = explode(',', $selected);
            $api = mercuryApi::getInstance();
            foreach ($selected as $id) {
                $uuid = MercVsd::findOne(['id' => $id])->uuid;
                if(!$api->getVetDocumentDone($uuid))
                    throw new \Exception('Done error');

                $cache = \Yii::$app->cache;
                $cache->delete('vetDocRaw_' . $uuid);
                $cache->delete('vetDoc_' . $uuid);
            }
        } catch (\Error $e)
        {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        }
        catch (\Exception $e)
        {
            Yii::$app->session->setFlash('error', $this->getErrorText($e));
            return $this->redirect(['index']);
        }

        Yii::$app->session->setFlash('success', 'ВСД успешно погашены!');
        $this->updateVSDList();
        return $this->redirect(['index']);
    }

    private function updateVSDList()
    {
        $visit = MercVisits::getLastVisit(Yii::$app->user->identity->organization_id);
        $transaction = Yii::$app->db_api->beginTransaction();
       try {
            $vsd = new VetDocumentsChangeList();
            if(isset($visit))
                $visit = gmdate("Y-m-d H:i:s",strtotime($visit) - 60*30);
            $vsd->updateData($visit);
            MercVisits::updateLastVisit(Yii::$app->user->identity->organization_id);
            $transaction->commit();
        }catch (\Exception $e)
        {
           $transaction->rollback();
        }
    }

    private function getErrorText($e)
    {
        if ($e->getCode() == 600)
            return "При обращении к api Меркурий возникла ошибка. Ошибка зарегистрирована в журнале за номером №".$e->getMessage().". Если ошибка повторяется обратитесь в техническую службу.";
        else
            return "При обращении к api Меркурий возникла ошибка. Если ошибка повторяется обратитесь в техническую службу.";
    }
}
