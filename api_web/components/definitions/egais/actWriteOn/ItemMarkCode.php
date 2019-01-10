<?php

namespace api_web\components\definitions\egais\actWriteOn;

use api_web\components\ValidateRequest;

/**
 * @SWG\Definition(type="object")
 */
class ItemMarkCode extends ValidateRequest
{
    /**
     * @SWG\Property(@SWG\Xml(name="mark_code_first"), example="53N000004928QEWZ9Z804A1309090032244121011104020215019325183103168250")
     * @var string
     */
    public $mark_code_first;

    /**
     * @SWG\Property(@SWG\Xml(name="mark_code_second"), example="54N000004928QEWZ9Z804A1309090032244121011104020215019325183103168250")
     * @var string
     */
    public $mark_code_second;

    public function rules()
    {
        return [
            [['mark_code_first', 'mark_code_second'], 'required'],
            [['mark_code_first', 'mark_code_second'], 'string']
        ];
    }
}