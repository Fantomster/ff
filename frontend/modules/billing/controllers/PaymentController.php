<?php

namespace frontend\modules\billing\controllers;

use frontend\modules\billing\providers\ProviderInterface;
use Yii;
use yii\web\Response;
use yii\web\Controller;
use frontend\modules\billing\models\BillingPayment;
use frontend\modules\billing\helpers\BillingLogger;
use frontend\modules\billing\handler\BillingAccessControl;

class PaymentController extends Controller
{
    /**
     * @var \common\models\User $user
     * @var ProviderInterface $provider
     */
    private $provider;
    private $log = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->provider = $this->module->getProvider();
        Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => BillingAccessControl::className(),
                'only' => ['create', 'capture', 'info', 'refund'],
                'rules' => [
                    [
                        'allow' => true,
                        'verbs' => ['POST', 'GET'],
                        /*'matchCallback' => function () {
                            return !Yii::$app->request->isAjax;
                        },*/
                    ],
                ],
            ]
        ];
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        $status = BillingLogger::LOGGER_STATUS_SUCCESS;

        if (isset($result['error'])) {
            $status = BillingLogger::LOGGER_STATUS_ERROR_API;
        }

        if ($this->log === true) {
            BillingLogger::log($result, $this->id . '/' . $this->action->id, $status);
        }

        return parent::afterAction($action, $result);
    }

    /**
     * @inheritdoc
     */
    public function actionIndex()
    {
        if ($this->provider->isProviderRequest()) {
            $notification = \GuzzleHttp\json_decode(file_get_contents("php://input"), true);
            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/notification.txt', print_r($notification,1), FILE_APPEND);
            if ($notification) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $external_id = $notification['object']['id'];
                    $payment = BillingPayment::find()->where(['external_payment_id' => $external_id])->one();
                    $payment->checkProvider($this->provider);
                    $response = $this->provider->capturePayment($payment);
                    $transaction->commit();
                    return $response;
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    return ['error' => $e->getMessage()];
                }
            } else {
                $this->log = false;
                return ['empty $notification'];
            }
        } else {
            $this->redirect('/site/index');
        }
    }

    /**
     * Создание платежа
     * @return array
     */
    public function actionCreate()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $post = [
                'amount' => Yii::$app->request->post('amount', 1),
                'payment_type_id' => Yii::$app->request->post('payment_type_id', 1),
                'currency' => Yii::$app->request->post('currency', 'RUB')
            ];

            $user = Yii::$app->user->identity;
            $payment = new BillingPayment($post);
            //Валюта, передаем iso_code
            $payment->setCurrency($post['currency']);
            //Пользователь который авторизирован
            $payment->setAttribute('user_id', $user->id);
            //Организация
            $payment->setAttribute('organization_id', $user->organization->id);
            //Устанавливаем провайдера
            $payment->setProvider($this->provider);
            //Валидируем, сохраняем
            if ($payment->validate() && $payment->save()) {
                //Отправляем на сервер
                $response = $this->provider->makePayment($payment);
            } else {
                throw new \Exception(array_keys($payment->getFirstErrors())[0] . ':' . array_pop($payment->getFirstErrors()));
            }
            //Вносим измнения в базу
            $transaction->commit();
            return $response;
        } catch (\Exception $e) {
            //Откат всех изменений в базе, ошибку выводим, хэндлер запишет ее в логи
            $transaction->rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Подтверждение платежа
     * @param integer $id
     * @return array
     */
    public function actionCapture($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $payment = BillingPayment::findOne($id);
            if ($payment) {
                $payment->checkProvider($this->provider);
                $response = $this->provider->confirmPayment($payment);
                $transaction->commit();
                return $response;
            } else {
                throw new \Exception('Paymnet not found ' . $id);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Информация о платеже
     * @param integer $id
     * @return array
     */
    public function actionInfo($id)
    {
        $payment = BillingPayment::findOne($id);
        try {
            if ($payment) {
                $payment->checkProvider($this->provider);
                return $this->provider->paymentInfo($payment);
            }
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Отмена платежа
     * @param integer $id
     * @return array
     */
    public function actionRefund($id)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $payment = BillingPayment::findOne($id);
            if ($payment) {
                $payment->checkProvider($this->provider);
                $response = $this->provider->refusePayment($payment);
                $transaction->commit();
                return $response;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['error' => $e->getMessage()];
        }
    }
}