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
    private $body = "";
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
        $this->mailClient = Mailgun::create(MAILGUN_SECRET);
        $this->domain = MAILGUN_DOMAIN;
    }
    public function dispatch($to, $subject = null, $from = null, $attachments=[]) {
        $content['to'] = $to;
        $content['from'] = $from?$from:MAILGUN_FROM_EMAIL;
        $content['subject'] = $subject?$subject:MAILGUN_DEFAULT_SUBJECT;

        if($this->isHtml($this->body)){
            $content['html'] = $this->body;
        }else{
            $content['text'] = $this->body;
        }
        $attachments['attachment'] = $attachments;

        $result = $this->mailClient->sendMessage($this->domain, $content, $attachments);
        return $result;
    }
    public function isHtml($text) {
        return $text != strip_tags($text);
    }
    public function getBody($content) : RainTPL {
        $mailTpl = new RainTPL;
        $mailTpl->assign( "baseUrl", SITE_URL );
        $mailTpl->assign( "subFileUrl", VIEWS_PATH );
        $mailTpl->assign( "data", $content );
        $mailTpl->assign( "siteName", SITE_NAME );
        $mailTpl->assign( "portalName", PORTAL_NAME );
        
    
        $mailTpl->assign( "imagePath", IMAGES_PATH .'email/');
    
        return $mailTpl;
    }


   

    /*Send  email */
    public function sendEmail($to,$subject,$emailTemplate,$content='') {  
        $this->body   = $this->getBody($emailTemplate, $content);
        return  $this->dispatch($to,$subject);
    }

    public function emailFormater($emailTemplate,$emailConfig) {
        preg_match_all('/{{(.+?)}}/', $emailTemplate, $matches);
        foreach ($matches[1] as $key => $value) {
            $emailTemplate = str_replace("{{".$value."}}",$emailConfig[$value],$emailTemplate);
            //echo $value;
        }
        return $emailTemplate;
    }
}