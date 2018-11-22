<?php

use yii\db\Migration;

/**
 * Class m181122_082553_fill_outer_units_for_edi
 */
class m181122_082553_fill_outer_units_for_edi extends Migration
{

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
        $allService = \common\models\AllService::findOne(['denom' => 'EDI']);
        if (!$allService) {
            return false;
        }
        $serviceID = $allService->id;
        $ediOrganization = \common\models\edi\EdiOrganization::find()->one();
        if (!$ediOrganization) {
            return false;
        }
        $orgID = $ediOrganization->organization_id;

        $columns = ['name', 'outer_uid', 'service_id', 'org_id'];
        $array = [
            ["г", "GRM", $serviceID, $orgID],
            ["кг", "KGM", $serviceID, $orgID],
            ["л", "LTR", $serviceID, $orgID],
            ["мм", "MMT", $serviceID, $orgID],
            ["м2", "MTK", $serviceID, $orgID],
            ["м3", "MTQ", $serviceID, $orgID],
            ["м", "MTR", $serviceID, $orgID],
            ["мг", "MGM", $serviceID, $orgID],
            ["мл", "MLT", $serviceID, $orgID],
            ["мм3", "MMQ", $serviceID, $orgID],
            ["шт", "PCE", $serviceID, $orgID],
            ["кор", "CT", $serviceID, $orgID],
            ["пач", "BH", $serviceID, $orgID],
            ["пд", "PF", $serviceID, $orgID],
            ["упк", "PK", $serviceID, $orgID],
            ["бут", "BO", $serviceID, $orgID],
            ["кон", "CON", $serviceID, $orgID],
            ["кор", "CT", $serviceID, $orgID],
            ["меш", "BG", $serviceID, $orgID],
            ["набор", "SET", $serviceID, $orgID],
            ["пак", "PA", $serviceID, $orgID],
            ["ящ", "CR", $serviceID, $orgID],
            ["рулон", "RO", $serviceID, $orgID],
        ];
        $this->batchInsert('{{%outer_unit}}', $columns, $array);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181122_082553_fill_outer_units_for_edi cannot be reverted.\n";

        return false;
    }
    */
}
