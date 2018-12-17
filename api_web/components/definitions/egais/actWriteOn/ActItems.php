<?php

namespace api_web\components\definitions\egais\actWriteOn;

use api_web\components\ValidateRequest;

/**
 * @SWG\Definition(type="object", @SWG\Xml(name="Items"))
 */
class ActItems extends ValidateRequest
{
    /**
     * @SWG\Property(@SWG\Xml(name="identity"), example="1")
     * @var integer
     */
    public $identity;

    /**
     * @SWG\Property(@SWG\Xml(name="quantity"), example="2")
     * @var string
     */
    public $quantity;

    /**
     * @SWG\Property(property="product", ref="ProductListed")
     * @var object
     */
    public $product;

    /**
     * @SWG\Property(property="inform_f1", ref="InformF1")
     * @var object
     */
    public $inform_f1;

    /**
     * @SWG\Property(property="mark_code_info", ref="MarkCodeInfo")
     * @var object
     */
    public $mark_code_info;

    public function rules()
    {
        return [
            [['identity', 'quantity', 'product', 'inform_f1', 'mark_code_info'], 'required'],
            [['identity'], 'integer'],
            [['quantity'], 'string'],
            ['product', 'isInstanceOf', 'params' => ['class' => ItemProduct::class]],
            ['inform_f1', 'isInstanceOf', 'params' => ['class' => ItemInformF1::class]],
            ['mark_code_info', 'isInstanceOf', 'params' => ['class' => ItemMarkCode::class]],
        ];
    }
}