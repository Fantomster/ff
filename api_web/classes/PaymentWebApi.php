<?php

namespace api_web\classes;

use Yii;
use common\models\Currency;
use frontend\modules\billing\helpers\Tarif;
use api_web\exceptions\ValidationException;
use frontend\modules\billing\models\BillingPayment;
use frontend\modules\billing\providers\ProviderInterface;

/**
 * Class PaymentWebApi
 * @package api_web\classes
 */
class PaymentWebApi extends \api_web\components\WebApi
{
    /**
     * Список валют
     * @return array|Currency[]|\yii\db\ActiveRecord[]
     */
    public function currencyList()
    {
        $list = Currency::find()
            ->select(['id', 'symbol as iso_code', 'text'])
            ->where(['is_active' => true])
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $item['id'] = (int)$item['id'];
        }

        return $list;
    }

    /**
     * Тариф
     * @return array
     */
    public function getTarif()
    {
        return Tarif::getPayment();
    }

    /**
     * @param array $post
     * @return string
     * @throws ValidationException
     * @throws \Exception
     */
    public function create(array $post)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->validatePaymentPost($post);
            /**
             * @var ProviderInterface $provider
             */
            $provider = (new \frontend\modules\billing\Module('billing'))->getProvider();
            $payment = new BillingPayment($post);
            //Валюта, передаем iso_code
            $payment->setCurrency($post['currency']);
            //Пользователь который авторизирован
            $payment->setAttribute('user_id', $this->user->id);
            //Организация
            $payment->setAttribute('organization_id', $this->user->organization->id);
            //Устанавливаем провайдера
            $payment->setProvider($provider);
            //Валидируем, сохраняем
            if ($payment->validate() && $payment->save()) {
                //Отправляем на сервер
                $response = $provider->makePayment($payment);
            } else {
                throw new \Exception(array_keys($payment->getFirstErrors())[0] . ':' . array_pop($payment->getFirstErrors()));
            }
            //Вносим измнения в базу
            $transaction->commit();
            return $response['confirmation']['confirmationUrl'];
        } catch (ValidationException $e) {
            //Откат всех изменений в базе, ошибку выводим, хэндлер запишет ее в логи
            $transaction->rollBack();
            throw $e;
        } catch (\Exception $e) {
            //Откат всех изменений в базе, ошибку выводим, хэндлер запишет ее в логи
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Валидация входящих параметров для оплаты
     * @param $post
     * @throws ValidationException
     */
    private function validatePaymentPost($post)
    {
        $attributes = ['amount', 'payment_type_id', 'currency'];
        foreach ($attributes as $attribute) {
            if (isset($post[$attribute])) {
                if ($attribute == 'currency') {
                    if (mb_strlen($post[$attribute]) != 3) {
                        throw new ValidationException([$attribute => 'It is expected the three-digit code']);
                    }
                } else {
                    if (!is_numeric($post[$attribute])) {
                        throw new ValidationException([$attribute => 'Must be a numeric value']);
                    }
                }
            } else {
                throw new ValidationException([$attribute => 'is empty']);
            }
        }
    }
}