<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class FavoriteSearch extends \common\models\search\FavoriteSearch
{
    public $count;
    public $page;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['page','count'], 'integer'],
                [['searchString', 'id', 'product', 'order.created_at'], 'safe'],
        ];
    }

}
