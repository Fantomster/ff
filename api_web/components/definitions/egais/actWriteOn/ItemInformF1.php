<?php

namespace api_web\components\definitions\egais\actWriteOn;

use api_web\components\ValidateRequest;

/**
 * @SWG\Definition(type="object")
 */
class ItemInformF1 extends ValidateRequest
{
    /**
     * @SWG\Property(@SWG\Xml(name="quantity"), example="20")
     * @var integer
     */
    public $quantity;

    /**
     * @SWG\Property(@SWG\Xml(name="bottling_date"), example="2014-11-20")
     * @var string
     */
    public $bottling_date;

    /**
     * @SWG\Property(@SWG\Xml(name="ttn_number"), example="Т-000430")
     * @var string
     */
    public $ttn_number;

    /**
     * @SWG\Property(@SWG\Xml(name="ttn_date"), example="2015-04-06")
     * @var string
     */
    public $ttn_date;

    /**
     * @SWG\Property(@SWG\Xml(name="egais_fix_number"), example="91000013637931")
     * @var string
     */
    public $egais_fix_number;

    /**
     * @SWG\Property(@SWG\Xml(name="egais_fix_date"), example="2015-04-06")
     * @var string
     */
    public $egais_fix_date;

    public function rules()
    {
        return [
            [['quantity', 'bottling_date', 'ttn_number', 'ttn_date', 'egais_fix_number', 'egais_fix_date'], 'required'],
            [['bottling_date', 'ttn_number', 'ttn_date', 'egais_fix_number', 'egais_fix_date'], 'string'],
            [['quantity'], 'integer']
        ];
    }
}