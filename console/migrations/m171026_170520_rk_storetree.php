<?php

use yii\db\Schema;
use yii\db\Migration;

class m171026_170520_rk_storetree extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }

    public function safeUp()
    {
        $tableOptions = 'ENGINE=InnoDB';

        $this->createTable(
            '{{%rk_storetree}}',
            [
                'id'=> $this->primaryKey(11),
                'root'=> $this->integer(11)->null()->defaultValue(null),
                'rid'=> $this->integer(11)->notNull(),
                'lft'=> $this->integer(11)->notNull(),
                'rgt'=> $this->integer(11)->notNull(),
                'lvl'=> $this->smallInteger(5)->notNull(),
                'prnt'=> $this->integer(11)->notNull(),
                'name'=> $this->string(255)->notNull(),
                'icon'=> $this->string(255)->null()->defaultValue(null),
                'icon_type'=> $this->smallInteger(1)->notNull()->defaultValue(1),
                'active'=> $this->smallInteger(1)->notNull()->defaultValue(1),
                'selected'=> $this->smallInteger(1)->notNull()->defaultValue(0),
                'disabled'=> $this->smallInteger(1)->notNull()->defaultValue(0),
                'readonly'=> $this->smallInteger(1)->notNull()->defaultValue(0),
                'visible'=> $this->smallInteger(1)->notNull()->defaultValue(1),
                'collapsed'=> $this->smallInteger(1)->notNull()->defaultValue(0),
                'movable_u'=> $this->smallInteger(1)->notNull()->defaultValue(1),
                'movable_d'=> $this->smallInteger(1)->notNull()->defaultValue(1),
                'movable_l'=> $this->smallInteger(1)->notNull()->defaultValue(1),
                'movable_r'=> $this->smallInteger(1)->notNull()->defaultValue(1),
                'removable'=> $this->smallInteger(1)->notNull()->defaultValue(1),
                'removable_all'=> $this->smallInteger(1)->notNull()->defaultValue(0),
                'created_at'=> $this->datetime()->null()->defaultValue(null),
                'updated_at'=> $this->datetime()->null()->defaultValue(null),
                'type'=> $this->integer(11)->null()->defaultValue(null),
                'acc'=> $this->integer(11)->null()->defaultValue(null),
                'fid'=> $this->integer(11)->null()->defaultValue(null),
                'version'=> $this->integer(11)->null()->defaultValue(null),
            ],$tableOptions
        );
        $this->createIndex('tbl_product_NK1','{{%rk_storetree}}',['root'],false);
        $this->createIndex('tbl_product_NK2','{{%rk_storetree}}',['lft'],false);
        $this->createIndex('tbl_product_NK3','{{%rk_storetree}}',['rgt'],false);
        $this->createIndex('tbl_product_NK4','{{%rk_storetree}}',['lvl'],false);
        $this->createIndex('tbl_product_NK5','{{%rk_storetree}}',['active'],false);

    }

    public function safeDown()
    {
        $this->dropIndex('tbl_product_NK1', '{{%rk_storetree}}');
        $this->dropIndex('tbl_product_NK2', '{{%rk_storetree}}');
        $this->dropIndex('tbl_product_NK3', '{{%rk_storetree}}');
        $this->dropIndex('tbl_product_NK4', '{{%rk_storetree}}');
        $this->dropIndex('tbl_product_NK5', '{{%rk_storetree}}');
        $this->dropTable('{{%rk_storetree}}');
    }
}
