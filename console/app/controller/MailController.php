<?php
namespace Controller;
use SFW\Connection;

use Mailgun\Mailgun;
use RainTPL;

class Mail extends Connection 
{

    public $mailClient  = null;
    private $domain  = null;
    private $mailHtmlDir = "mails/";
    private $body = "You have changes in one smart please check it out";
    public function __construct() {
        parent::__construct();
        $this->setMailClient();
    }
    public function setMailClient($mailer=null) {
        $mailer = $mailer?$mailer:MAIL_DRIVER;
        switch (MAIL_DRIVER) {
            case 'MAILGUN':
                $this->setMailGunClient();
                break;
            default:
                $this->setMailGunClient();
                break;
        }
    }
    public function setMailGunClient()
    {
        $this->mailClient = new Mailgun(MAILGUN_SECRET);
        $this->domain = MAILGUN_DOMAIN;
    }
    public function dispatch($to, $subject = null, $from = null, $attachments=[]) {
        $content['to'] = $to;
        $content['from'] = $from?$from:'contact@onesmarter.in';
        $content['subject'] = $subject?$subject:'Mail from ones smart';

        if($this->isHtml($this->body)){
            $content['html'] = $this->body;
        }else{
            $content['text'] = $this->body;
        }
        $attachments['attachment'] = $attachments;

        $result = $this->mailClient->sendMessage($this->domain, $content, $attachments);
        return $result;
    }
    function isHtml($text) {
        return $text != strip_tags($text);
    }
    function getBody($body_location = 'testmail',$content) {
        $mailTpl = new RainTPL;
        $mailTpl->assign( "base_url", SITE_URL );
        $mailTpl->assign( "sub_file_url", SUB_FILE_URL );
        $mailTpl->assign( "data", $content );
        $mailTpl->assign( "site_name", SITE_NAME );
        $mailTpl->assign( "portal_name", PORTAL_NAME );
        
    
        $mailTpl->assign( "image_path", VIEWS_PATH .'assets/email/');
        

        $mailHtml   ="";
        // $mailHtml  .= $mailTpl->draw($this->mail_html_dir.$body_location, $return_string = true);

        return $mailHtml;
    }


   

   /*Send  email */
   public function sendEmail($to,$subject,$emailTemplate,$content='') {  
           $from         = "";
           $this->body   = $this->getBody($emailTemplate, $content);
           return  $this->dispatch($to,$subject);
   }



    public function send($type, ...$data) {
    }

    function emailFormater($emailTemplate,$emailConfig) {
        preg_match_all('/{{(.+?)}}/', $emailTemplate, $matches);
        foreach ($matches[1] as $key => $value) {
            $emailTemplate = str_replace("{{".$value."}}",$emailConfig[$value],$emailTemplate);
            //echo $value;
        }
        return $emailTemplate;
    }
}