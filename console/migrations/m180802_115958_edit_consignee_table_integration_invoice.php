<?php

use yii\db\Migration;

class m180802_115958_edit_consignee_table_integration_invoice extends Migration
{
    public function safeUp()
    {
        $rows = (new \yii\db\Query())->select(['id','organization_id','consignee'])->from('integration_invoice')->all();;
        foreach ($rows as $row) {
            $id = $row['id'];
            $org_id = $row['organization_id'];
            $consignee = $row['consignee'];
            if ($consignee===NULL) {
                $rows2 =  (new \yii\db\Query())->select(['name'])->from('organization')->where('id=:id',['id' => $org_id])->one();
                $this->update('{{%integration_invoice}}',['consignee' => $rows2['name']],['id' =>$id]);
            }
        }
    }

    public function safeDown()
    {
        echo "m180802_115958_edit_consignee_table_integration_invoice cannot be reverted.\n";

        return false;
    }

}
