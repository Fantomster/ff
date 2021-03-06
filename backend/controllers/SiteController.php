<?php

namespace backend\controllers;

use common\models\Catalog;
use Yii;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\components\AccessRule;
use common\models\Role;

/**
 * Site controller
 */
class SiteController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'logout'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'import-from-xls', 'ajax-delete-product', 'ajax-edit-catalog-form', 'get-sub-cat', 'send-test-mail', 'get-file'],
                        'allow' => true,
                        'roles' => [
                            Role::ROLE_ADMIN,
//                            Role::ROLE_FKEEPER_OBSERVER,
                        ],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions() {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex() {
        //return $this->render('index');
        return $this->redirect(['/statistics/registered']);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin() {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout() {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionImportFromXls($id) {
        $catalog = Catalog::findOne([
                    'supp_org_id' => $id
        ]);
        $catalogId = $catalog->id;

        return \franchise\controllers\SiteController::actionImportFromXls($id, $catalogId);
    }

    public function actionAjaxDeleteProduct() {
        return \franchise\controllers\SiteController::actionAjaxDeleteProduct();
    }

    public function actionAjaxEditCatalogForm($catalog = null) {
        return GoodsController::actionAjaxUpdateProductMarketPlace(null);
    }

    public function actionGetSubCat() {
        return \franchise\controllers\SiteController::actionGetSubCat();
    }

    public function actionSendTestMail() {
        $model = new \backend\models\TestMail;
        if ($model->load(Yii::$app->request->post())) {
            try {
                $subject = "mixcart - Проверка почтовой службы";
                $result = Yii::$app->mailer->compose('test')
                        ->setTo($model->email)
                        ->setSubject($subject)
                        ->send();
                if ($result) {
                    Yii::$app->session->setFlash("email-success", 'Письмо отослано на почту ' . $model->email);
                    $model->email = '';
                }
            } catch (Exception $ex) {
                //
            }
        }

        return $this->render('send-test-mail', compact('model'));
    }

    function getRemoteFileSize($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_exec($ch);
        $fileSize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        curl_close($ch);
        if ($fileSize) {
            return $fileSize;
        }
    }

    public function actionGetFile($id) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        $file = '38f12ccb98e2fa8a8e3b7c9885695db5.gif';
        $size = $this->getRemoteFileSize('https://s3-eu-west-1.amazonaws.com/fkeeper/bill/38f12ccb98e2fa8a8e3b7c9885695db5.gif');
//        header('Content-Type: image/gif');
        header('Content-Disposition: attachment; filename=' . $file);
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);
        flush();
        readfile('https://s3-eu-west-1.amazonaws.com/fkeeper/bill/38f12ccb98e2fa8a8e3b7c9885695db5.gif');
    }

}
