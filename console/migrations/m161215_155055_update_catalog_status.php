<?php

use yii\db\Migration;

class m161215_155055_update_catalog_status extends Migration
{
    public function safeUp() {
        $this->update('{{%catalog}}', ['status'=> common\models\Catalog::STATUS_ON], ['type' => common\models\Catalog::BASE_CATALOG]);       
    }
    
}
