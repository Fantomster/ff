<?php

use yii\db\Migration;

/**
 * Class m180814_182425_update_one_s_dic_table
 */
class m180814_182425_update_one_s_dic_table extends Migration
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
        $oneSDics = \api\common\models\one_s\OneSDic::find()->all();
        foreach ($oneSDics as &$dic){
            $count = 0;
            switch ($dic->dictype_id){
                case 1:
                    $count = \api\common\models\one_s\OneSContragent::find()->where(['org_id' => $dic->org_id])->count();
                    break;
                case 2:
                    $count = \api\common\models\one_s\OneSStore::find()->where(['org_id' => $dic->org_id])->count();
                    break;
                case 3:
                    $count = \api\common\models\one_s\OneSGood::find()->where(['org_id' => $dic->org_id])->count();
                    break;
            }
            $dic->dicstatus_id = 1;
            $dic->obj_count = $count;
            $dic->save();
        }
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
        echo "m180814_182425_update_one_s_dic_table cannot be reverted.\n";

        return false;
    }
    */
}
