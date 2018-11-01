<?php

use yii\db\Migration;
use api\common\models\iiko\iikoDicconst;
use api\common\models\iiko\iikoPconst;
use api\common\models\AllMaps;

class m181101_053919_add_daughters_products_table_all_map extends Migration
{
    public function safeUp()
    {
        $obConstModel = iikoDicconst::findOne(['denom' => 'main_org']); // Получаем идентификатор константы бизнеса для сопоставления
        $parents = iikoPconst::find()->select('value')->where(['const_id' => $obConstModel->id])->distinct()->all();
        foreach ($parents as $parent) {
            $parent_id = $parent->value;
            if (($parent_id == 0) or (is_null($parent_id))) continue;
            $arChildsModels = iikoPconst::find()->select('org')->where(['const_id' => $obConstModel->id, 'value' => $parent_id])->all(); //получаем дочерние бизнесы
            $allMainProducts = AllMaps::find()->select('service_id, product_id, supp_id, serviceproduct_id, koef, vat, is_active')->where(['org_id' => $parent_id, 'service_id' => 2])->all();
            foreach ($arChildsModels as $child) {
                foreach ($allMainProducts as $main_product) {
                    $child_product = AllMaps::find()->select('id, store_rid, vat')->where(['org_id' => $child->org, 'service_id' => 2, 'product_id' => $main_product->product_id])->one();
                    if ($child_product) {
                        $ChildProduct = AllMaps::findOne($child_product->id);
                        (is_null($child_product->store_rid)) ? $ChildProduct->store_rid = null : $ChildProduct->store_rid = $child_product->store_rid;
                        (is_null($child_product->vat)) ? $ChildProduct->vat = null : $ChildProduct->vat = $child_product->vat;
                    } else {
                        $ChildProduct = new AllMaps();
                        $ChildProduct->store_rid = null;
                        $ChildProduct->vat = $main_product->vat;
                    }
                    $ChildProduct->service_id = $main_product->service_id;
                    $ChildProduct->koef = $main_product->koef;
                    $ChildProduct->org_id = $child->org;
                    $ChildProduct->product_id = $main_product->product_id;
                    $ChildProduct->supp_id = $main_product->supp_id;
                    $ChildProduct->serviceproduct_id = $main_product->serviceproduct_id;
                    $ChildProduct->is_active = $main_product->is_active;
                    if (!is_null($ChildProduct->serviceproduct_id)) {
                        $ChildProduct->linked_at = Yii::$app->formatter->asDate(time(), 'yyyy-MM-dd HH:mm:ss');
                    }
                    $ChildProduct->save();
                }
            }
        }
    }

    public function safeDown()
    {
        echo "m181101_053919_add_daughters_products_table_all_map cannot be reverted.\n";
        return false;
    }
}
