<?php
namespace frontend\modules\clientintegr\modules\merc\models;

class submitApplicationRequest extends BaseRequest
{
    public $apiKey;
    public $application;

    public function rules()
    {
        return [
            [['apiKey'], 'string', 'max' => 255],
            [['application'], 'safe'],
        ];
    }
}