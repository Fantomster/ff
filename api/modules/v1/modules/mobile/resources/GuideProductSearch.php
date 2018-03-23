<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GuideProductSearch extends \common\models\search\GuideProductsSearch
{
    public $count;
    public $page;
    public $guide_id;
    public $guide_list;
    

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['page','count', 'guide_id'], 'integer'],
                [['searchString', 'cbg_id', 'name', 'guide_list'], 'safe'],
        ];
    }

}
