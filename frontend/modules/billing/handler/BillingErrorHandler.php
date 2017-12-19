<?php

namespace frontend\modules\billing\handler;

use frontend\modules\billing\helpers\BillingLogger;
use Yii;
use yii\base\ErrorHandler;
use yii\web\Response;

class BillingErrorHandler extends ErrorHandler
{
    /**
     * @param \Error|\Exception $exception
     */
    protected function renderException($exception)
    {
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
        } else {
            $response = new Response();
        }

        $response->data = $this->convertExceptionToArray($exception);
        $response->setStatusCode($exception->statusCode);
        $response->send();
    }

    /**
     * @param \Error|\Exception $exception
     * @return array
     */
    protected function convertExceptionToArray($exception)
    {
        $error = ['error' => $exception->getMessage()];
        BillingLogger::log($error, Yii::$app->controller->id.'/'.Yii::$app->controller->action->id, BillingLogger::LOGGER_STATUS_ERROR);
        return $error;
    }
}