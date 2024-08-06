<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Business_Manager_Email_Sender' ) ) :

	/**
	 * Business_Manager_Email_Sender Class.
	 */
	class Business_Manager_Email_Sender {
		/**
		 * The recipient email address.
		 *
		 * @var string
		 */
		private $to;

		/**
		 * The subject of the email.
		 *
		 * @var string
		 */
		private $subject;

		/**
		 * The message content of the email.
		 *
		 * @var string
		 */
		private $message;

		/**
		 * Additional headers for the email.
		 *
		 * @var string
		 */
		private $headers;

		/**
         * Constructor.
         *
         * @param string $to      The recipient email address.
         * @param string $subject The subject of the email.
         * @param string $message The message content of the email.
         * @param array  $headers Additional headers for the email. Default is an empty array.
         */
		public function __construct( $to, $subject, $message, $headers = [] ) {
			$this->to      = $to;
			$this->subject = $subject;
			$this->message = $message;
			$this->headers = $headers;
		}

		/**
		 * Load a template file with optional data.
		 *
		 * @param string $template_path The path to the template file.
		 * @param array  $data          Optional data to pass to the template.
		 */
		private function load_template( $template_path, $data = [] ) {
			ob_start();
			extract( $data );
			include $template_path;
			return ob_get_clean();
		}

		/**
		 * Sends user an email.
		 *
		 * @return bool
		 */
		public function send_email() {
			// Load email template.
			$template_path = BUSINESSMANAGER_DIR . '/templates/emails/bm-email.php';
			$email_content = $this->load_template( $template_path, [ 'message' => $this->message ] );

			// Add headers for HTML content.
			$this->headers[] = empty( $this->headers ) ? 'Content-Type: text/html; charset=UTF-8' : $this->headers;
			$sent            = wp_mail( $this->to, $this->subject, $email_content, $this->headers );

			if ( $sent ) {
				return true;
			} else {
				return false;
			}
		}
	}

endif;
