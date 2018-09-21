<?php

use yii\db\Migration;

class m180921_125143_update_column_koef_tables_waybill_data extends Migration
{
    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $this->execute('alter table `iiko_waybill_data` change column `koef` `koef` DOUBLE NOT NULL DEFAULT 1');
        $this->execute('alter table `rk_waybill_data` change column `koef` `koef` DOUBLE NOT NULL DEFAULT 1');
        $this->execute('alter table `one_s_waybill_data` change column `koef` `koef` DOUBLE NOT NULL DEFAULT 1');
        $this->execute('alter table `all_map` change column `koef` `koef` DOUBLE NOT NULL DEFAULT 1');
        $this->addCommentOnColumn('{{%iiko_waybill_data}}', 'koef', 'Коэффициент пересчёта в приходной накладной в системе IIKO');
        $this->addCommentOnColumn('{{%rk_waybill_data}}', 'koef', 'Коэффициент пересчёта в приходной накладной в системе R-Keeper');
        $this->addCommentOnColumn('{{%one_s_waybill_data}}', 'koef', 'Коэффициент пересчёта в приходной накладной в системе 1С');
        $this->addCommentOnColumn('{{%all_map}}', 'koef', 'Коэффициент пересчёт единиц измерения');
    }

    public function safeDown()
    {
        echo "m180921_125143_update_column_koef_tables_waybill_data cannot be reverted.\n";
        return false;
    }
}
