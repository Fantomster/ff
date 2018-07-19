<?php

use yii\db\Migration;

class m180719_090015_alter_collate_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $this->execute('alter table `integration_invoice` CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
        $this->execute('alter table `integration_invoice` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
    }

    public function safeDown()
    {
        $this->execute('alter table `integration_invoice` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;');
        $this->execute('alter table `integration_invoice` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;');
    }

}
