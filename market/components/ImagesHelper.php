<?php
namespace market\components;

use yii\base\Component;
use Yii;
use yii\helpers\Url;

class ImagesHelper extends Component
{
    public static function getUrl($category_id)
    {
        if(file_exists(Yii::getAlias('@web').'/fmarket/images/image-category/' . $category_id . ".jpg"))
            return  Url::to('@web/fmarket/images/image-category/' . $category_id . ".jpg", true);
        else
            return Url::to('@web/fmarket/images/product_placeholder.jpg', true);
    }
}