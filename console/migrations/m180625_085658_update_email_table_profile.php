<?php

use yii\db\Migration;
use yii\db\Query;

class m180625_085658_update_email_table_profile extends Migration
{

    public function safeUp()
    {
        $rows =  (new \yii\db\Query())->select(['id','user_id'])->from('profile')->all();
        foreach ($rows as $row) {
            $id = $row['id'];
            $user_id = $row['user_id'];
            $rows2 =  (new \yii\db\Query())->select(['email'])->from('user')->where('id=:id',['id' => $user_id])->one();
            $email = $rows2['email'];
            $this->update('{{%profile}}',
                ['email' => $email],
                ['id' =>$id]
        );
        }
    }

    public function safeDown()
    {
        $this->update('{{%profile, }}', array(
            'email' => null));
    }

}

