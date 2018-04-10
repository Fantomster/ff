<?php

use yii\db\Migration;
use common\models\SourceMessage;
use common\models\Message;

/**
 * Class m180329_080843_add_translate_messages_two
 */
class m180329_080843_add_translate_messages_two extends Migration
{

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['app','frontend.views.guides.sort_by']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'app', 'message' => 'frontend.views.guides.sort_by']) -> id;

        if(Message::findOne(['id' => $row_id]) == null){
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', 'Sort by'],
                [$row_id,'es', 'Ordenar por'],
                [$row_id,'ru', 'Сортировка по'],
                [$row_id,'md', 'Sortați după'],
            ]);
        }

        /*************************************************************************************/
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['app','frontend.views.guides.sort_by_time_asc']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'app', 'message' => 'frontend.views.guides.sort_by_time_asc']) -> id;

        if(Message::findOne(['id' => $row_id]) == null){
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', 'Order of adding in ascending order'],
                [$row_id,'es', 'Orden de agregar en orden ascendente'],
                [$row_id,'ru', 'Порядку добавления по возрастанию'],
                [$row_id,'md', 'Ordine de adăugare în ordine ascendentă'],
            ]);
        }

        /************************************************************/
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['app','frontend.views.guides.sort_by_time_desc']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'app', 'message' => 'frontend.views.guides.sort_by_time_desc']) -> id;

        if(Message::findOne(['id' => $row_id]) == null){
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', 'Order of adding in descending order'],
                [$row_id,'es', 'Orden de agregar en orden descendente'],
                [$row_id,'ru', 'Порядку добавления по убыванию'],
                [$row_id,'md', 'Ordine de adăugare în ordine descrescătoare'],
            ]);
        }

        /************************************************************/
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['app','frontend.views.guides.sort_by_name_asc']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'app', 'message' => 'frontend.views.guides.sort_by_name_asc']) -> id;

        if(Message::findOne(['id' => $row_id]) == null){
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', 'Name in increasing order'],
                [$row_id,'es', 'Nombre en orden creciente'],
                [$row_id,'ru', 'Наименованию по возрастанию'],
                [$row_id,'md', 'Nume în ordine crescătoare'],
            ]);
        }

        /************************************************************/
        $this->batchInsert('{{%source_message}}', ['category', 'message'], [
            ['app','frontend.views.guides.sort_by_name_desc']
        ]);

        $row_id = SourceMessage::findOne(['category'=>'app', 'message' => 'frontend.views.guides.sort_by_name_desc']) -> id;

        if(Message::findOne(['id' => $row_id]) == null){
            $this->batchInsert('{{%message}}', ['id', 'language', 'translation'], [
                [$row_id,'en', 'Name in descending order'],
                [$row_id,'es', 'Nombre en orden descendente'],
                [$row_id,'ru', 'Наименованию по убыванию'],
                [$row_id,'md', 'Nume în ordine descrescătoare'],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return true;
    }
}
