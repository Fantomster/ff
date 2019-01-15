<?php
/**
 * Created by PhpStorm.
 * User: Konstantin Silukov
 * Date: 2018-12-03
 * Time: 11:26
 */

namespace api_web\classes;

use api_web\components\WebApi;
use common\models\Journal;
use common\models\Role;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;

/**
 * Class JournalWebApi
 *
 * @package api_web\classes
 */
class JournalWebApi extends WebApi
{

    /**
     * @param $request
     * @return array
     * @throws \yii\base\InvalidArgumentException
     */
    public function list($request)
    {
        $page = $request['pagination']['page'] ?? 1;
        $pageSize = $request['pagination']['page_size'] ?? 12;
        $sort = $post['sort'] ?? null;

        $query = Journal::find();
        if (isset($request['search'])) {
            $search = $request['search'];
            if (isset($search['date']) && !empty($search['date'])) {
                $date = $search['date'];
                if (isset($date['start']) && !empty($date['start'])) {
                    $query->andWhere('created_at >= :date_from',
                        [':date_from' => date('Y-m-d H:i:s', strtotime($date['start'] . ' 00:00:00'))]);
                }
                if (isset($date['end']) && !empty($date['end'])) {
                    $query->andWhere('created_at <= :date_to',
                        [':date_to' => date('Y-m-d H:i:s', strtotime($date['end'] . ' 23:59:59'))]);
                }
            }
            if (isset($search['service_id']) && !empty($search['service_id'])) {
                $query->andWhere(['service_id' => $search['service_id']]);
            }
            if (isset($search['type']) && !empty($search['type'])) {
                $query->andWhere(['type' => $search['type']]);
            }
        }

        if (isset($request['search']['user_id']) && !empty($request['search']['user_id'])) {
            $query->andWhere(['user_id' => $request['search']['user_id']]);
        } elseif ($this->user->role_id == Role::ROLE_RESTAURANT_MANAGER) {
            $query->andWhere(['organization_id' => $this->user->organization_id]);
        } else {
            $query->andWhere(['user_id' => $this->user->id]);
        }

        if ($sort) {
            if ($sort == 'response') {
                $query->orderBy('response ASC');
            } elseif ($sort == '-response') {
                $query->orderBy('response DESC');
            }
            if ($sort == 'created_at') {
                $query->orderBy('created_at ASC');
            } elseif ($sort == '-created_at') {
                $query->orderBy('created_at DESC');
            }
        } else {
            $query->orderBy('id DESC');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);

        $pagination = new Pagination();
        $pagination->setPage($page - 1);
        $pagination->setPageSize($pageSize);
        $dataProvider->setPagination($pagination);

        $result = [];
        foreach ($dataProvider->models as $model) {
            $result[] = $model;
        }

        $return = [
            'result'     => $result,
            'pagination' => [
                'page'       => ($dataProvider->pagination->page + 1),
                'page_size'  => $dataProvider->pagination->pageSize,
                'total_page' => ceil($dataProvider->totalCount / $pageSize)
            ]
        ];

        return $return;
    }

}