<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class OrderCatalogSearch extends \common\models\search\OrderCatalogSearch
{
    public $count;
    public $page;
    

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['page','count'], 'integer']
        ];
    }

}
