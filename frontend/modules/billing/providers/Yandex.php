<?php

namespace frontend\modules\billing\providers;

use YandexCheckout\Model\MonetaryAmount;
use YandexCheckout\Model\Receipt;
use YandexCheckout\Model\ReceiptItem;
use yii\db\Expression;
use YandexCheckout\Client;
use frontend\modules\billing\models\BillingPayment;

class Yandex extends Client implements ProviderInterface
{
    /**
     * Авторизация https://kassa.yandex.ru/docs/checkout-api/#autentifikaciq
     * @param array $config
     * @return Client
     */
    public function auth(array $config)
    {
        return parent::setAuth($config['shop_id'], $config['secret_key']);
    }

    /**
     * Создание платежа https://kassa.yandex.ru/docs/checkout-api/#sozdanie-platezha
     * @param BillingPayment $payment
     * @return \YandexCheckout\Request\Payments\CreatePaymentResponse
     * @throws \Exception
     */
    public function makePayment(BillingPayment $payment)
    {
        try {
            $item = new ReceiptItem();
            $item->setPrice((new MonetaryAmount($payment->amount, $payment->currency->iso_code)));
            $item->setDescription($payment->paymentType->title);
            $item->setQuantity(1);
            $item->setVatCode(1);

            $receipt = new Receipt();
            $receipt->setEmail($payment->user->email);
            $receipt->addItem($item);

            $pay = [
                'amount' => [
                    'value' => $payment->amount,
                    'currency' => $payment->currency->iso_code
                ],
                'receipt' => $receipt,
                'description' => $payment->paymentType->title . ' (' . $payment->organization->name . ')',
                'metadata' => [
                    'billing_payment_id' => $payment->billing_payment_id,
                    'currency_id' => $payment->currency_id,
                    'organization_id' => $payment->organization_id,
                    'description' => $payment->paymentType->title
                ],
                'confirmation' => [
                    'type' => 'redirect',
                    'enforce' => true,
                    'return_url' => $payment->return_url
                ],
                'client_ip' => \Yii::$app->request->getUserIP(),
                'capture' => false
            ];
            //Создаем платеж
            $response = parent::createPayment($pay, $payment->idempotency_key);
            if (isset($response->id)) {
                $payment->external_payment_id = $response->id;
                $payment->setAttribute('external_created_at', $response->created_at->getTimestamp());
                $payment->status = BillingPayment::STATUS_WAIT;
                if ($payment->validate() && $payment->save()) {
                    return $response;
                } else {
                    throw new \Exception(array_keys($payment->getFirstErrors())[0] . ':' . array_pop($payment->getFirstErrors()));
                }
            } else {
                throw new \Exception('Empty Response ID');
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Подтверждение платежа https://kassa.yandex.ru/docs/checkout-api/#podtwerzhdenie-platezha
     * @param BillingPayment $payment
     * @return mixed
     * @throws \Exception
     */
    public function confirmPayment(BillingPayment $payment)
    {
        try {
            if (empty($payment->external_payment_id)) {
                throw new \Exception('Empty $payment->external_payment_id');
            }
            //Получаем информацию о платеже от провайдера
            $info = $this->paymentInfo($payment);

            if ($info->status == 'waiting_for_capture') {
                //Проверим сумму и валюту
                if ($info->amount->value == $payment->amount && $info->amount->currency == $payment->currency->iso_code) {
                    if ($info->paid === true) {
                        $params = [
                            'amount' => [
                                'value' => $payment->amount,
                                'currency' => $payment->currency->iso_code
                            ]
                        ];
                        $response = parent::capturePayment($params, $payment->external_payment_id, BillingPayment::generateIdemKey());
                        if ($response->status == 'succeeded') {
                            $payment->payment_at = new Expression('NOW()');
                            $payment->status = BillingPayment::STATUS_SUCCESS;
                            if ($payment->save()) {
                                return $response;
                            } else {
                                throw new \Exception(array_keys($payment->getFirstErrors())[0] . ':' . array_pop($payment->getFirstErrors()));
                            }
                        } else {
                            throw new \Exception('Ошибка при подтверждении заказа в кассе');
                        }
                    } else {
                        throw new \Exception('Признак paid отличен от true - заказ не оплачен.');
                    }
                } else {
                    throw new \Exception('Различается сумма в кассе и MixCart.');
                }
            } else if ($info->status == 'succeeded') {
                if (empty($payment->payment_at)) {
                    $payment->payment_at = new Expression('NOW()');
                }
                $payment->status = BillingPayment::STATUS_SUCCESS;
                if ($payment->save()) {
                    return $info;
                } else {
                    throw new \Exception(array_keys($payment->getFirstErrors())[0] . ':' . array_pop($payment->getFirstErrors()));
                }
            } else {
                throw new \Exception('status в кассе дожлен быть waiting_for_capture: ' . $info->status);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Отмена платежа https://kassa.yandex.ru/docs/checkout-api/#otmena-platezha
     * @param BillingPayment $payment
     * @return mixed
     * @throws \Exception
     */
    public function refusePayment(BillingPayment $payment)
    {
        try {
            if (empty($payment->external_payment_id)) {
                throw new \Exception('Empty $payment->external_payment_id');
            }
            $response = parent::cancelPayment($payment->external_payment_id, BillingPayment::generateIdemKey());
            if ($response->status == 'canceled') {
                $payment->refund_at = new Expression('NOW()');
                $payment->status = BillingPayment::STATUS_REFUND;
                if ($payment->save()) {
                    return $response;
                } else {
                    throw new \Exception(array_keys($payment->getFirstErrors())[0] . ':' . array_pop($payment->getFirstErrors()));
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Информация о платеже https://kassa.yandex.ru/docs/checkout-api/#informaciq-o-platezhe
     * @param BillingPayment $payment
     * @return mixed
     * @throws \Exception
     */
    public function paymentInfo(BillingPayment $payment)
    {
        try {
            if (empty($payment->external_payment_id)) {
                throw new \Exception('Empty $payment->external_payment_id');
            }
            return parent::getPaymentInfo($payment->external_payment_id);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @return bool
     */
    public function isProviderRequest()
    {
        //IP адреса с которых доступны уведомления
        $allow_ips_yandex = [
            '185.71.77.2',
            '185.71.77.3',
            '185.71.77.4',
            '185.71.77.5',
            '185.71.76.2',
            '185.71.76.3',
            '185.71.76.4',
            '127.0.0.1'
        ];

        return in_array(\Yii::$app->request->getUserIP(), $allow_ips_yandex);
    }
}