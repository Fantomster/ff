<?php

namespace api_web\modules\integration\classes;


use api_web\modules\integration\classes\dictionaries\abstractDictionary;
use api_web\modules\integration\interfaces\DictionaryInterface;

/**
 * Class Dictionary
 *
 * @package api_web\modules\integration\classes
 */
class Dictionary
{
    /**@var abstractDictionary */
    public $dict;

    /**
     * Dictionary constructor.
     *
     * @param $service_id
     * @param $type
     * @throws \yii\web\BadRequestHttpException
     */
    public function __construct($service_id, $type)
    {
        $int = new Integration($service_id);
        $this->setDictionary($int->getDict($type));
    }

    /**
     * @param \api_web\modules\integration\interfaces\DictionaryInterface $dict
     */
    private function setDictionary(DictionaryInterface $dict)
    {
        $this->dict = $dict;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->dict->{$name}(current($arguments));
    }
}