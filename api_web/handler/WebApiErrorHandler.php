<?php

namespace api_web\handler;

use api_web\exceptions\ValidationException;
use api_web\helpers\Logger;
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

        if ($exception->statusCode != 0) {
            $response->setStatusCode($exception->statusCode);
            \Yii::$app->response->statusCode = $exception->statusCode;
        } elseif ($response->data['code'] == 42000) {
            $response->setStatusCode(500);
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
            'code' => (int)$exception->statusCode ?? 0,
            'type' => (string)$this->get_class_name($exception),
            'message' => (string)$this->prepareMessage($exception->getMessage())
        ];

        if (YII_DEBUG === true) {
            $error['file'] = (string)$exception->getFile();
            $error['line'] = (int)$exception->getLine();
        }

        if ($exception instanceof ValidationException) {
            $validation = $exception->validation;
            foreach ($validation as $key => &$value) {
                if (is_string($value)) {
                    $value = (string)$this->prepareMessage($value);
                }
            }
            $error['errors'] = $validation;
        }

        if ($exception instanceof NotFoundHttpException) {
            $error = \GuzzleHttp\json_encode($error);
        } else {
            Logger::getInstance()::setType('error');
            Logger::getInstance()::response($error);
        }

        return $error;
    }

    /**
     * @param $msg
     * @return string
     */
    private function prepareMessage($msg)
    {
        try {
            if (strstr($msg, '|') !== false) {
                $msg = explode('|', $msg);
                $message = \Yii::t('api_web', $msg[0]);
                unset($msg[0]);
                return vsprintf($message, $msg);
            } else {
                return \Yii::t('api_web', $msg);
            }
        } catch (\Exception $e) {
            return $msg;
        }
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