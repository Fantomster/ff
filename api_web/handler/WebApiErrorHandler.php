<?php

namespace api_web\handler;

use api_web\exceptions\ValidationException;
use Yii;
use yii\base\ErrorHandler;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class WebApiErrorHandler
 * @package api_web\handler
 */
class WebApiErrorHandler extends ErrorHandler
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

        $response->format = Response::FORMAT_JSON;
        $response->data = $this->convertExceptionToArray($exception);

        if (isset($exception->statusCode)) {
            $response->setStatusCode($exception->statusCode);
        }
        $response->send();
    }

    /**
     * @param \Error|\Exception $exception
     * @return array
     */
    protected function convertExceptionToArray($exception)
    {
        $error = [
            'code' => (int)$exception->getCode(),
            'type' => (string)$this->get_class_name($exception),
            'message' => (string)$exception->getMessage()
        ];

        if (YII_DEBUG === true) {
            $error['file'] = (string)$exception->getFile();
            $error['line'] = (int)$exception->getLine();
        }

        if ($exception instanceof ValidationException) {
            $error['errors'] = $exception->validation;
        }

        if ($exception instanceof NotFoundHttpException) {
            $error = \GuzzleHttp\json_encode($error);
        }

        return $error;
    }

    /**
     * @param $class
     * @return bool|string
     */
    private function get_class_name($class)
    {
        $class = get_class($class);
        if ($pos = strrpos($class, '\\')) {
            return substr($class, $pos + 1);
        }
        return $class;
    }
}