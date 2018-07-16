<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VsdHttp
 *
 * @author elbabuino
 */
class VsdHttp {
    public $authLink = 'https://t2-mercury.vetrf.ru/hs/';
    public $vsdLink = 'https://t2-mercury.vetrf.ru/pub/operatorui?_language=ru&_action=showVetDocumentFormByUuid&uuid=';
    public $pdfLink = 'https://t2-mercury.vetrf.ru/hs/operatorui?printType=1&preview=false&_action=printVetDocumentList&_language=ru&isplayPreview=false&displayRecipient=true&transactionPk=&vetDocument=&batchNumber=&printPk=';
    public $username;
    public $password;
    private $session = 'vsd-http-cookie';
    
    private function getCookie() {
        if (!isset())
    }
}
