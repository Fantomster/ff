<?php

namespace api_web\handler;

use Yii;
use yii\web\Response;
use yii\base\ErrorHandler;
use api_web\helpers\Logger;
use yii\web\NotFoundHttpException;
use api_web\exceptions\ValidationException;

/**
 * Class WebApiErrorHandler
 *
 * @package api_web\handler
 */
class WebApiErrorHandler extends ErrorHandler
{
    const HTTP_BAD_REQUEST_CODE = 400;
    const HTTP_FORBIDDEN_CODE = 403;
    const HTTP_INTERNAL_SERVER_ERROR_CODE = 500;

    /**
     * @param \Exception $exception
     * @throws \Exception
     */
    protected function renderException($exception)
    {
        \Yii::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
        } else {
            $response = new Response();
        }
        /** @var Response format */
        $response->format = Response::FORMAT_JSON;
        $response->data = $this->convertExceptionToArray($exception);
        $statusCode = isset($exception->statusCode) ? $exception->statusCode : self::HTTP_BAD_REQUEST_CODE;
        if ($statusCode != 0) {
            $response->setStatusCode($statusCode);
        } elseif ($exception->getCode() == 42000) {
            $response->setStatusCode(self::HTTP_INTERNAL_SERVER_ERROR_CODE);
        }
        $response->send();
    }

    /**
     * @param \Exception $exception
     * @return array|string
     * @throws \Exception
     */
    protected function convertExceptionToArray($exception)
    {
        $error = [
            'code'    => (int)$exception->getCode() ?? 0,
            'type'    => (string)$this->get_class_name($exception),
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
     * @return array|string
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