<?php

namespace frontend\modules\billing\providers;

use frontend\modules\billing\models\BillingPayment;

interface ProviderInterface
{
    /**
     * @param array $config
     * @return mixed
     */
    public function auth(array $config);

    /**
     * @param BillingPayment $payment
     * @return mixed
     */
    public function createPayment(BillingPayment $payment);

    /**
     * @param BillingPayment $payment
     * @return mixed
     */
    public function paymentInfo(BillingPayment $payment);

    /**
     * @param BillingPayment $payment
     * @return mixed
     */
    public function capturePayment(BillingPayment $payment);

    /**
     * @param BillingPayment $payment
     * @return mixed
     */
    public function cancelPayment(BillingPayment $payment);

    /**
     * @return bool
     */
    public function isProviderRequest();
}