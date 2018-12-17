<?php

namespace api_web\components;

use api_web\exceptions\ValidationException;
use yii\base\Model;

class ValidateRequest extends Model
{
    /**
     * @param $model
     * @param $request
     * @throws ValidationException
     */
    public static function loadData($model, $request)
    {
        /** @var Model $object */
        $object = new $model();
        $object->setAttributes($request);

        if (!$object->validate()) {
            throw new ValidationException($object->getErrors());
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function isInstanceOf($attribute, $params)
    {
        if (!empty($this->$attribute) && is_iterable($this->$attribute)) {
            /** @var Model $model */
            $model = new $params['class']();
            if (is_iterable(current($this->$attribute))) {
                foreach ($this->$attribute as $item) {
                    $this->checkModel($model, $item, $attribute);
                }
            } else {
                $this->checkModel($model, $this->$attribute, $attribute);
            }
        }
    }

    /**
     * @param $attribute
     * @param Model $model
     * @param array $item
     */
    private function checkModel(Model $model, array $item, $attribute)
    {
        $model->setAttributes($item);
        if (!$model->validate()) {
            $this->addError($attribute, $model->getErrors());
        }
    }
}