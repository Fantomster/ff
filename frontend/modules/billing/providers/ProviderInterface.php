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
    public function makePayment(BillingPayment $payment);

    /**
     * @param BillingPayment $payment
     * @return mixed
     */
    public function paymentInfo(BillingPayment $payment);

    /**
     * @param BillingPayment $payment
     * @return mixed
     */
    public function confirmPayment(BillingPayment $payment);

    /**
     * @param BillingPayment $payment
     * @return mixed
     */
    public function refusePayment(BillingPayment $payment);

    /**
     * @return bool
     */
    public function isProviderRequest();
}