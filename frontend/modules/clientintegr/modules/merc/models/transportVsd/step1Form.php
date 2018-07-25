<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17.07.2018
 * Time: 18:36
 */

namespace frontend\modules\clientintegr\modules\merc\models\transportVsd;


use api\common\models\merc\MercStockEntry;

class step1Form extends MercStockEntry
{
    public $select_amount;

    public function rules()
    {
        return [
            [['product_name', 'select_amount'], 'required'],
            [['select_amount'], 'number'],
            [['select_amount'], 'checkNax'],
            [['product_name'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge([
            'select_amount' => \Yii::t('message', 'frontend.client.integration.volume', ['ru' => 'Объём'])],
            parent::attributeLabels());
    }

    public function checkNax($attribute, $params)
    {

            if ($this->$attribute > $this->amount)
                $this->addError($attribute, 'Введенное количество больше доступного ('.$this->amount.').');
    }
}