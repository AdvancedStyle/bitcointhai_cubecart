<?php

define('MODULE_PAYMENT_BITCOINTHAI_TEXT_PAYMSG','You must send <strong>%s</strong> Bitcoins to the address: %s');
define('IMAGE_BUTTON_CONFIRM_ORDER','Make Payment');
define('MODULE_PAYMENT_BITCOINTHAI_TEXT_AFTERPAY','After you have completed payment please click the &quot;'.IMAGE_BUTTON_CONFIRM_ORDER.'&quot; button');
define('MODULE_PAYMENT_BITCOINTHAI_TEXT_ADDRESS','Bitcoin Address:');
define('MODULE_PAYMENT_BITCOINTHAI_TEXT_AMOUNT','Bitcoin Amount:');
define('MODULE_PAYMENT_BITCOINTHAI_TEXT_COUNTDOWN','You must send the bitcoins within the next %s Minutes %s Seconds');
define('MODULE_PAYMENT_BITCOINTHAI_TEXT_COUNTDOWN_EXP','Bitcoin payment time has expired, please refresh the page to get a new address');
define('MODULE_PAYMENT_BITCOINTHAI_TEXT_ERROR','Sorry Bitcoin payments are currently unavailable');
define('MODULE_PAYMENT_BITCOINTHAI_TITLE_ERROR','Bitcoin Error');

class Gateway {
	private $_config;
	private $_module;
	private $_basket;
	private $_result_message;
	private $api;
	private $enabled;

	public function __construct($module = false, $basket = false) {
		$this->_db		=& $GLOBALS['db'];

		$this->_module	= $module;
		$this->_basket =& $GLOBALS['cart']->basket;
		
		$this->_config['ipn_url'] = $GLOBALS['storeURL'].'/index.php?_g=rm&type=gateway&cmd=call&module=BitcoinThai';
		
		include_once('includes/bitcointhai.php');
		
		$this->enabled = true;
		
		$this->api = new bitcointhaiAPI;
		if(!$this->api->init($this->_module['api_id'], $this->_module['api_key'])){
			$this->enabled = false;
		}elseif(!$this->api->validate($this->_basket['total'],$GLOBALS['config']->get('config', 'default_currency'))){
			$this->enabled = false;
		}
	}

	##################################################

	public function transfer() {
		$transfer	= array(
			'action'	=> currentPage(),
			'method'	=> 'post',
			'target'	=> '_self',
			'submit'	=> 'manual',
		);
		return $transfer;
	}

	##################################################

	public function repeatVariables() {
		return (isset($hidden)) ? $hidden : false;
	}

	public function fixedVariables() {
		$hidden['gateway']	= basename(dirname(__FILE__));
		return (isset($hidden)) ? $hidden : false;
	}

	public function call() {
		$data = $_POST;
		
		if($ipn = $this->api->verifyIPN($data)){
			$cart_order_id	= $data['reference_id'];
			if (!empty($cart_order_id) && !empty($data)) {
				$order				= Order::getInstance();
				$order_summary		= $order->getSummary($cart_order_id);
				
				$order->paymentStatus(Order::PAYMENT_SUCCESS, $cart_order_id);
				$order->orderStatus(Order::ORDER_PROCESS, $cart_order_id);
				
				## Build the transaction log data
				$transData = array();
				$transData['gateway']		= 'BitcoinThai';
				$transData['order_id']		= $cart_order_id;
				$transData['trans_id']		= $data['order_id'];
				$transData['status']		= $data['order_status'];
				$transData['notes'][]	= 'Bitcoin IPN: '.$data['message'];
				$order->logTransaction($transData);
				
				ob_end_clean();
				echo 'IPN Success';
				exit();
			}
		}
			
		ob_end_clean();
		header("HTTP/1.0 403 Forbidden");
		echo 'IPN Failed';
		exit();
	}

	public function process() {
	
		$order				= Order::getInstance();
		$cart_order_id 		= $this->_basket['cart_order_id'];
		
		$result = $this->api->checkorder($_POST['order_id'], $cart_order_id);
		if(!$result || $result->error != ''){
			if(!$result){
			  $this->_result_message = MODULE_PAYMENT_BITCOINTHAI_TEXT_ERROR;
			}else{
			  $this->_result_message = $result->error;
			  if(isset($result->order_id)){
				  $_SESSION['bitcoin_order_id'] = $result->order_id;
			  }
			}
		}else{
			unset($_SESSION['bitcoin_order_id']);
			httpredir(currentPage(array('_g', 'type', 'cmd', 'module'), array('_a' => 'complete')));
		}
	}
	
	public function form() {
		
		## Process transaction
		if (isset($_POST['order_id'])) {
			$return	= $this->process();
		}

		// Display payment result message
		if (!empty($this->_result_message))	{
			$GLOBALS['gui']->setError($this->_result_message);
		}
		
		if($this->enabled){
			$this->api->order_id = $_SESSION['bitcoin_order_id'];
			$data = array('amount' => $this->_basket['total'],
						  'currency' => $GLOBALS['config']->get('config', 'default_currency'),
						  'ipn' => $this->_config['ipn_url']);
			if(!$paybox = $this->api->paybox($data)){
				return array('title' => MODULE_PAYMENT_BITCOINTHAI_TEXT_ERROR);
			}
			$_SESSION['bitcoin_order_id'] = $this->api->order_id;
			$btc_url = 'bitcoin:'.$paybox->address.'?amount='.$paybox->btc_amount.'&label='.urlencode($GLOBALS['config']->get('config', 'store_name').' Order '.$this->_basket['cart_order_id']);
		    return '<input type="hidden" name="order_id" value="'.$paybox->order_id.'"><div style="float:left; margin:10px;"><a href="'.$btc_url.'"><img src="data:image/png;base64,'.$paybox->qr_data.'" width="200" alt="Send to '.$paybox->address.'" border="0"></a></div><h2 style="margin:10px 0px;">Bitcoin Payment</h2><p style="margin:10px 0px;">'.sprintf(MODULE_PAYMENT_BITCOINTHAI_TEXT_PAYMSG,$paybox->btc_amount,$paybox->address).'</p><p style="margin:10px 0px;">'.MODULE_PAYMENT_BITCOINTHAI_TEXT_AFTERPAY.'</p><p style="margin:10px 0px;">'.$this->api->countDown($paybox->expire,'form',MODULE_PAYMENT_BITCOINTHAI_TEXT_COUNTDOWN,MODULE_PAYMENT_BITCOINTHAI_TEXT_COUNTDOWN_EXP).'</p>';
		}else{
			if($this->api->error != '' && $this->_module['debugging']){
				$ret = '<p class="error">'.$this->api->error.'</p>';
			}else{
				$ret = '<p class="error">'.MODULE_PAYMENT_BITCOINTHAI_TEXT_ERROR.'</p>';
			}
		}

		return $ret;
	}
	
}