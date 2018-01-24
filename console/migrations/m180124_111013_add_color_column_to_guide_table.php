<?php

use yii\db\Migration;

/**
 * Handles adding color to table `guide`.
 */
class m180124_111013_add_color_column_to_guide_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%guide}}', 'color', $this->string(255)->null()->defaultValue(null));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%guide}}', 'color');
    }
}
