<?php
class ModelAccountRecurring extends Model 
{
	public function getRecurringOrder($id) 
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "recurring` o WHERE o.recurring_id = '" . (int)$id . "'");	
	
		return $query->row;
	}
	 
	public function getRecurringOrders($start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 1;
		}	
		
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "recurring` o WHERE o.customer_id = '" . (int)$this->customer->getId() . "' LIMIT " . (int)$start . "," . (int)$limit);	
	
		return $query->rows;
	}
	
	public function getRecurringOrderProducts($order_id) 
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recurring_product WHERE recurring_id = '" . (int)$order_id . "'");	
		return $query->rows;
	}
	
	public function getOrderProductOptions($order_id, $order_product_id) 
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recurring_product_options WHERE recurring_id = '" . (int)$order_id . "' AND product_id = '" . (int)$order_product_id . "'");
	
		return $query->rows;
	}
	
	public function getOrderVouchers($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->rows;
	}
	
	public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order");
	
		return $query->rows;
	}	

	public function getOrderHistories($order_id) {
		$query = $this->db->query("SELECT date_added, os.name AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND oh.notify = '1' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added");
	
		return $query->rows;
	}	

	public function getOrderDownloads($order_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_download WHERE order_id = '" . (int)$order_id . "' ORDER BY name");
	
		return $query->rows; 
	}	

	public function getTotalOrders() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$this->customer->getId() . "' AND order_status_id > '0'");
		
		return $query->row['total'];
	}
		
	public function getTotalOrderProductsByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->row['total'];
	}
	
	public function getTotalOrderVouchersByOrderId($order_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_voucher` WHERE order_id = '" . (int)$order_id . "'");
		
		return $query->row['total'];
	}	
	
	public function cancel($id)
	{
		/*define('AUTHNET_LOGIN', $this->config->get('authorizenet_aim_login'));
   	 	define('AUTHNET_TRANSKEY', $this->config->get('authorizenet_aim_key'));
		
		require(DIR_APPLICATION .'controller/payment/AuthNetXML/AuthnetXML.class.php');
		
		$data = $this->getRecurringOrder($id);
		
		$xml = new AuthnetXML(AUTHNET_LOGIN, AUTHNET_TRANSKEY, AuthnetXML::USE_DEVELOPMENT_SERVER);
		
    	$xml->ARBCancelSubscriptionRequest(array(
        	'refId' => $data['name'],
        	'subscriptionId' => $id
    	));
		
		//echo $xml->response();
		
		*/
		
		
		$this->db->query("update recurring set status = 'cancel' where recurring_id = " . $id);
		
	}
	
	public function restart($id)
	{
		$info = $this->getRecurringOrder($id);
		
		$next = $info['next_order_date'];
		$today = date("Y-m-d");
		
		if ($next <= $today)
		{
			$next = date("Y-m-d", strtotime(sprintf("+%s week", $info['recurring'])));
		}
		
		$this->db->query("update recurring set status = 'active', next_order_date = '".$next."' where recurring_id = " . $id);
		
	}
	
	
	public function update($data, $id)
	{
		$this->db->query("update ".DB_PREFIX."recurring set next_order_date = '".$data['next_order_date']."', status = '".$data['status']."', recurring = '".$data['recurring']."' where recurring_id = " . $id);
		
		if (isset($data['product']))
		{
			foreach($data['product'] as $recurring_product_id => $product)
			{
				if ($product['quantity'] > 0)
				{
					$this->db->query("update ".DB_PREFIX."recurring_product set quantity = '".$product['quantity']."' where recurring_product_id = " . $recurring_product_id);
				}
			}
		}
	}
	
	
	public function addProduct($data, $id)
	{
		print_r($data);
		$this->db->query("insert into ".DB_PREFIX."recurring_product set product_id = '".$data['product_id']."', quantity = '".$data['quantity']."', recurring_id = '".$id."'");
	}
	
}
?>