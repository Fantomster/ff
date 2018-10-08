<?php

use yii\db\Migration;

/**
 * Class m181001_143657_add_login_allowed_column_in_license_table
 */
class m181001_143657_add_login_allowed_column_in_license_table extends Migration
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
        $this->addColumn('{{%license}}', 'login_allowed', $this->boolean()->defaultValue(true));
        $this->addCommentOnColumn('{{%license}}', 'login_allowed', 'Является признаком, достаточно ли данной лицензии для обеспечения возможности входа в систему');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%license}}', 'login_allowed');
    }

}
