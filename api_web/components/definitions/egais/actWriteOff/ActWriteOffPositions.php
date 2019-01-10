<?php

namespace api_web\components\definitions\egais\actWriteOff;

use api_web\components\ValidateRequest;

/**
 * @SWG\Definition(type="object")
 */
class ActWriteOffPositions extends ValidateRequest
{
    /**
     * @SWG\Property(@SWG\Xml(name="identity"), example=1)
     * @var integer
     */
    public $identity;

    /**
     * @SWG\Property(@SWG\Xml(name="quantity"), example=2)
     * @var integer
     */
    public $quantity;

    /**
     * @SWG\Property(@SWG\Xml(name="sum_sale"), example=123.00)
     * @var double
     */
    public $sum_sale;

    /**
     * @SWG\Property(property="inform_f2", ref="PositionInformF2")
     * @var object
     */
    public $inform_f2;

    /**
     * @SWG\Property(property="mark_code_info", ref="PositionMarkCode")
     * @var object
     */
    public $mark_code_info;

    public function rules()
    {
        return [
            [['identity', 'quantity', 'sum_sale', 'inform_f2', 'mark_code_info'], 'required'],
            [['identity', 'quantity'], 'integer'],
            [['sum_sale'], 'double'],
            ['inform_f2', 'isInstanceOf', 'params' => ['class' => PositionInformF2::class]],
            ['mark_code_info', 'isInstanceOf', 'params' => ['class' => PositionMarkCode::class]],
        ];
    }
}