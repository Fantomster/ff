<?php

use yii\db\Migration;

/**
 * Class m181011_134953_add_col_user_status_to_merc_vsd
 */
class m181011_134953_add_col_user_status_to_merc_vsd extends Migration
{

    public function init()
    {
        $this->db = 'db_api';
        parent::init();
    }
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%merc_vsd}}', 'user_status', $this->string(50)->defaultValue(null));
        $this->addCommentOnColumn('{{%merc_vsd}}', 'user_status', 'Операция которую совершил пользователь с ВСД');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%merc_vsd}}', 'user_status');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181011_134953_add_col_user_status_to_merc_vsd cannot be reverted.\n";

        return false;
    }
    */
}
