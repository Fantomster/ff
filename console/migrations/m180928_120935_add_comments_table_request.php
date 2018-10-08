<?php

use yii\db\Migration;

class m180928_120935_add_comments_table_request extends Migration
{

    public function safeUp()
    {
        $this->execute('alter table `request` comment "Таблица сведений о заявках ресторанов";');
        $this->addCommentOnColumn('{{%request}}', 'id', 'Идентификатор записи в таблице');
        $this->addCommentOnColumn('{{%request}}', 'category','Идентификатор категории товара');
        $this->addCommentOnColumn('{{%request}}', 'product','Наименование продукта-товара');
        $this->addCommentOnColumn('{{%request}}', 'comment','Комментарий к заявке');
        $this->addCommentOnColumn('{{%request}}', 'regular','Периодичность данной заявки (1 - разово, 2 - ежедневно, 3 - еженедельно, 4 - ежемесячно)');
        $this->addCommentOnColumn('{{%request}}', 'amount','Количество товара');
        $this->addCommentOnColumn('{{%request}}', 'rush_order','Показатель срочности заявки (0 - не срочная, 1 - срочная)');
        $this->addCommentOnColumn('{{%request}}', 'payment_method','Способ платежа (1 - наличный расчёт, 2 - безналичный расчёт)');
        $this->addCommentOnColumn('{{%request}}', 'deferment_payment','Отсрочка платежа');
        $this->addCommentOnColumn('{{%request}}', 'responsible_supp_org_id','Идентификатор организации-поставщика, взявшего на себя ответственность за выполнение заявки ресторана');
        $this->addCommentOnColumn('{{%request}}', 'count_views','Количество просмотров заказа');
        $this->addCommentOnColumn('{{%request}}', 'created_at','Дата и время создания записи в таблице');
        $this->addCommentOnColumn('{{%request}}', 'end','Дата и время окончания действия заявки ресторана');
        $this->addCommentOnColumn('{{%request}}', 'rest_org_id','Идентификатор организации-ресторана, откуда поступила заявка');
        $this->addCommentOnColumn('{{%request}}', 'active_status','Показатель статуса активности заявки (0 - не активна, 1 - активна)');
        $this->addCommentOnColumn('{{%request}}', 'rest_user_id','Идентификатор пользователя, сотрудника ресторана, создавшего заявку');
    }

    public function safeDown()
    {
        $this->execute('alter table `request` comment "";');
        $this->dropCommentFromColumn('{{%request}}', 'id');
        $this->dropCommentFromColumn('{{%request}}', 'category');
        $this->dropCommentFromColumn('{{%request}}', 'product');
        $this->dropCommentFromColumn('{{%request}}', 'comment');
        $this->dropCommentFromColumn('{{%request}}', 'regular');
        $this->dropCommentFromColumn('{{%request}}', 'amount');
        $this->dropCommentFromColumn('{{%request}}', 'rush_order');
        $this->dropCommentFromColumn('{{%request}}', 'payment_method');
        $this->dropCommentFromColumn('{{%request}}', 'deferment_payment');
        $this->dropCommentFromColumn('{{%request}}', 'responsible_supp_org_id');
        $this->dropCommentFromColumn('{{%request}}', 'count_views');
        $this->dropCommentFromColumn('{{%request}}', 'created_at');
        $this->dropCommentFromColumn('{{%request}}', 'end');
        $this->dropCommentFromColumn('{{%request}}', 'rest_org_id');
        $this->dropCommentFromColumn('{{%request}}', 'active_status');
        $this->dropCommentFromColumn('{{%request}}', 'rest_user_id');
    }
}
