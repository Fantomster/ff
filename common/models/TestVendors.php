<?php

namespace common\models;

use common\models\guides\GuideProduct;
use common\models\guides\Guide;

/**
 * This is the model class for table "relation_supp_rest".
 *
 * @property integer $id
 * @property integer $manager_id
 * @property integer $leader_id
 */
class TestVendors extends \yii\db\ActiveRecord {


    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'test_vendors';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['vendor_id'], 'integer'],
            [['guide_name'], 'string'],
            [['is_active'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'vendor_id' => 'Vendor ID',
            'guide_name' => 'Название гайда',
            'is_active' => 'Активен?',
        ];
    }


    public function getOrganization(){
        return $this->hasOne(Organization::className(), ['id'=>'vendor_id']);
    }


    public function getColorsArray():array
    {
        $colorsArray = [
            'D81B60',
            '8E24AA',
            '5E35B1',
            '5C6BC0',
            '039BE5',
            '009688',
            'C0CA33',
            'FFD600',
            'FB8C00',
            'F4511E',
            'D32F2F',
            'A1887F',
            '5D4037',
            'BDBDBD',
            '757575',
            '000000',
        ];
        return $colorsArray;
    }

    public function setGuides($client){
        $guides = \common\models\guides\Guide::findAll(['client_id'=>$client->id]);
        if(!$guides) {
            $testVendors = TestVendors::findAll(['is_active' => 1]);
            foreach ($testVendors as $testVendor) {
                $vendor = Organization::findOne(['id' => $testVendor->vendor_id]);
                $colorsArray = self::getColorsArray();
                $now = new \yii\db\Expression('NOW()');

                $guide = new Guide();
                $guide->client_id = $client->id;
                $guide->type = 2;
                $guide->name = $testVendor->guide_name;
                $guide->color = $colorsArray[array_rand($colorsArray)];
                $guide->created_at = $now;
                $guide->updated_at = $now;
                $guide->save();
                $guide_id = $guide->id;

                $baseCatId = $vendor->baseCatalog->id;
                $rel = RelationSuppRest::findOne(['rest_org_id' => $client->id, 'supp_org_id' => $testVendor->vendor_id, 'cat_id' => $baseCatId]);
                if (!$rel) {
                    $rel = new RelationSuppRest();
                    $rel->rest_org_id = $client->id;
                    $rel->supp_org_id = $testVendor->vendor_id;
                    $rel->cat_id = $baseCatId;
                    $rel->invite = 1;
                    $rel->created_at = $now;
                    $rel->updated_at = $now;
                    $rel->status = 1;
                    $rel->save();
                }

                $baseGoods = CatalogBaseGoods::findAll(['cat_id' => $baseCatId]);
                foreach ($baseGoods as $good) {
                    $guide_product = new GuideProduct();
                    $guide_product->guide_id = $guide_id;
                    $guide_product->cbg_id = $good->id;
                    $guide_product->created_at = $now;
                    $guide_product->updated_at = $now;
                    $guide_product->save();
                }
            }
        }
    }

}
