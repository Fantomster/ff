<?php

use yii\db\Migration;

/**
 * Class m180323_092459_update_notification
 */
class m180323_092459_update_notification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
            $res = \common\models\notifications\EmailNotification::find()->all();

            foreach ($res as $row)
            {
                $rels = \common\models\RelationUserOrganization::findAll(['user_id' => $row->user_id]);
                foreach ($rels as $rel)
                {
                    $model = new \common\models\notifications\EmailNotification();
                    $model->attributes = $row->attributes;
                    $model->rel_user_org_id = $rel->id;
                    if($rel->organization->type_id == \common\models\Organization::TYPE_SUPPLIER)
                        $model->order_done = 0;
                    $model->save();
                }
                $row->delete();
            }

        $res = \common\models\notifications\SmsNotification::find()->all();

        foreach ($res as $row)
        {
            $rels = \common\models\RelationUserOrganization::findAll(['user_id' => $row->user_id]);
            foreach ($rels as $rel)
            {
                $model = new \common\models\notifications\SmsNotification();
                $model->attributes = $row->attributes;
                $model->rel_user_org_id = $rel->id;
                $model->save();
            }
            $row->delete();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m180323_092459_update_notification cannot be reverted.\n";

        return false;
    }
}
