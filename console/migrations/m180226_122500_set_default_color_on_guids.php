<?php

use yii\db\Migration;

/**
 * Class m180226_122500_set_default_color_on_guids
 */
class m180226_122500_set_default_color_on_guids extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $guids = \common\models\guides\Guide::find()->where('color is null')->all();

        foreach ($guids as $guide)
        {
            $guide->color = \common\models\guides\Guide::$COLORS[rand(0,17)];
            $guide->save(false);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        return true;
    }
}
