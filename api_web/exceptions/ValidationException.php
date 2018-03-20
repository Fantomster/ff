<?php

namespace api_web\exceptions;

/**
 * Class ValidationException
 * @package api_web\exceptions
 */
class ValidationException extends \yii\web\HttpException
{
    public $validation;

    public function __construct(array $validation = [], $message = 'Ошибка валидации полей', $code = 101, \Exception $previous = null)
    {
        $this->validation = $validation;
        parent::__construct(400, $message, $code, $previous);
    }
}