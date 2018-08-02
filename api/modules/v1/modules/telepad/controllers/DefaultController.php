<?php

namespace api\modules\v1\modules\telepad\controllers;

use api\common\models\one_s\OneSContragent;
use api\common\models\one_s\OneSGood;
use api\common\models\one_s\OneSStore;
use api\common\models\one_s\OneSWaybill;
use api\common\models\one_s\OneSWaybillData;
use api\common\models\one_s\OneSСontragent;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\web\Controller;
use api\common\models\one_s\OneSRestAccess;
use api\common\models\ApiSession;
use api\common\models\ApiActions;
use common\models\CatalogBaseGoods;
use common\models\CatalogGoods;
use common\models\MpEd;
use common\models\Catalog;
use common\models\RelationSuppRest;

/**
 * Description of SiteController
 * F-Keeper SOAP server based on mongosoft\soapserver
 * Author: R.Smirnov
 */
class DefaultController extends Controller
{
    public $enableCsrfValidation = false;
    protected $authenticated = false;
    private $username;
    private $password;
    private $nonce;
    private $extimefrom;
    private $ip;

    /**
     * @param \yii\base\Action $action
     * @return bool
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->ip = Yii::$app->request->getUserIP();
            return true;
        }
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'wsdl' => [
                'class' => 'mongosoft\soapserver\Action',
                'serviceOptions' => [
                    'disableWsdlMode' => false,
                ]
            ],
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }


    /**
     * Hello
     * @param string $name1
     * @return string
     * @soap
     */
    public function getHello($name)
    {
        $this->save_action(__FUNCTION__, 0, 1, 'OK', $this->ip);
        return 'Hello ' . $name . ' IP - ' . $this->ip . '! Server Date:' . gmdate("Y-m-d H:i:s");
    }


}