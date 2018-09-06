<?php

use yii\db\Migration;

/**
 * Class m180906_141924_add_settings_mercury
 */
class m180906_141924_add_settings_mercury extends Migration
{
    public function init()
    {
        $this->db = "db_api";
        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%merc_pconst}}', ['org' => 0, 'const_id' => 1, 'value' => 'onlinemarket-180612']);
        $this->insert('{{%merc_pconst}}', ['org' => 0, 'const_id' => 2, 'value' => 'Jn3F6bK3k7']);
        $this->insert('{{%merc_pconst}}', ['org' => 0, 'const_id' => 4, 'value' => 'none']);
        $this->insert('{{%merc_pconst}}', ['org' => 0, 'const_id' => 5, 'value' => 'none']);
        $this->insert('{{%merc_pconst}}', ['org' => 0, 'const_id' => 6, 'value' => 'none']);
        $this->insert('{{%merc_pconst}}', ['org' => 0, 'const_id' => 10, 'value' => 'none']);
        $this->insert('{{%merc_pconst}}', ['org' => 0, 'const_id' => 11, 'value' => 'none']);
        $this->insert('{{%merc_pconst}}', ['org' => 0, 'const_id' => 12, 'value' => 'none']);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%merc_pconst}}', ['org' => 0]);
    }

}
