<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\helpers;

use common\models\RelationUserOrganization;

/**
 * Description of MailHelper
 *
 * @author El Babuino
 */
class MailHelper
{
    public static function isSelf($senderOrg, $recipient)
    {
        return (($senderOrg->id == $recipient->organization_id) || (isset($recipient->id) && (RelationUserOrganization::relationExists($recipient->id, $senderOrg->id))));
    }
    
}
