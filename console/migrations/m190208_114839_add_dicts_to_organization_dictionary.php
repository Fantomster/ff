<?php

use api_web\components\Registry;
use api_web\exceptions\ValidationException;
use common\models\OrganizationDictionary;
use common\models\OuterDictionary;
use common\models\vetis\VetisBusinessEntity;
use common\models\vetis\VetisForeignEnterprise;
use common\models\vetis\VetisRussianEnterprise;
use yii\db\Migration;

/**
 * Class m190208_114839_add_dicts_to_organization_dictionary
 */
class m190208_114839_add_dicts_to_organization_dictionary extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $arMap = [
            'businessEntity'    => VetisBusinessEntity::find()->count(),
            'russianEnterprise' => VetisRussianEnterprise::find()->count(),
            'foreignEnterprise' => VetisForeignEnterprise::find()->count(),
        ];
        $arDictionaryIds = OuterDictionary::find()
            ->where(['service_id' => (int)Registry::MERC_SERVICE_ID])
            ->all();

        /**@var OuterDictionary $dictionary */
        foreach ($arDictionaryIds as $dictionary) {
            if (!in_array($dictionary->name, ['productItem', 'transport'])) {
                $orgDic = new OrganizationDictionary([
                    'outer_dic_id' => $dictionary->id,
                    'org_id'       => 1,
                    'status_id'    => OrganizationDictionary::STATUS_ACTIVE,
                    'count'        => $arMap[$dictionary->name],
                ]);

                if (!$orgDic->save()) {
                    throw new ValidationException($orgDic->getFirstErrors());
                }
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190208_114839_add_dicts_to_organization_dictionary cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190208_114839_add_dicts_to_organization_dictionary cannot be reverted.\n";

        return false;
    }
    */
}
