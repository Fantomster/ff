<?php

use yii\db\Migration;

/**
 * Class m181114_134302_add_waybill_rk_doc_rid
 */
class m181114_134302_add_waybill_rk_doc_rid extends Migration
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
        $this->addColumn(
            \common\models\Waybill::tableName(),
            'outer_document_id',
            $this->string(20)
                ->null()
                ->comment('Поле для записи номера документа, который создан во внешней у.с. в случае успешной выгрузки')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn(\common\models\Waybill::tableName(), 'outer_document_id');
    }
}
