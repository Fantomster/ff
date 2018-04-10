<?php

use yii\db\Migration;

/**
 * Class m180410_090349_fix_translations_dev_683
 */
class m180410_090349_fix_translations_dev_683 extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->delete('{{%source_message}}',
            ['message'=>'frontend.views.order.add_to_order']
        );

        /*************************************************************************************/
        $this->delete('{{%source_message}}',
            ['message'=>'frontend.controllers.order.add_position']);
    }
}
