<?php

namespace frontend\modules\clientintegr\modules\merc\helpers\api\regionalization;

use frontend\modules\clientintegr\modules\merc\helpers\api\baseApi;

class regionalizationApi extends baseApi
{

    public function init()
    {
        $this->system = 'regianalization';
        $this->wsdlClassName = Regionalization::class;
        $_ = new \frontend\modules\clientintegr\modules\merc\helpers\api\regionalization\Regionalization();
        parent::init(); // TODO: Change the autogenerated stub
    }

}
