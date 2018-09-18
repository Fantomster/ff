<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/18/2018
 * Time: 10:56 AM
 */

namespace api_web\modules\integration\classes;


class Integration
{
    static $service_map = [
        2 => 'Iiko',

    ];

    public function __construct($serviceId)
    {
        $this->service_id = $serviceId;
        $this->serviceName = self::$service_map[$serviceId];
    }

    public function getDict($type){
        $_ = $this->getDictName($type);
        return new $_($this->service_id);
    }

    private function getDictName($type){
        return "api_web\modules\integration\classes\dictionaries\\" . $this->serviceName . $type;
    }


}