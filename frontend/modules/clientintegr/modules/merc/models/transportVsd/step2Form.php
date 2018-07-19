<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 17.07.2018
 * Time: 18:36
 */

namespace frontend\modules\clientintegr\modules\merc\models\transportVsd;

use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\dictsApi;
use frontend\modules\clientintegr\modules\merc\helpers\api\dicts\ListOptions;
use yii\base\Model;

class step2Form extends Model
{
    public $purpose;
    public $cargoExpertized;
    public $locationProsperity = 'Благополучна';

    public function rules()
    {
        return [
            [['purpose', 'cargoExpertized', 'locationProsperity'], 'required'],
            [['purpose', 'cargoExpertized', 'locationProsperity'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'purpose' => 'Назначение груза',
            'cargoExpertized' => 'Результат проведения ВСЭ',
            'locationProsperity' => 'Благополучие местности',
        ];
    }

    public function getPurposeList()
    {
        $api = dictsApi::getInstance();
        $listOptions = new ListOptions();
        $listOptions->count = 100;
        $listOptions->offset = 0;
        $res = [];

        do {
            $list = $api->getPurposeList($listOptions);
            $list = $list->purposeList;

            if(isset($list->purpose)) {
                foreach ($list->purpose as $item) {
                    if ($item->last && $item->active)
                        $res[$item->guid] = $item->name;
                }
            }
            if($list->count < $list->total)
                $listOptions->offset += $list->count;
        } while ($list->total > ($list->count + $list->offset));

        return $res;
    }

    public function getExpertizeList()
    {
       return [
            'UNKNOWN' => 'Результат неизвестен',
            'UNDEFINED' => 'Результат невозможно определить (не нормируется)',
            'POSITIVE' => 'Положительный результат',
            'NEGATIVE' => 'Отрицательный результат',
            'UNFULFILLED' => 'Не проводилось',
            'VSERAW' => 'ВСЭ подвергнуто сырьё, из которого произведена продукция',
            'VSEFULL' => 'Продукция подвергнута ВСЭ в полном объеме'
       ];
    }
}