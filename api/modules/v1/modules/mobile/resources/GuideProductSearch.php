<?php

namespace api\modules\v1\modules\mobile\resources;

/**
 * @author Eugene Terentev <eugene@terentev.net>
 */
class GuideProductSearch extends \common\models\search\GuideProductsSearch
{
    public $count;
    public $page;
    

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['page','count'], 'integer'],
                [['searchString', 'guide_id', 'cbg_id', 'name'], 'safe'],
        ];
    }

}
