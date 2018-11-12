<?php

use yii\db\Migration;

/**
 * Class m181106_113728_add_edi_providers_columns
 */
class m181106_113728_add_edi_providers_columns extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%edi_organization}}', 'provider_id', $this->tinyInteger()->null());
        $this->addCommentOnColumn('{{%edi_organization}}', 'provider_id', 'ID EDI провайдера');
        $this->addColumn('{{%edi_organization}}', 'provider_priority', $this->tinyInteger()->null());
        $this->addCommentOnColumn('{{%edi_organization}}', 'provider_priority', 'Приоритет провайдера');

        $this->addColumn('{{%edi_provider}}', 'provider_class', $this->string(30)->null());
        $this->addCommentOnColumn('{{%edi_provider}}', 'provider_class', 'Класс провайдера');
        $this->addColumn('{{%edi_provider}}', 'realization_class', $this->string(30)->null());
        $this->addCommentOnColumn('{{%edi_provider}}', 'realization_class', 'Класс реализации');

        $this->update('{{%edi_provider}}', ['provider_class' => 'EcomProvider', 'realization_class' => 'EcomRealization'], ['name' => 'Ecom']);
        $this->update('{{%edi_provider}}', ['provider_class' => 'KorusProvider', 'realization_class' => 'KorusRealization'], ['name' => 'Korus']);
        $this->update('{{%edi_provider}}', ['provider_class' => 'LeradataProvider', 'realization_class' => 'LeradataRealization'], ['name' => 'Leradata']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%edi_organization}}', 'provider_id');
        $this->dropColumn('{{%edi_organization}}', 'provider_priority');

        $this->dropColumn('{{%edi_provider}}', 'provider_class');
        $this->dropColumn('{{%edi_provider}}', 'realization_class');

        $this->dropColumn('{{%roaming_map}}', 'provider_id');
    }
}
