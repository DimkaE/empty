<?php

if (isset($_POST['phone'])) {
    $mailto = 'test@mail.ru';
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
    $headers .= "From: ROBOT_SITE <info@{$_SERVER['SERVER_NAME']}>\r\n";
    $subject = 'Заявка с сайта';

    $message = '<body>';
    $message .= '<p>Заявка с сайта</p>';
    $message .= '<p>Телефон: ' . $_POST['phone'] . '</p>';
    if (isset($_POST['name'])) {
        $message .= '<p>Имя: ' . $_POST['name'] . '</p>';
    }
    $message .= '<p>E-mail: ' . $_POST['email'] . '</p>';
    $message .= '<body>';
    if (mail($mailto, $subject, $message, $headers)) {
        echo 'success';
    } else {
        echo 'error';
    }
}

/* отправка файла */
class multipartmail{
	var $header;
	var $parts;
	var $message;
	var $subject;
	var $to_address;
	var $boundary;

	function __construct($dest, $src, $sub){
		$this->to_address = $dest;
		$this->subject = $sub;
		$this->parts = array("");
		$this->boundary = "--" . md5(uniqid(time()));
		$this->header = "From: $src\r\n" .
			"MIME-Version: 1.0\r\n" .
			"Content-Type: multipart/mixed;" .
			" boundary=\"" . $this->boundary . "\"\r\n" .
			"X-Mailer: PHP/" . phpversion();
	}

	function addmessage($msg = "", $ctype = "text/plain"){
		$this->parts[0] = "Content-Type: $ctype; charset=utf-8\r\n" .
			"Content-Transfer-Encoding: 7bit\r\n" .
			"\n" .$msg."\r\n";
	}

	function addattachment($file, $ctype){
		$vrem=strrchr($file, "/");
        if ($vrem==false){$fname=$file;}else{$fname=substr($vrem,1);}
		$data = file_get_contents($file);
		$i = count($this->parts);
		$content_id = "part$i." . sprintf("%09d", crc32($fname)) . strrchr($this->to_address, "@");
		$this->parts[$i] = "Content-Type: $ctype; name=\"$fname\"\r\n" .
			"Content-Transfer-Encoding: base64\r\n" .
			"Content-ID: <$content_id>\r\n" .
			"Content-Disposition: attachment; " .
			" filename=\"$fname\"\r\n" .
			"\n" .
			chunk_split( base64_encode($data), 68, "\n");
		return $content_id;
	}

	function buildmessage(){
		$this->message = "This is a multipart message in mime format.\n";
		$cnt = count($this->parts);
		for($i=0; $i<$cnt; $i++){
			$this->message .= "--" . $this->boundary . "\n" .
				$this->parts[$i];
		}
	}

	/* to get the message body as a string */
	function getmessage(){
		$this->buildmessage();
		return $this->message;
	}

	function sendmail(){
		$this->buildmessage();
		mail($this->to_address, $this->subject, $this->message, $this->header);
	}
}

if (isset($_POST['tel_t'])) {
	$uploaddir = __DIR__ . '/uploads/';
	$uploadfile = $uploaddir . basename($_FILES['file_t']['name']);
	move_uploaded_file($_FILES['file_t']['tmp_name'], $uploadfile);
	$message = '
		Макет с сайта
		Имя: ' . $_POST['name_t'] . '
		Телефон: ' . $_POST['tel_t']
;
	$mulmail = new multipartmail($mailto, "cm75757@ava.timeweb.ru" , $subject);
	$cid = $mulmail->addattachment($uploadfile, $_FILES['file_t']['type']);
	$mulmail->addmessage($message);
    $mulmail->sendmail();
	unlink($uploadfile);
}