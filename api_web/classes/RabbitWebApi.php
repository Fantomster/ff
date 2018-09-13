<?php
/**
 * Created by PhpStorm.
 * User: Fanto
 * Date: 9/13/2018
 * Time: 1:32 PM
 */

namespace api_web\classes;


use api_web\components\WebApi;

class RabbitWebApi extends WebApi
{
    public function dispatch($request){
        if (!empty($request['queue'])){
            $this->${$request['queue']};
        }
    }

    
}