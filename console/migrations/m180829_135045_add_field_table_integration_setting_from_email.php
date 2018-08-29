<?php

use yii\db\Migration;

class m180829_135045_add_field_table_integration_setting_from_email extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%integration_setting_from_email}}', 'language', $this->string(3)->notNull()->defaultValue('ru'));
        $this->addCommentOnColumn('{{%integration_setting_from_email}}', 'language',
            'Двухбуквенное обозначение языка, на котором ведётся переписка');
    }

    public function safeDown()
    {
        $this->dropColumn('{{%integration_setting_from_email}}', 'language');
    }
}
