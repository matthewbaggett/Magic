<?php
/*    +-----------------------------------------------------------------------+
 *    | Socializr & Magic Framework                                           |
 *    +-----------------------------------------------------------------------+
 *    | Copyright (c) 2009-2011 The Magic Group                               |
 *    +-----------------------------------------------------------------------+
 *    | This source file is the property of The Magic Group (hereby known as  |
 *    | "Us", or "we". We're a nice bunch :) We're an approachable  lot       |
 *    | for licencing~                                                        |
 *    |                                                                       |
 *    | You can contact us with one of the emails below:                      |
 *    +-----------------------------------------------------------------------+
 *    | Authors: Matthew Baggett <matthew@baggett.me>                         |
 *    |          Magic Generator <hello@turbocrms.com>                        |
 *    +-----------------------------------------------------------------------+
 */
// $Id:$
/**
 * Object generated June 17, 2011, 10:29:55 am.
 */
class MailCoreObject extends MailBaseObject implements MailInterface
{

	static protected $mailer = NULL;

	static protected $transport = NULL;

	public function __construct()
	{
		parent::__construct();
		$this->set_mail_instance_id(UUID::v4());
		$this->set_timestamp_queued(time());
		$this->set_sent(Mail::SENT_UNSENT);
		$this->set_type(Mail::TYPE_TEXT);
		$this->set_to(array());
		$this->set_from(array());
      $this->dirty = self::IS_UNSAVED;
	}

	public function add_to($to)
	{
		$to = explode(",", $to);
		array_walk($to, 'trim');
		$arr_to = $this->get_to();
		foreach ($to as $to_row) {
			$arr_to[] = $to_row;
		}
		$this->set_to($arr_to);
		return $this;
	}

	public function add_from($from)
	{
		$from = explode(",", $from);
		array_walk($from, 'trim');
		$arr_from = $this->get_from();
		foreach ($from as $from_row) {
			$arr_from[] = $from_row;
		}
		$this->set_from($arr_from);
		return $this;
	}

	public function add_attachment($data, $file, $type = "text/plain")
	{
		$attachments = $this->get_attachments();
		$attachments[] = array('data' => $data, 'file' => $file, 'type' => $type);
		$this->set_attachments($attachments);
		return $this;
	}

    public function add_attachment_from_disk($file){
        $attachments = $this->get_attachments();
		$attachments[] = array('file' => $file,);
		$this->set_attachments($attachments);
		return $this;
    }

	public function send()
	{
		//If to/from is a string, wrap it
		if (!is_array($this->get_from())) {
			if (strlen($this->get_from()) > 0) {
				$this->set_from(array($this->get_from()));
			}
		}
		if (!is_array($this->get_to())) {
			if (strlen($this->get_to()) > 0) {
				$this->set_to(array($this->get_to()));
			}
		}

		//Filter out empties
		$this->set_to(array_filter($this->get_to()));
		$this->set_from(array_filter($this->get_from()));

		//If the array is empty, add the default locations
		if (count($this->get_from()) == 0) {
			$this->add_from(MagicConfig::get("SERVER_EMAIL"));
		}
		if (count($this->get_to()) == 0) {
			$this->add_to(MagicConfig::get("ADMIN_EMAIL"));
		}

		//Start processing
		$this->set_attempts($this->get_attempts() + 1)->save();
		MagicLogger::log("Sending mail, attempt #{$this->get_attempts()}");

		//Find the transport..
		if (!self::$transport) {
			self::$transport = Swift_SmtpTransport::newInstance()->setHost(MagicConfig::get("EMAIL_SMTP_HOST"))->setPort(MagicConfig::get('EMAIL_SMTP_PORT'))->setEncryption(
				MagicConfig::get('EMAIL_SMTP_SSL') ? 'ssl' : null
			)->setUsername(MagicConfig::get('EMAIL_SMTP_USERNAME'))->setPassword(MagicConfig::get('EMAIL_SMTP_PASSWORD'));
		}

		//Find the mailer...
		if (!self::$mailer) {
			self::$mailer = Swift_Mailer::newInstance(self::$transport);
		}
      
		//Generate the message
		$format = $this->get_type() == 'html' ? 'text/html' : 'text/plain';
		$message = Swift_Message::newInstance(self::$transport)->setSubject($this->get_subject())->setBody($this->get_message(), $format);

		//Process the list of to & froms.
		foreach ($this->get_from() as $address) {
			$address_bits = explode(" ", $address, 2);
			if (count($address_bits) == 2) {
				$nicename = trim($address_bits[0]);
				$email = trim($address_bits[1]);
				$email = trim($email, "<>");
				$message->addFrom($email, $nicename);
			} else {
				$message->addFrom($address);
			}
		}
		foreach ($this->get_to() as $address) {
			$address_bits = explode(" ", $address, 2);
			if (count($address_bits) == 2) {
				$nicename = trim($address_bits[0]);
				$email = trim($address_bits[1]);
				$email = trim($email, "<>");
				$message->addTo($email, $nicename);
			} else {
				$message->addTo($address);
			}
		}

		//Run through the attachment
		if(is_array($this->get_attachments())){
			if(count($this->get_attachments()) > 0){
				foreach ($this->get_attachments() as $file) {
                    if($file['data']){
					    $attachment = Swift_Attachment::newInstance()->setFilename($file['file'])->setBody($file['data'])->setContentType($file['type']);
					    $message->attach($attachment);
                    }else{
                        $message->attach(Swift_Attachment::fromPath($file['file']));
                    }
				}
			}
		}

		//Set some headers
		$headers = $message->getHeaders();
		$headers->addTextHeader('X-Mail-Instance-ID', $this->get_mail_instance_id());
		$headers->addTextHeader('X-Mail-Timestamp-Queued', $this->get_timestamp_queued());
		$headers->addTextHeader('X-Mail-Timestamp-Dispatch', time());
		$headers->addTextHeader('X-Mail-Attempt', $this->get_attempts());
		$headers->addTextHeader('X-Mail-Host', gethostname());

		//Fire off the email
		MagicLogger::log("Sending from " . implode(", ", $this->get_from()) . " to " . implode(", ", $this->get_to()) . " Subject: '{$this->get_subject()}'");
		if (self::$mailer->send($message)) {
			$this->set_sent('sent')->set_timestamp_sent(time())->save();
			MagicLogger::log("Sent mail #{$this->get_id()}/{$this->get_mail_instance_id()}!");
		} else {
			$this->set_sent('unsent')->save();
			MagicLogger::log("Failed to send mail #{$this->get_id()}/{$this->get_mail_instance_id()} :(");
		}
		return $this;
	}

	public function execute()
	{
		
		return $this->save();
	}
}
