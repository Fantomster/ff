<?php
use yii\db\Schema;
use yii\db\Migration;

class m161223_142619_insert_update_category extends Migration
{
    public function safeUp() {
        $this->update('{{%mp_category}}', ['name'=> 'Растительные масла'], ['id' => 122]);  
        $this->batchInsert('{{%mp_category}}', ['name','parent'], [
            ['масло авокадо',122],
            ['из абрикосовых косточек',122],
            ['арахисовое масло',122],
            ['Аргановое масло',122],
            ['из виноградных косточек',122],
            ['из косточек вишни',122],
            ['ореховое масло (из грецкого ореха)',122],
            ['горчичное масло',122],
            ['масло зародышей пшеницы',122],
            ['кедровое масло',122],
            ['конопляное масло',122],
            ['красное пальмовое масло',122],
            ['кукурузное масло',122],
            ['кунжутное масло',122],
            ['льняное масло',122],
            ['миндальное масло',122],
            ['облепиховое масло',122],
            ['пальмовое масло',122],
            ['подсолнечное масло',122],
            ['рапсовое масло',122],
            ['из рисовых отрубей',122],
            ['из расторопши пятнистой',122],
            ['рыжиковое масло',122],
            ['сафлоровое масло',122],
            ['соевое масло',122],
            ['тыквенное масло',122],
            ['хлопковое масло',122],
            ['масло шиповника',122],
            ['масло чёрного тмина',122],
            ['пихтовое масло',122]
        ]);
        $this->delete('{{%mp_category}}', ['id' => 125]);
    }  
}