<?php

namespace frontend\modules\clientintegr\modules\merc\controllers;

use api\common\models\merc\mercDicconst;
use api\common\models\merc\mercService;
use frontend\modules\clientintegr\modules\merc\helpers\mercApi;
use frontend\modules\clientintegr\modules\merc\helpers\vetDocumentDone;
use frontend\modules\clientintegr\modules\merc\helpers\vetDocumentsList;
use frontend\modules\clientintegr\modules\merc\models\getVetDocumentByUUIDRequest;
use frontend\modules\clientintegr\modules\merc\models\rejectedForm;
use yii\helpers\Url;
use Yii;

class DefaultController extends \frontend\modules\clientintegr\controllers\DefaultController
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;

    public function beforeAction($action)
    {
        $license = mercService::getLicense();

        if (!isset($license) && ($this->getRoute() != 'clientintegr/merc/default/nolic')) {
            $this->redirect(['nolic']);
            return false;
        }
        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function actionIndex()
    {
        if(!mercDicconst::checkSettings())
            return $this->redirect(['/clientintegr/merc/settings']);

        $license = mercService::getLicense();
        $searchModel  = new vetDocumentsList();
        $searchModel->load(Yii::$app->request->get());
        $dataProvider = $searchModel->getArrayDataProvider();
        $params = ['searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'license' => $license];
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
            Yii::$app->session->setFlash('success', 'Ошибка загрузки ВСД, возможно сервер ВЕТИС "Меркурий"  перегружен, попробуйте повторить запрос чуть позже<br>
                  <small>Если ошибка повторяется, пожалуйста, сообщите нам
                  <a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
            return $this->redirect(['index']);
        }
        catch (\Exception $e){
            Yii::$app->session->setFlash('success', 'Ошибка загрузки ВСД, возможно сервер ВЕТИС "Меркурий"  перегружен, попробуйте повторить запрос чуть позже<br>
                  <small>Если ошибка повторяется, пожалуйста, сообщите нам
                  <a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
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
            $api = mercApi::getInstance();
            $api->getVetDocumentDone($uuid);

            $cache = \Yii::$app->cache;
            $cache->delete('vetDocRaw_' . $uuid);
            $cache->delete('vetDoc_' . $uuid);

        } catch (\Error $e)
        {
            Yii::$app->session->setFlash('success', 'Ошибка обработки ВСД, возможно сервер ВЕТИС "Меркурий"  перегружен, попробуйте повторить запрос чуть позже<br>
                  <small>Если ошибка повторяется, пожалуйста, сообщите нам
                  <a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        catch (\Exception $e){
            Yii::$app->session->setFlash('success', 'Ошибка обработки ВСД, возможно сервер ВЕТИС "Меркурий"  перегружен, попробуйте повторить запрос чуть позже<br>
                  <small>Если ошибка повторяется, пожалуйста, сообщите нам
                  <a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }

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
            $model->decision = vetDocumentDone::RETURN_ALL;
        else
            $model->decision = vetDocumentDone::PARTIALLY;

        try {
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                $api = mercApi::getInstance();
                $api->getVetDocumentDone($uuid, $model->attributes);

                $cache = \Yii::$app->cache;
                $cache->delete('vetDocRaw_' . $uuid);
                $cache->delete('vetDoc_' . $uuid);

                if (Yii::$app->request->isAjax)
                    return true;
                return $this->redirect(['view', 'uuid' => $uuid]);
            }
        } catch (\Error $e)
        {
            Yii::$app->session->setFlash('success', 'Ошибка обработки ВСД, возможно сервер ВЕТИС "Меркурий"  перегружен, попробуйте повторить запрос чуть позже<br>
                  <small>Если ошибка повторяется, пожалуйста, сообщите нам
                  <a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        catch (\Exception $e)
        {
            Yii::$app->session->setFlash('success', 'Ошибка обработки ВСД, возможно сервер ВЕТИС "Меркурий"  перегружен, попробуйте повторить запрос чуть позже<br>
                  <small>Если ошибка повторяется, пожалуйста, сообщите нам
                  <a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }

        try {
            $document = new getVetDocumentByUUIDRequest();
            $document->getDocumentByUUID($uuid);
        }catch (\Error $e)
        {
            Yii::$app->session->setFlash('success', 'Ошибка загрузки формы акта неоответствия ВСД, возможно сервер ВЕТИС "Меркурий"  перегружен, попробуйте повторить запрос чуть позже<br>
                  <small>Если ошибка повторяется, пожалуйста, сообщите нам
                  <a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
            return $this->goBack((!empty(Yii::$app->request->referrer) ? Yii::$app->request->referrer : ['index']));
        }
        catch (\Exception $e)
        {
            Yii::$app->session->setFlash('success', 'Ошибка загрузки формы акта неоответствия ВСД, возможно сервер ВЕТИС "Меркурий"  перегружен, попробуйте повторить запрос чуть позже<br>
                  <small>Если ошибка повторяется, пожалуйста, сообщите нам
                  <a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
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
            foreach ($selected as $uuid) {
                $api = mercApi::getInstance();
                $api->getVetDocumentDone($uuid);

                $cache = \Yii::$app->cache;
                $cache->delete('vetDocRaw_' . $uuid);
                $cache->delete('vetDoc_' . $uuid);
            }
        } catch (\Error $e)
        {
            Yii::$app->session->setFlash('success', 'Ошибка обработки ВСД, возможно сервер ВЕТИС "Меркурий"  перегружен, попробуйте повторить запрос чуть позже<br>
                  <small>Если ошибка повторяется, пожалуйста, сообщите нам
                  <a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
        }
        catch (\Exception $e)
        {
            Yii::$app->session->setFlash('success', 'Ошибка обработки ВСД, возможно сервер ВЕТИС "Меркурий"  перегружен, попробуйте повторить запрос чуть позже<br>
                  <small>Если ошибка повторяется, пожалуйста, сообщите нам
                  <a href="mailto://info@mixcart.ru" target="_blank" class="alert-link" style="background:none">info@mixcart.ru</a></small>');
        }

        return $this->redirect(['index']);
    }
}
