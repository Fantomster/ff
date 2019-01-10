<?php

namespace api_web\components\definitions\egais\actWriteOff;

use api_web\components\ValidateRequest;

/**
 * @SWG\Definition(type="object")
 */
class ActWriteOffV3 extends ValidateRequest
{
    /**
     * @SWG\Property(@SWG\Xml(name="identity"), example=456)
     * @var integer
     */
    public $identity;

    /**
     * @SWG\Property(@SWG\Xml(name="type_write_off"), example="Реализация")
     * @var string
     */
    public $type_write_off;

    /**
     * @SWG\Property(@SWG\Xml(name="note"), example="текст комментария")
     * @var string
     */
    public $note;

    /**
     * @SWG\Property(@SWG\Xml(name="positions"), type="array", @SWG\Items(ref="ActWriteOffPositions"))
     */
    public $positions = [];

    public function rules()
    {
        return [
            [['identity', 'type_write_off', 'note', 'positions'], 'required'],
            [['identity'], 'integer'],
            [['type_write_off', 'note'], 'string'],
            ['positions', 'isInstanceOf', 'params' => ['class' => ActWriteOffPositions::class]],
        ];
    }
}