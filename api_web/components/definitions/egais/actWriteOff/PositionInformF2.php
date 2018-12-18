<?php

namespace api_web\components\definitions\egais\actWriteOff;

use api_web\components\ValidateRequest;

/**
 * @SWG\Definition(type="object")
 */
class PositionInformF2 extends ValidateRequest
{
    /**
     * @SWG\Property(@SWG\Xml(name="f2_reg_id"), example="TEST-FB-000000036821312")
     * @var string
     */
    public $f2_reg_id;

    public function rules()
    {
        return [
            [['f2_reg_id'], 'required'],
            [['f2_reg_id'], 'string']
        ];
    }
}