<?php

if ( version_compare( $GLOBALS['wp_version'], '3.1.9', '>' ) ) {
	echo "Using custom build of PHPMailer for 3.2 and later testing\n\n";
	require_once(DIR_TESTROOT . '/wp-testlib/class-phpmailer.php');
} else {
	require_once(ABSPATH . '/wp-includes/class-phpmailer.php');
}

class MockPHPMailer extends PHPMailer {
	var $mock_sent = array();

	// override the Send function so it doesn't actually send anything
	function Send() {
		if ( (count($this->to) + count($this->cc) + count($this->bcc)) < 1 ) {
			$this->SetError( 'You must provide at least one recipient email address.' );
			return false;
		}

		// Set whether the message is multipart/alternative
		if( ! empty($this->AltBody) )
			$this->ContentType = 'multipart/alternative';

		$this->error_count = 0; // reset errors
		$this->SetMessageType();
		$header = $this->CreateHeader();
		$body = $this->CreateBody();

		if ( $body == '' ) {
			$this->SetError( 'Message body empty' );
			return false;
		}

		$this->mock_sent[] = array(
			'to' => $this->to,
			'cc' => $this->cc,
			'bcc' => $this->bcc,
			'header' => $header,
			'body' => $body,
		);

		return true;
    }
}

?>
