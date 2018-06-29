<?php

use yii\db\Migration;

class m180629_081219_add_comments_table_organization extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `organization` comment "Таблица сведений об организациях";');
        $this->addCommentOnColumn('{{%organization}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%organization}}', 'type_id', 'Идентификатор типа этой организации');
        $this->addCommentOnColumn('{{%organization}}', 'name', 'Название организации (бизнеса)');
        $this->addCommentOnColumn('{{%organization}}', 'city', 'Город, где располагается организация');
        $this->addCommentOnColumn('{{%organization}}', 'address', 'Адрес организации');
        $this->addCommentOnColumn('{{%organization}}', 'zip_code', 'Почтовый индекс организации');
        $this->addCommentOnColumn('{{%organization}}', 'phone', 'Телефон организации');
        $this->addCommentOnColumn('{{%organization}}', 'email', 'Е-мэйл организации (не используется)');
        $this->addCommentOnColumn('{{%organization}}', 'website', 'Ссылка на веб-сайт организации');
        $this->addCommentOnColumn('{{%organization}}', 'created_at', 'Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%organization}}', 'updated_at', 'Дата и время последнего изменения записи в таблице');
        $this->addCommentOnColumn('{{%organization}}', 'step', 'Показатель прохождения этапов регистрации');
        $this->addCommentOnColumn('{{%organization}}', 'legal_entity', 'Полное название юридического лица');
        $this->addCommentOnColumn('{{%organization}}', 'contact_name', 'Имя контакного лица организации');
        $this->addCommentOnColumn('{{%organization}}', 'about', 'Примечание-описание организации');
        $this->addCommentOnColumn('{{%organization}}', 'picture', 'Ссылка на картинку-аватар организации');
        $this->addCommentOnColumn('{{%organization}}', 'es_status', 'Показатель необходимости использования морфологического описка (ElasticSearch) для организации');
        $this->addCommentOnColumn('{{%organization}}', 'rating', 'Рейтинг организации в системе (пока не используется)');
        $this->addCommentOnColumn('{{%organization}}', 'white_list', 'Показатель нахождения организации в "белом списке" (0 - не находится, 1 - находится)');
        $this->addCommentOnColumn('{{%organization}}', 'partnership', 'Показатель, явлдяется ли организация нашим партнёром (0 - не является, 1  - является)');
        $this->addCommentOnColumn('{{%organization}}', 'lat', 'Географическая координата (широта)');
        $this->addCommentOnColumn('{{%organization}}', 'lng', 'Географическая координата (долгота)');
        $this->addCommentOnColumn('{{%organization}}', 'country', 'Страна нахождения организации  (по базе из Google)');
        $this->addCommentOnColumn('{{%organization}}', 'locality', 'Населённый пункт (город), где находится организация (по базе из Google)');
        $this->addCommentOnColumn('{{%organization}}', 'route', 'Название улицы, где находится организация (по базе из Google)');
        $this->addCommentOnColumn('{{%organization}}', 'street_number', 'Номер дома, где находится организация (по базе из Google)');
        $this->addCommentOnColumn('{{%organization}}', 'place_id', 'Показатель местонахождения организации (по базе из Google)');
        $this->addCommentOnColumn('{{%organization}}', 'formatted_address', 'Отформатированный адрес, где находится организация');
        $this->addCommentOnColumn('{{%organization}}', 'administrative_area_level_1', 'Административный регион (1-й уровень), где находится организация');
        $this->addCommentOnColumn('{{%organization}}', 'franchisee_sorted', 'Показатель сортировки франчайзеров по регионам');
        $this->addCommentOnColumn('{{%organization}}', 'blacklisted', 'Показатель нахождения организации в "чёрном списке" (0 - не находится, 1 - находится)');
        $this->addCommentOnColumn('{{%organization}}', 'parent_id', 'Идентификатор организации, к которой этот бизнес относится');
        $this->addCommentOnColumn('{{%organization}}', 'manager_id', 'Идентификатор менеджера Микскарта, ответственного за организацию (не используется)');
        $this->addCommentOnColumn('{{%organization}}', 'is_allowed_for_franchisee', 'Показатель состояния согласия на франшизу (0 - не согласен, 1 - согласен)');
        $this->addCommentOnColumn('{{%organization}}', 'is_work', 'Показатель, что организщация является коммерческим клиентом (устанавливается в админке)');
        $this->addCommentOnColumn('{{%organization}}', 'inn', 'ИНН организации');
    }

    public function safeDown()
    {
        $this->execute('alter table `organization` comment "";');
        $this->dropCommentFromColumn('{{%organization}}', 'id');
        $this->dropCommentFromColumn('{{%organization}}', 'type_id');
        $this->dropCommentFromColumn('{{%organization}}', 'name');
        $this->dropCommentFromColumn('{{%organization}}', 'city');
        $this->dropCommentFromColumn('{{%organization}}', 'address');
        $this->dropCommentFromColumn('{{%organization}}', 'zip_code');
        $this->dropCommentFromColumn('{{%organization}}', 'phone');
        $this->dropCommentFromColumn('{{%organization}}', 'email');
        $this->dropCommentFromColumn('{{%organization}}', 'website');
        $this->dropCommentFromColumn('{{%organization}}', 'created_at');
        $this->dropCommentFromColumn('{{%organization}}', 'updated_at');
        $this->dropCommentFromColumn('{{%organization}}', 'step');
        $this->dropCommentFromColumn('{{%organization}}', 'legal_entity');
        $this->dropCommentFromColumn('{{%organization}}', 'contact_name');
        $this->dropCommentFromColumn('{{%organization}}', 'about');
        $this->dropCommentFromColumn('{{%organization}}', 'picture');
        $this->dropCommentFromColumn('{{%organization}}', 'es_status');
        $this->dropCommentFromColumn('{{%organization}}', 'rating');
        $this->dropCommentFromColumn('{{%organization}}', 'white_list');
        $this->dropCommentFromColumn('{{%organization}}', 'partnership');
        $this->dropCommentFromColumn('{{%organization}}', 'lat');
        $this->dropCommentFromColumn('{{%organization}}', 'lng');
        $this->dropCommentFromColumn('{{%organization}}', 'country');
        $this->dropCommentFromColumn('{{%organization}}', 'locality');
        $this->dropCommentFromColumn('{{%organization}}', 'route');
        $this->dropCommentFromColumn('{{%organization}}', 'street_number');
        $this->dropCommentFromColumn('{{%organization}}', 'place_id');
        $this->dropCommentFromColumn('{{%organization}}', 'formatted_address');
        $this->dropCommentFromColumn('{{%organization}}', 'administrative_area_level_1');
        $this->dropCommentFromColumn('{{%organization}}', 'franchisee_sorted');
        $this->dropCommentFromColumn('{{%organization}}', 'blacklisted');
        $this->dropCommentFromColumn('{{%organization}}', 'parent_id');
        $this->dropCommentFromColumn('{{%organization}}', 'manager_id');
        $this->dropCommentFromColumn('{{%organization}}', 'is_allowed_for_franchisee');
        $this->dropCommentFromColumn('{{%organization}}', 'is_work');
        $this->dropCommentFromColumn('{{%organization}}', 'inn');
    }
}
