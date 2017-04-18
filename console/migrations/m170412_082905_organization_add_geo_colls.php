<?php

use yii\db\Migration;
use yii\db\Schema;

class m170412_082905_organization_add_geo_colls extends Migration
{   
    public function safeUp() {
        $this->addColumn('{{%organization}}', 'lat', Schema::TYPE_FLOAT . ' NULL');
        $this->addColumn('{{%organization}}', 'lng', Schema::TYPE_FLOAT . ' NULL');
        $this->addColumn('{{%organization}}', 'country', $this->string()->null());
        $this->addColumn('{{%organization}}', 'locality', $this->string()->null());
        $this->addColumn('{{%organization}}', 'route', $this->string()->null());
        $this->addColumn('{{%organization}}', 'street_number', $this->string()->null());
        $this->addColumn('{{%organization}}', 'place_id', $this->string()->null());
        $this->addColumn('{{%organization}}', 'formatted_address', $this->string()->null());
    }

    public function safeDown() {
        $this->dropColumn('{{%organization}}', 'lat');
        $this->dropColumn('{{%organization}}', 'lng');
        $this->dropColumn('{{%organization}}', 'country');
        $this->dropColumn('{{%organization}}', 'locality');
        $this->dropColumn('{{%organization}}', 'route');
        $this->dropColumn('{{%organization}}', 'street_number');
        $this->dropColumn('{{%organization}}', 'place_id');
        $this->dropColumn('{{%organization}}', 'formatted_address');
    }
}
