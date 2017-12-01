<?php

use yii\db\Migration;

/**
 * Handles the creation of table `source_message_and_message`.
 */
class m171127_143659_create_source_message_and_message_table extends Migration
{


    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $output = exec("php yii migrate/up --migrationPath=@yii/i18n/migrations --interactive=0");
        echo "<pre>$output</pre>";
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $output = exec("php yii migrate/down --migrationPath=@yii/i18n/migrations --interactive=0");
        echo "<pre>$output</pre>";
    }
}
