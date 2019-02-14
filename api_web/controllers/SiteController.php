<?php

namespace api_web\controllers;

use api_web\components\WebApiSwaggerAction;
use Yii;
use yii\web\Controller;
use frontend\modules\billing\models\BillingPayment;
use frontend\modules\billing\providers\Yandex;

/**
 * Class SiteController
 * @package api_web\controllers
 */
class SiteController extends Controller
{

    public function actions()
    {
        $scanDir = [
            Yii::getAlias('@api_web/components/definitions/'),
            Yii::getAlias('@api_web/components/WebApiController.php'),
            Yii::getAlias('@api_web/controllers/UserController.php'),
            Yii::getAlias('@api_web/controllers/DefaultController.php'),
            Yii::getAlias('@api_web/controllers/AnalyticsController.php'),
            Yii::getAlias('@api_web/controllers/MarketController.php'),
            Yii::getAlias('@api_web/controllers/PaymentController.php'),
            Yii::getAlias('@api_web/controllers/CartController.php'),
            Yii::getAlias('@api_web/controllers/OrderController.php'),
            Yii::getAlias('@api_web/controllers/PreorderController.php'),
            Yii::getAlias('@api_web/controllers/LazyVendorController.php'),
            Yii::getAlias('@api_web/controllers/LazyVendorPriceController.php'),
            Yii::getAlias('@api_web/controllers/ClientController.php'),
            Yii::getAlias('@api_web/controllers/VendorController.php'),
            Yii::getAlias('@api_web/controllers/GuideController.php'),
            Yii::getAlias('@api_web/controllers/ChatController.php'),
            Yii::getAlias('@api_web/controllers/RequestController.php'),
            Yii::getAlias('@api_web/controllers/NotificationController.php'),
            Yii::getAlias('@api_web/controllers/EdiController.php'),
            Yii::getAlias('@api_web/controllers/SystemController.php'),
            Yii::getAlias('@api_web/controllers/RabbitController.php'),
            Yii::getAlias('@api_web/controllers/WaybillController.php'),
            Yii::getAlias('@api_web/controllers/CallbackController.php'),
            Yii::getAlias('@api_web/controllers/DocumentController.php'),
            Yii::getAlias('@api_web/controllers/JournalController.php'),
            Yii::getAlias('@api_web/controllers/PromoController.php'),
        ];

        /**
         * Добавление интеграционных контроллеров происходит автоматически
         */
        $module = Yii::$app->getModule('integration');
        $controllers = array_filter(scandir($module->getControllerPath()), function ($name) {
            return strstr($name, 'Controller.php');
        });

        foreach ($controllers as $file) {
            $scanDir[] = $module->getControllerPath() . '/' . $file;
        }

        foreach ($module->modules as $keyModule => $class) {
            $subModule = $module->getModule($keyModule);

            $controllers = array_filter(scandir($subModule->getControllerPath()), function ($name) {
                return strstr($name, 'Controller.php');
            });

            foreach ($controllers as $file) {
                $scanDir[] = $subModule->getControllerPath() . '/' . $file;
            }
        }

        $scanDir = array_unique($scanDir);

        return [
            'doc' => [
                'class' => \api_web\components\MySwaggerAction::className(),
                'restUrl' => \yii\helpers\Url::to(['/site/api'], true),
            ],
            'api' => [
                'class' => WebApiSwaggerAction::class,
                'scanDir' => $scanDir,
                'cache' => 'cache',
                'cacheKey' => 'api-web-swagger-cache'
            ],
        ];
    }

    public function beforeAction($action)
    {
        if ($action->id == 'payment-confirm') {
            \Yii::$app->request->enableCsrfCookie = false;
            \Yii::$app->request->enableCsrfValidation = false;
            \Yii::$app->request->enableCookieValidation = false;
            $this->enableCsrfValidation = false;
        }

        return parent::beforeAction($action); // TODO: Change the autogenerated stub
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionPaymentConfirm()
    {
        try {
            /**
             * @var Yandex $provider
             */
            $provider = (new \frontend\modules\billing\Module('billing'))->getProvider();
            if (!$provider->isProviderRequest()) {
                throw new \Exception(\Yii::t('api_web', "Request Provider no allow This IP:{ip}", ['ru'=>'Запрос поставщика не разрешает этот IP: {ip}', 'ip' => \Yii::$app->request->getUserIP()]));
            }

            $notification = \Yii::$app->request->getRawBody();
            if (empty($notification)) {
                throw new \Exception(\Yii::t('api_web', 'empty Body in Request', ['ru'=>'Пустое тело запроса']));
            }

            $notification = \GuzzleHttp\json_decode($notification, true);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $external_id = $notification['object']['id'];
                $payment = BillingPayment::findOne(['external_payment_id' => $external_id]);
                if (empty($payment)) {
                    throw new \Exception(\Yii::t('api_web', "Payment not found {body}", ['ru'=>'Платеж не найден {body}', 'body' => \Yii::$app->request->getRawBody()]));
                }
                $payment->checkProvider($provider);
                $provider->confirmPayment($payment);
                $transaction->commit();
                \Yii::$app->response->setStatusCode(200);
                \Yii::$app->response->send();
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            $i = [
                $e->getMessage(),
                date('d.m.Y H:i:s'),
                \Yii::$app->request->getUserIP(),
                PHP_EOL
            ];
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/notification_error.txt', implode(' | ', $i), FILE_APPEND);
            \Yii::$app->response->data = \GuzzleHttp\json_encode(['error' => $e->getMessage()]);
            \Yii::$app->response->setStatusCode(401);
            \Yii::$app->response->send();
        }
    }
}
