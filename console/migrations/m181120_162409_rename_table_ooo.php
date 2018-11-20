<?php

use yii\db\Migration;

class m181120_162409_rename_table_ooo extends Migration
{
    public function safeUp()
    {
        $this->execute('rename table `ooo` to `organization_forms`;');
        $this->execute('alter table `organization_forms` comment "Таблица соответствия кратких и полных названий форм собственности организаций";');
    }

    public function safeDown()
    {$this->execute('rename table `organization_forms` to `ooo`;');
        $this->execute('alter table `ooo` comment "Таблица соответствия кратких и полных названий форм собственности организаций";');
    }
}
