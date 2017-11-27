<?php
/**
 * Created by PhpStorm.
 * User: MikeN
 * Date: 27.11.2017
 * Time: 10:14
 */

namespace common\widgets;

use yii\base\Widget;

class LangSwitch extends Widget
{
    public function run()
    {
        return $this->render('lang_switch');
    }

    /**
     * Возвращает иконку флага
     * @param string $country
     * @param array $options
     * @param bool $squared
     * @return string
     */
    public static function getFlag($country = 'us', $options = [], $squared = false)
    {
        $country = ($country === 'en' ? 'us' : $country);

        return \modernkernel\flagiconcss\Flag::widget([
            'tag' => 'i',
            'country' => $country,
            'squared' => $squared,
            'options' => $options
        ]);
    }
}