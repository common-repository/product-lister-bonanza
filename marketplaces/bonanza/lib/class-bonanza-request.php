<?php
/**
 * BonanzaRequest Class to handle different rquests.
 *
 * @class BonanzaRequest
 *
 * @version 1.0.0
 */
if (! class_exists ( 'BonanzaRequest' )) {
	class BonanzaRequest {
		/**
		 * BonanzaRequest Constructor.
		 */
		public function __construct() {
			 
		}
		 
		/** 
		* Function to call POST Curl Request
		*/
		public function sendCurlPostMethod($url="http://demo.cedcommerce.com/woocommerce/marketplaces/bonanza/marketplaces-bonanza.php",$bodyDataToSend='' ){
			  
				$requestBody = array(
		            'method'         => 'POST',
		            'timeout'         => 45,
		            'redirection'     => 5,
		            'httpversion'     => '1.0',
		            'blocking'         => true,
		            'headers'         => array(),
		            'body'             => $bodyDataToSend,
		            'cookies'         => array()
		        );
		        $response = wp_remote_post( $url, $requestBody );	     
		        $response = isset( $response['body'] ) ? $response['body']: array();
		        return $response;
		}
	}
}
?>