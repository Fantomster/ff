<?php

namespace common\models\search;

use common\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Organization;
use yii\data\ArrayDataProvider;

/**
 * Description of ClientSearch
 *
 * @author sharaf
 */
class BusinessSearch extends Organization {

    public $searchString;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['searchString'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params) {
        $this->load($params);
        $searchString = ($this->searchString == '') ? null : $this->searchString;
        $dataProvider = User::getAllOrganizationsDataProvider($searchString, true);
        return $dataProvider;
    }

}
