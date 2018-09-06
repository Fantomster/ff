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
        $res = \common\models\vetis\VetisPurpose::getPurposeList();

        if (!empty($res)) {
            return $res;
        }

        $api = dictsApi::getInstance();

        $list = $api->getPurposeList();
        return $list ?? [];
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
