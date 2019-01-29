<?php

use yii\db\Migration;

/**
 * Class m190124_073612_add_iiko_product_type_dictionary
 */
class m190124_073612_add_iiko_product_type_dictionary extends Migration
{

    public $table = '{{%outer_product_type_selected}}';

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

        $this->createTable($this->table, [
            'id'                    => $this->primaryKey(),
            'outer_product_type_id' => $this->integer()->notNull(),
            'org_id'                => $this->integer()->notNull(),
            'selected'              => $this->tinyInteger()->defaultValue(0)
        ]);

        $this->dropColumn(\common\models\OuterProductType::tableName(), 'selected');

        $dictionary = new \common\models\OuterDictionary();
        $dictionary->service_id = \api_web\components\Registry::IIKO_SERVICE_ID;
        $dictionary->name = 'product_type';
        if ($dictionary->save()) {
            $agentDictionary = \common\models\OuterDictionary::find()->where([
                'service_id' => \api_web\components\Registry::IIKO_SERVICE_ID,
                'name'       => 'agent'
            ])->one();

            $goodsType = \common\models\OuterProductType::find()->where([
                'service_id' => \api_web\components\Registry::IIKO_SERVICE_ID,
                'value'      => 'GOODS'
            ])->one();

            $organizationOnlyIiko = (new \yii\db\Query())
                ->select('org_id')
                ->from(\common\models\OrganizationDictionary::tableName())
                ->where(['outer_dic_id' => $agentDictionary->id])
                ->column($this->db);

            $date = new \yii\db\Expression('NOW()');
            $rows = [];
            $rows2 = [];
            foreach ($organizationOnlyIiko as $org_id) {

                $rows[] = [
                    $dictionary->id,
                    $org_id,
                    1,
                    10,
                    $date,
                    $date
                ];

                $rows2[] = [
                    $goodsType->id,
                    $org_id,
                    1
                ];
            }

            $this->batchInsert(\common\models\OrganizationDictionary::tableName(), [
                'outer_dic_id',
                'org_id',
                'status_id',
                'count',
                'created_at',
                'updated_at'
            ], $rows);

            $this->batchInsert($this->table, [
                'outer_product_type_id',
                'org_id',
                'selected'
            ], $rows2);

        } else {
            print_r($dictionary->getFirstErrors());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190124_073612_add_iiko_product_type_dictionary cannot be reverted.\n";

        return true;
    }
}
