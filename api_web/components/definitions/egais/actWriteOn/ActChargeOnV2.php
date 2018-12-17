<?php

namespace api_web\components\definitions\egais\actWriteOn;

use api_web\components\ValidateRequest;

/**
 * @SWG\Definition(type="object")
 */
class ActChargeOnV2 extends ValidateRequest
{
    /**
     * @SWG\Property(@SWG\Xml(name="note"), example="Найдена не учтенная продукция")
     * @var string
     */
    public $note;

    /**
     * @SWG\Property(@SWG\Xml(name="type"), example="Производство_Сливы")
     * @var string
     */
    public $type;

    /**
     * @SWG\Property(@SWG\Xml(name="items"), type="array", @SWG\Items(ref="ActItems"))
     */
    public $items = [];

    public function rules()
    {
        return [
            [['note', 'type', 'items'], 'required'],
            [['note', 'type'], 'string'],
            ['items', 'isInstanceOf', 'params' => ['class' => ActItems::class]],
        ];
    }
}