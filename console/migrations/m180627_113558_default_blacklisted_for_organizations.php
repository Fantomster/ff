<?php

use yii\db\Migration;

/**
 * Class m180627_113558_default_blacklisted_for_organizations
 */
class m180627_113558_default_blacklisted_for_organizations extends Migration {

    /**
     * {@inheritdoc}
     */
    public function safeUp() {
        $this->alterColumn('{{%organization}}', 'blacklisted', $this->integer()->notNull()->defaultValue(common\models\Organization::STATUS_UNSORTED));
        $this->update('{{%organization}}', ['blacklisted' => common\models\Organization::STATUS_UNSORTED], ['blacklisted' => common\models\Organization::STATUS_WHITELISTED]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown() {
        $this->alterColumn('{{%organization}}', 'blacklisted', $this->integer()->notNull()->defaultValue(0));
        $this->update('{{%organization}}', ['blacklisted' => common\models\Organization::STATUS_WHITELISTED], ['blacklisted' => common\models\Organization::STATUS_UNSORTED]);
    }

}
