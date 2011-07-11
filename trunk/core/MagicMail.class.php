<?php
class MagicMail{

    static public $transport = NULL;
    static public $mailer = NULL;

    private $to;
    private $from;
    private $subject;
    private $message;

    private $instant = false;

    private $attachments;

    private $html = false;

    static public function Factory(){
        return new MagicMail();
    }

    public function __construct(){
        if(MagicConfig::get("EMAIL_SMTP_HOST") && MagicConfig::get("EMAIL_SMTP_PORT")){
           if(MagicMail::$transport === NULL){
               MagicMail::$transport = Swift_SmtpTransport::newInstance()
                       ->setHost(MagicConfig::get("EMAIL_SMTP_HOST"))
                       ->setPort(MagicConfig::get('EMAIL_SMTP_PORT'))
                       ->setEncryption(MagicConfig::get('EMAIL_SMTP_SSL')?'ssl':null)
                       ->setUsername(MagicConfig::get('EMAIL_SMTP_USERNAME'))
                       ->setPassword(MagicConfig::get('EMAIL_SMTP_PASSWORD'));

               MagicMail::$mailer = Swift_Mailer::newInstance(MagicMail::$transport);

           }
        }
        $this->to = MagicConfig::get("ADMIN_EMAIL")?MagicConfig::get("ADMIN_EMAIL"):'geeks@turbocrms.com';
        $this->from = MagicConfig::get("SERVER_EMAIL")?MagicConfig::get("SERVER_EMAIL"):'geeks@turbocrms.com';
    }

    public function set_to($to){
        $this->to = $to;
        return $this;
    }
    public function set_from($from){
        $this->from = $from;
        return $this;
    }
    public function set_subject($subject){
        $this->subject = $subject;
        return $this;
    }
    public function set_message($message){
        $this->message = $message;
        return $this;
    }
   public function set_html($html_mode = false){
      $this->html = $html_mode;
      return $this;
   }
   public function attach_string_as_file($data,$file){
      $attachment = Swift_Attachment::newInstance()
        ->setFilename($file)
        ->setContentType('text/plain')
        ->setBody($data);
      $this->attachments[] = $attachment;
      return $this;

   }

    public function execute($instant = null){
       if($instant !== NULL){
          $this->instant = $instant;
       }
       $mail = Mail::Factory()
               ->add_to(explode(",",$this->to))
               ->add_from(explode(",",$this->from))
               ->set_subject($this->subject)
               ->set_message($this->message)
               ->set_type($this->html ? 'html' : 'text')
               ->set_attachments($this->attachments)
               ->save();
       if($this->instant){
          $mail->send()->save();
       }

       return;


       if(MagicMail::$transport === NULL){
          MagicLogger::log("Cannot send mail, no transport configured");
          return false;
       }
        $message = Swift_Message::newInstance(MagicMail::$transport)
                ->setSubject($this->subject)
                ->setFrom(explode(",",$this->from))
                ->setTo(explode(",",$this->to))
                ->setBody($this->message,$this->html?'text/html':'text/plain');
       foreach((array) $this->attachments as $attachment){
          $message->attach($attachment);
       }
       //print_r($message->getFrom());
       MagicLogger::log("Sending from ".implode(", ",array_keys($message->getFrom()))." to ".implode(", ",array_keys($message->getTo()))." Subject: '{$this->subject}'");
        if(MagicMail::$mailer->send($message)){
            MagicLogger::log("Sent mail!");
            return TRUE;
        }else{
            throw new MagicException("Cannot send mail :(");
        }
    }
}