<?php

class MailCoreController extends MailBaseController{
    public function viewAction(){
        $mail = MailSearcher::Factory()->search_by_mail_instance_id($_GET['parameter'])->execute_one();
        //print_r($mail);
        echo $mail->get_message();
        exit;
    }
}