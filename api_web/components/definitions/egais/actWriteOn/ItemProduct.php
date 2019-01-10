<?php

namespace api_web\components\definitions\egais\actWriteOn;

use api_web\components\ValidateRequest;

/**
 * @SWG\Definition(type="object")
 */
class ItemProduct extends ValidateRequest
{
    /**
     * @SWG\Property(@SWG\Xml(name="full_name"), example="Водка 'Журавли'")
     * @var string
     */
    public $full_name;

    /**
     * @SWG\Property(@SWG\Xml(name="alc_code"), example="0150325000001195171")
     * @var string
     */
    public $alc_code;

    /**
     * @SWG\Property(@SWG\Xml(name="capacity"), example="0.7000")
     * @var double
     */
    public $capacity;

    /**
     * @SWG\Property(@SWG\Xml(name="unit_type"), example="Packed")
     * @var string
     */
    public $unit_type;

    /**
     * @SWG\Property(@SWG\Xml(name="alc_volume"), example=40)
     * @var integer
     */
    public $alc_volume;

    /**
     * @SWG\Property(@SWG\Xml(name="product_v_code"), example=200)
     * @var integer
     */
    public $product_v_code;

    /**
     * @SWG\Property(property="producer", ref="ProductProducer")
     * @var object
     */
    public $producer;

    public function rules()
    {
        return [
            [['full_name', 'alc_code', 'capacity', 'unit_type', 'alc_volume', 'product_v_code', 'producer'], 'required'],
            [['full_name', 'alc_code', 'unit_type'], 'string'],
            [['alc_volume', 'product_v_code'], 'integer'],
            [['capacity'], 'double'],
            ['producer', 'isInstanceOf', 'params' => ['class' => ProductProducer::class]],
        ];
    }
}