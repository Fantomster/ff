<?php

namespace api_web\components\definitions\egais\actWriteOn;

use api_web\components\ValidateRequest;

/**
 * @SWG\Definition(type="object")
 */
class ProducerAddress extends ValidateRequest
{
    /**
     * @SWG\Property(@SWG\Xml(name="country"), example="643")
     * @var integer
     */
    public $country;

    /**
     * @SWG\Property(@SWG\Xml(name="region_code"), example="50")
     * @var integer
     */
    public $region_code;

    /**
     * @SWG\Property(@SWG\Xml(name="description"), example="РОССИЯ,141201,МОСКОВСКАЯ ОБЛ,,Пушкино г,,Октябрьская ул,46 (за исключением литера Б17, 1 этаж, № на плане 6, литера Б, 1 этаж, № на плане 8) |")
     * @var string
     */
    public $description;

    public function rules()
    {
        return [
            [['country', 'region_code', 'description'], 'required'],
            [['country', 'region_code'], 'integer'],
            [['description'], 'string']
        ];
    }
}