<?php

use yii\db\Migration;

/**
 * Class m180418_092208_dev_808_offline_vendors_create_catalogs
 */
class m180418_092208_dev_808_offline_vendors_create_catalogs extends Migration
{

    public function safeUp()
    {
        $query = (new \yii\db\Query())->select(['org.id']);
        $query->from('organization AS org');
        $query->leftJoin('catalog AS c', 'c.supp_org_id = org.id AND c.type = 1');
        $query->where("c.id is null AND org.type_id = 2");

        if (!empty($query->all())) {
            foreach ($query->all() as $vendor) {
                $this->createBaseCatalog($vendor['id']);
                //Поиск всех связей с ресторанами, у кого не назначен каталог
                $relations = \common\models\RelationSuppRest::find()
                    ->where(['supp_org_id' => $vendor['id']])
                    ->andWhere(['cat_id' => 0])
                    ->all();

                if (!empty($relations)) {
                    foreach ($relations as $relation) {
                        if (!$this->createCatalog($relation)) {
                            throw new \Exception('Not save Relation');
                        }
                    }
                }
            }
        }
    }

    /**
     * Создание главного каталога
     * @param $vendor_id
     */
    private function createBaseCatalog($vendor_id)
    {
        $this->insert('catalog', [
            'type' => 1,
            'supp_org_id' => $vendor_id,
            'status' => 1,
            'created_at' => new \yii\db\Expression('NOW()'),
            'name' => 'Главный каталог!'
        ]);
    }

    /**
     * Создание индивидуального каталога
     * @param \common\models\RelationSuppRest $relation
     * @return bool
     * @throws Exception
     */
    private function createCatalog(\common\models\RelationSuppRest $relation)
    {
        $model = new \common\models\Catalog([
            'type' => 2,
            'supp_org_id' => $relation->vendor->id,
            'status' => 1,
            'created_at' => new \yii\db\Expression('NOW()'),
            'name' => $relation->client->name . ' #1'
        ]);

        if ($model->save()) {
            $relation->cat_id = $model->id;
            $relation->invite = 1;
            if ($relation->save()) {
                return true;
            }
        } else {
            throw new \Exception('Not save Model Catalog');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180418_092208_dev_808_offline_vendors_create_catalogs cannot be reverted.\n";
    }
}
