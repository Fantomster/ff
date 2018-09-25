<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 10:55 AM
 */

namespace api_web\modules\integration\classes;


use api_web\modules\integration\classes\dictionaries\abstractDictionary;
use api_web\modules\integration\interfaces\DictionaryInterface;

class Dictionary
{
    /**@var abstractDictionary*/
    public $dict;

    public function __construct($service_id, $type)
    {
        $int = new Integration($service_id);
        $this->setDictionary($int->getDict($type));
    }

    private function setDictionary(DictionaryInterface $dict){
        $this->dict = $dict;
    }

    public function productList($request){
        return $this->dict->productList($request);
    }

    public function agentList($request){
    	return $this->dict->agentList($request);
    }

    public function agentUpdate($request){
    	return $this->dict->agentUpdate($request);
    }
}