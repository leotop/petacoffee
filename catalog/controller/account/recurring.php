<?php 
class ControllerAccountRecurring extends Controller {
	private $error = array();
		
	public function index() {
    	if (!$this->customer->isLogged()) 
		{
      		$this->session->data['redirect'] = $this->url->link('account/recurring', '', 'SSL');

	  		$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
		
		$this->language->load('account/recurring');
		
		$this->load->model('account/recurring');

    	$this->document->setTitle($this->language->get('heading_title'));

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	); 

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		
		$url = '';
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
				
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/recurring', $url, 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_order_id'] = $this->language->get('text_order_id');
		$this->data['text_status'] = $this->language->get('text_status');
		$this->data['text_date_added'] = $this->language->get('text_date_added');
		$this->data['text_customer'] = $this->language->get('text_customer');
		$this->data['text_products'] = $this->language->get('text_products');
		$this->data['text_total'] = $this->language->get('text_total');
		$this->data['text_empty'] = $this->language->get('text_empty');

		$this->data['button_view'] = $this->language->get('button_view');
		$this->data['button_reorder'] = $this->language->get('button_reorder');
		$this->data['button_continue'] = $this->language->get('button_continue');
		
		if (isset($this->request->get['page'])) 
		{
			$page = $this->request->get['page'];
		} 
		else 
		{
			$page = 1;
		}
		
		$this->data['orders'] = array();
		
		$results = $this->model_account_recurring->getRecurringOrders();
		
		foreach($results as $result)
		{
			$result['amount'] = $this->currency->format($result['amount']);
			$result['previous_amount'] = $this->currency->format($result['previous_amount']);
			$result['date'] = date($this->language->get('date_format_short_us'), strtotime($result['date']));
			$result['next_order_date'] = date($this->language->get('date_format_short_us'), strtotime($result['next_order_date']));
			$result['href'] = $this->url->link('account/order/info', 'order_id=' . $result['name'], 'SSL');
			$result['reorder'] = $this->url->link('account/recurring/cancel', 'id=' . $result['recurring_id'], 'SSL');
			$result['restart'] = $this->url->link('account/recurring/restart', 'id=' . $result['recurring_id'], 'SSL');
			$result['edit'] = $this->url->link('account/recurring/update', 'id=' . $result['recurring_id'], 'SSL');
			$this->data['orders'][] = $result;
		}

		$this->data['continue'] = $this->url->link('account/account', '', 'SSL');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/recurring_list.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/recurring_list.tpl';
		} else {
			$this->template = 'default/template/account/recurring_list.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'	
		);
						
		$this->response->setOutput($this->render());				
	}
	
	public function cancel()
	{
		if (!$this->customer->isLogged()) 
		{
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL');
			
			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
		
		if (isset($this->request->get['id'])) {
			$id = $this->request->get['id'];
		} else {
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}	
		
		if ($this->validate($id))
		{
			$this->model_account_recurring->cancel($id);
			$this->redirect($this->url->link('account/recurring', '', 'SSL'));
		}
	}
	
	public function restart()
	{
		if (!$this->customer->isLogged()) 
		{
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL');			
			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
		
		if (isset($this->request->get['id'])) {
			$id = $this->request->get['id'];
		} else {
			$this->redirect($this->url->link('account/login', '', 'SSL'));
		}	
		
		if ($this->validate($id))
		{
			$this->model_account_recurring->restart($id);
			$this->redirect($this->url->link('account/recurring', '', 'SSL'));
		}
	}
	
	private function validate($id)
	{
		$this->load->model('account/recurring');
		$info = $this->model_account_recurring->getRecurringOrder($id);
		if ($info['customer_id'] == $this->customer->getId())
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	public function update()
	{
		$this->cart->clear();			
		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);			
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);
		unset($this->session->data['coupon']);
		unset($this->session->data['reward']);
		unset($this->session->data['voucher']);
		unset($this->session->data['vouchers']);		
		unset($this->session->data['recurring']);
		unset($this->session->data['recurring_frequency']);
		
		
		$this->language->load('account/recurring');		
		$this->load->model('account/recurring');
		$this->load->model('account/order');
		

    	$this->document->setTitle($this->language->get('heading_title'));
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST'))
		{
			$this->model_account_recurring->update($this->request->post, $this->request->get['id']);
			$this->redirect($this->url->link('account/recurring/','', 'SSL'));
		}
		
		$info = $this->model_account_recurring->getRecurringOrder($this->request->get['id']);
		
		$order_info = $this->model_account_order->getOrder($info['last_order_id']);

      	$this->data['breadcrumbs'] = array();

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home'),        	
        	'separator' => false
      	); 

      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('text_account'),
			'href'      => $this->url->link('account/account', '', 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		
		
		$url = '';
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
				
      	$this->data['breadcrumbs'][] = array(
        	'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('account/recurring', $url, 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);
		
		$this->data['breadcrumbs'][] = array(
        	'text'      => 'Recurring Order #'.$info['name'],
			'href'      => $this->url->link('account/recurring/update', 'id=' . $info['recurring_id'], 'SSL'),        	
        	'separator' => $this->language->get('text_separator')
      	);

		$this->data['heading_title'] = sprintf('Recurring Order #%s', $info['name']);
		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_select'] = $this->language->get('text_select');
		$this->data['entry_option'] = $this->language->get('entry_option');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['button_upload'] = $this->language->get('button_upload');
		$this->data['token'] = md5(rand());
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['button_remove'] = $this->language->get('button_remove');
		
		$this->session->data['recurring'] = 1;
		$this->session->data['recurring_frequency'] = $info['recurring'];
		
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		$this->data['action'] = $this->url->link('account/recurring/update','id=' . $this->request->get['id']);
		
		if (isset($this->request->post['next_order_date']))
		{
			$this->data['next_order_date'] = $this->request->post['next_order_date'];
		}
		else if ($info)
		{
			$this->data['next_order_date'] = $info['next_order_date'];
		}
		
		if (isset($this->request->post['recurring_frequency']))
		{
			$this->data['recurring_frequency'] = $this->request->post['recurring_frequency'];
		}
		else if ($info)
		{
			$this->data['recurring_frequency'] = $info['recurring'];
		}
		
		
		if (isset($this->request->post['shipping_address_id']))
		{
			$this->data['shipping_address_id'] = $this->request->post['shipping_address_id'];
		}
		else if ($info)
		{
			$this->data['shipping_address_id'] = $info['shipping_address_id'];
		}
		
		
		if (isset($this->request->post['payment_address_id']))
		{
			$this->data['payment_address_id'] = $this->request->post['payment_address_id'];
		}
		else if ($info)
		{
			$this->data['payment_address_id'] = $info['payment_address_id'];
		}
		
		if (isset($this->request->post['payment_zone_id']))
		{
			$this->data['payment_zone_id'] = $this->request->post['payment_zone_id'];
		}
		else if ($info)
		{
			$this->data['payment_zone_id'] = $info['payment_zone_id'];
		}
		
		if (isset($this->request->post['shipping_zone_id']))
		{
			$this->data['shipping_zone_id'] = $this->request->post['shipping_zone_id'];
		}
		else if ($info)
		{
			$this->data['shipping_zone_id'] = $info['shipping_zone_id'];
		}
		
		if (isset($this->request->post['shipping_method']))
		{
			$this->data['shipping_code'] = $this->request->post['shipping_method'];
		}
		else if ($info)
		{
			$this->data['shipping_code'] = $info['shipping_code'];
		}	
		
		if (isset($this->request->post['payment_method']))
		{
			$this->data['payment_code'] = $this->request->post['payment_method'];
		}
		else if ($info)
		{
			$this->data['payment_code'] = $info['payment_code'];
		}	
		
		if (isset($this->request->post['comment']))
		{
			$this->data['comment'] = $this->request->post['comment'];
		}
		else if ($info)
		{
			$this->data['comment'] = $info['comment'];
		}
		
		if (isset($this->request->post['status']))
		{
			$this->data['status'] = $this->request->post['status'];
		}
		else if ($info)
		{
			$this->data['status'] = $info['status'];
		}
		
		if (isset($this->request->post['order_product'])) 
		{
			$order_products = $this->request->post['order_product'];
		} 
		elseif (isset($info['last_order_id'])) 
		{
			$order_products = $this->model_account_recurring->getRecurringProducts($info['recurring_id']);			
		} 
		else 
		{
			$order_products = array();
		}
		
		$this->load->model('catalog/product');
		
		$this->document->addScript('view/javascript/jquery/ajaxupload.js');
		
		$this->data['products'] = array();		
		
		foreach($order_products as $order_product)
		{
			$order_options = array_filter($this->model_account_recurring->getRecurringOptions($info['recurring_id'], $order_product['recurring_product_id']));
			
			$option = array();
			
			foreach($order_options as $order_option)
			{
				if ($order_option['type'] == 'select')
				{
					$option[$order_option['product_option_id']] = $order_option['product_option_value_id'];
				}
				else if ($order_option['type'] == 'checkbox')
				{
					$option[$order_option['product_option_id']] = array($order_option['product_option_value_id']);
				}
			}
			
			$this->cart->add($order_product['product_id'], $order_product['quantity'], $option);
			
		}
		
		$results = $this->cart->getProducts();
		
		foreach($results as $order_product)
		{
			$order_option = array();
			
			//print_r($order_product);
			
			foreach ($order_product['option'] as $option) 
			{
				if ($option['type'] != 'file') 
				{
					$value = $option['option_value'];	
				} 
				else 
				{
					$filename = $this->encryption->decrypt($option['option_value']);						
					$value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
				}
					
				$order_option[] = array(
					'product_option_id' => $option['product_option_id'],
					'product_option_value_id' => $option['product_option_value_id'],
					'type' => $option['type'],
					'name'  => $option['name'],
					'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
				);
        	}
			
			$this->data['products'][] = array(
				'product_id'       => $order_product['product_id'],
				'name'             => $order_product['name'],
				'model'            => $order_product['model'],
				'option'           => $order_option,
				'quantity'         => $order_product['quantity'],
				'price'            => $order_product['price'],
				'total'            => $order_product['total'],
				'reward'           => $order_product['reward']
			);
		}
		
		
		if (isset($this->request->post['order_voucher'])) {
			$this->data['order_vouchers'] = $this->request->post['order_voucher'];
		} elseif (isset($info['last_order_id'])) {
			$this->data['order_vouchers'] = $this->model_account_order->getOrderVouchers($info['last_order_id']);			
		} else {
			$this->data['order_vouchers'] = array();
		}
		
		/*if (isset($this->request->post['order_total'])) {
      		$this->data['order_totals'] = $this->request->post['order_total'];
    	} elseif (isset($info['last_order_id'])) { 
			$this->data['order_totals'] = $this->model_account_order->getOrderTotals($info['last_order_id']);
		} else {
      		$this->data['order_totals'] = array();
    	}*/
		
		$this->load->model('account/address');		
		$this->data['addresses'] = $this->model_account_address->getAddresses();
		
		$shipping_address = $this->model_account_address->getAddress($info['shipping_address_id']);
		$payment_address  = $this->model_account_address->getAddress($info['payment_address_id']);	
		
		//print_r($info);
		
		$quote_data = array();			
		$this->load->model('setting/extension');			
		$results = $this->model_setting_extension->getExtensions('shipping');			
		foreach ($results as $result) 
		{
			if ($this->config->get($result['code'] . '_status')) 
			{
				$this->load->model('shipping/' . $result['code']);					
				$quote = $this->{'model_shipping_' . $result['code']}->getQuote($shipping_address); 
		
				if ($quote) 
				{
					$quote_data[$result['code']] = array( 
						'title'      => $quote['title'],
						'quote'      => $quote['quote'], 
						'sort_order' => $quote['sort_order'],
						'error'      => $quote['error']
					);
				}
			}
		}
	
		$sort_order = array();
		  
		foreach ($quote_data as $key => $value) 
		{
			$sort_order[$key] = $value['sort_order'];
			
			foreach($value['quote'] as $quote)
			{
				if ($quote['code'] == $info['shipping_code']) $this->session->data['shipping_method'] = $quote;
			}
			
		}
	
		array_multisort($sort_order, SORT_ASC, $quote_data);
			
		$this->data['shipping_methods'] = $quote_data;
		
		// Totals
		$total_data = array();					
		$total = 0;
		$taxes = $this->cart->getTaxes();
			
		$this->load->model('setting/extension');
			
		$sort_order = array(); 
			
		$results = $this->model_setting_extension->getExtensions('total');
			
		foreach ($results as $key => $value) 
		{
			$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
		}
			
		array_multisort($sort_order, SORT_ASC, $results);
			
		foreach ($results as $result) 
		{
			if ($this->config->get($result['code'] . '_status')) 
			{
				$this->load->model('total/' . $result['code']);		
				$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
			}
		}
		
		$method_data = array();
			
		$this->load->model('setting/extension');
			
		$results = $this->model_setting_extension->getExtensions('payment');
		
		foreach ($results as $result) 
		{
			if ($this->config->get($result['code'] . '_status')) 
			{
				$this->load->model('payment/' . $result['code']);					
				$method = $this->{'model_payment_' . $result['code']}->getMethod($payment_address, $total); 
					
				if ($method) {
					$method_data[$result['code']] = $method;
				}
			}
		}

		$sort_order = array(); 
		  
		foreach ($method_data as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}
	
		array_multisort($sort_order, SORT_ASC, $method_data);			
			
		$this->data['payment_methods'] = $method_data;	
		$this->data['order_totals'] = $total_data;
		
		$this->load->model('localisation/country');
		
    	$this->data['countries'] = $this->model_localisation_country->getCountries();
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/recurring_form.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/account/recurring_form.tpl';
		} else {
			$this->template = 'default/template/account/recurring_form.tpl';
		}
		
		$this->cart->clear();
			
		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);			
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);
		unset($this->session->data['coupon']);
		unset($this->session->data['reward']);
		unset($this->session->data['voucher']);
		unset($this->session->data['vouchers']);
		
		unset($this->session->data['recurring']);
		unset($this->session->data['recurring_frequency']);
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'	
		);
						
		$this->response->setOutput($this->render());	
	}
	
	public function remove()
	{
		$this->load->model('account/recurring');
		$products = $this->model_account_recurring->getRecurringOrderProducts($this->request->get['id']);
		
		if (count($products) > 1)
		{		
			$this->db->query("delete from ".DB_PREFIX."recurring_product WHERE recurring_product_id = " . $this->request->get['recurring_product_id']);
		}
		else
		{
			$this->error['warning'] = "You must have at least 1 product.";
		}
		$this->redirect($this->url->link('account/recurring/update', 'id=' . $this->request->get['id'], 'SSL'));
	}
	
	public function info() { 
		$this->language->load('account/order');
		
		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}	

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL');
			
			$this->redirect($this->url->link('account/login', '', 'SSL'));
    	}
						
		$this->load->model('account/order');
			
		$order_info = $this->model_account_order->getOrder($order_id);
		
		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));
			
			$this->data['breadcrumbs'] = array();
		
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),        	
				'separator' => false
			); 
		
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),        	
				'separator' => $this->language->get('text_separator')
			);
			
			$url = '';
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', $url, 'SSL'),      	
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_order'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
					
      		$this->data['heading_title'] = $this->language->get('text_order');
			
			$this->data['text_order_detail'] = $this->language->get('text_order_detail');
			$this->data['text_invoice_no'] = $this->language->get('text_invoice_no');
    		$this->data['text_order_id'] = $this->language->get('text_order_id');
			$this->data['text_date_added'] = $this->language->get('text_date_added');
      		$this->data['text_shipping_method'] = $this->language->get('text_shipping_method');
			$this->data['text_shipping_address'] = $this->language->get('text_shipping_address');
      		$this->data['text_payment_method'] = $this->language->get('text_payment_method');
      		$this->data['text_payment_address'] = $this->language->get('text_payment_address');
      		$this->data['text_history'] = $this->language->get('text_history');
			$this->data['text_comment'] = $this->language->get('text_comment');

      		$this->data['column_name'] = $this->language->get('column_name');
      		$this->data['column_model'] = $this->language->get('column_model');
      		$this->data['column_quantity'] = $this->language->get('column_quantity');
      		$this->data['column_price'] = $this->language->get('column_price');
      		$this->data['column_total'] = $this->language->get('column_total');
			$this->data['column_action'] = $this->language->get('column_action');
			$this->data['column_date_added'] = $this->language->get('column_date_added');
      		$this->data['column_status'] = $this->language->get('column_status');
      		$this->data['column_comment'] = $this->language->get('column_comment');
			
			$this->data['button_return'] = $this->language->get('button_return');
      		$this->data['button_continue'] = $this->language->get('button_continue');
		
			if ($order_info['invoice_no']) {
				$this->data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$this->data['invoice_no'] = '';
			}
			
			$this->data['order_id'] = $this->request->get['order_id'];
			$this->data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));
			
			if ($order_info['payment_address_format']) {
      			$format = $order_info['payment_address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}
		
    		$find = array(
	  			'{firstname}',
	  			'{lastname}',
	  			'{company}',
      			'{address_1}',
      			'{address_2}',
     			'{city}',
      			'{postcode}',
      			'{zone}',
				'{zone_code}',
      			'{country}'
			);
	
			$replace = array(
	  			'firstname' => $order_info['payment_firstname'],
	  			'lastname'  => $order_info['payment_lastname'],
	  			'company'   => $order_info['payment_company'],
      			'address_1' => $order_info['payment_address_1'],
      			'address_2' => $order_info['payment_address_2'],
      			'city'      => $order_info['payment_city'],
      			'postcode'  => $order_info['payment_postcode'],
      			'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
      			'country'   => $order_info['payment_country']  
			);
			
			$this->data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

      		$this->data['payment_method'] = $order_info['payment_method'];
			
			if ($order_info['shipping_address_format']) {
      			$format = $order_info['shipping_address_format'];
    		} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}
		
    		$find = array(
	  			'{firstname}',
	  			'{lastname}',
	  			'{company}',
      			'{address_1}',
      			'{address_2}',
     			'{city}',
      			'{postcode}',
      			'{zone}',
				'{zone_code}',
      			'{country}'
			);
	
			$replace = array(
	  			'firstname' => $order_info['shipping_firstname'],
	  			'lastname'  => $order_info['shipping_lastname'],
	  			'company'   => $order_info['shipping_company'],
      			'address_1' => $order_info['shipping_address_1'],
      			'address_2' => $order_info['shipping_address_2'],
      			'city'      => $order_info['shipping_city'],
      			'postcode'  => $order_info['shipping_postcode'],
      			'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
      			'country'   => $order_info['shipping_country']  
			);

			$this->data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$this->data['shipping_method'] = $order_info['shipping_method'];
			
			$this->data['products'] = array();
			
			$products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);

      		foreach ($products as $product) {
				$option_data = array();
				
				$options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

         		foreach ($options as $option) {
          			if ($option['type'] != 'file') {
						$value = $option['value'];
					} else {
						$value = utf8_substr($option['value'], 0, utf8_strrpos($option['value'], '.'));
					}
					
					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);					
        		}

        		$this->data['products'][] = array(
          			'name'     => $product['name'],
          			'model'    => $product['model'],
          			'option'   => $option_data,
          			'quantity' => $product['quantity'],
          			'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'return'   => $this->url->link('account/return/insert', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], 'SSL')
        		);
      		}

			// Voucher
			$this->data['vouchers'] = array();
			
			$vouchers = $this->model_account_order->getOrderVouchers($this->request->get['order_id']);
			
			foreach ($vouchers as $voucher) {
				$this->data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}
			
      		$this->data['totals'] = $this->model_account_order->getOrderTotals($this->request->get['order_id']);
			
			$this->data['comment'] = nl2br($order_info['comment']);
			
			$this->data['histories'] = array();

			$results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);

      		foreach ($results as $result) {
        		$this->data['histories'][] = array(
          			'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
          			'status'     => $result['status'],
          			'comment'    => nl2br($result['comment'])
        		);
      		}

      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');
		
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/account/order_info.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/account/order_info.tpl';
			} else {
				$this->template = 'default/template/account/order_info.tpl';
			}
			
			
			
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'	
			);
								
			$this->response->setOutput($this->render());		
    	} else {
			$this->document->setTitle($this->language->get('text_order'));
			
      		$this->data['heading_title'] = $this->language->get('text_order');

      		$this->data['text_error'] = $this->language->get('text_error');

      		$this->data['button_continue'] = $this->language->get('button_continue');
			
			$this->data['breadcrumbs'] = array();

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home'),
				'separator' => false
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_account'),
				'href'      => $this->url->link('account/account', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('account/order', '', 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
			
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_order'),
				'href'      => $this->url->link('account/order/info', 'order_id=' . $order_id, 'SSL'),
				'separator' => $this->language->get('text_separator')
			);
												
      		$this->data['continue'] = $this->url->link('account/order', '', 'SSL');
			 			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/error/not_found.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/error/not_found.tpl';
			} else {
				$this->template = 'default/template/error/not_found.tpl';
			}
			
			$this->children = array(
				'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer',
				'common/header'	
			);
								
			$this->response->setOutput($this->render());				
    	}
  	}
	
	public function addproduct()
	{
		$this->load->model('account/recurring');
		$json = array();
		
		if (isset($this->request->post['product']))
		{
			$this->model_account_recurring->addProduct($this->request->post, $this->request->get['id']);
			
			$results = $this->model_account_recurring->getRecurringOrderProducts($this->request->get['id']);
			$json['order_product'] = array();
			$this->load->model('catalog/product');
		
			foreach($results as $result)
			{
				$product = $this->model_catalog_product->getProduct($result['product_id']);			
				$result['name'] = $product['name'];			
				$result['model'] = $product['model'];	
				$result['remove'] = $this->url->link('account/recurring/remove', 'id=' . $this->request->get['id'] . '&recurring_product_id=' . $result['recurring_product_id']);
				// Display prices
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
				} else {
					$price = false;
				}
				
				// Display prices
				if (($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) {
					$total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $result['quantity']);
				} else {
					$total = false;
				}
			
				$options = $this->model_account_recurring->getOrderProductOptions($result['recurring_product_id']);
				
				$option_data = array();
				$option_price = 0;
				$option_points = 0;
				$option_weight = 0;
			
       			foreach ($options as $option) 
				{
					$product_option_id = $option['product_option_id'];
					$option_value = $option['product_option_value_id'];
				
					$option_query = $this->db->query("SELECT po.product_option_id, po.option_id, od.name, o.type FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_option_id = '" . (int)$product_option_id . "' AND po.product_id = '" . (int)$result['product_id'] . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "'");
						
					if ($option_query->num_rows) 
					{
						if ($option_query->row['type'] == 'select' || $option_query->row['type'] == 'radio' || $option_query->row['type'] == 'image') 
						{
							$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$option_value . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
								
							if ($option_value_query->num_rows) 
							{
								if ($option_value_query->row['price_prefix'] == '+') 
								{
									$option_price += $option_value_query->row['price'];
								}
								elseif ($option_value_query->row['price_prefix'] == '-') 
								{
									$option_price -= $option_value_query->row['price'];
								}
	
								if ($option_value_query->row['points_prefix'] == '+') 
								{
									$option_points += $option_value_query->row['points'];
								}
								elseif ($option_value_query->row['points_prefix'] == '-') 
								{
									$option_points -= $option_value_query->row['points'];
								}
																
								if ($option_value_query->row['weight_prefix'] == '+') 
								{
									$option_weight += $option_value_query->row['weight'];
								}
								elseif ($option_value_query->row['weight_prefix'] == '-') 
								{
									$option_weight -= $option_value_query->row['weight'];
								}
									
								if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $result['quantity']))) 
								{
									$stock = false;
								}
									
								$option_data[] = array(
									'product_option_id'       => $product_option_id,
									'product_option_value_id' => $option_value,
									'option_id'               => $option_query->row['option_id'],
									'option_value_id'         => $option_value_query->row['option_value_id'],
									'name'                    => $option_query->row['name'],
									'option_value'            => $option_value_query->row['name'],
									'type'                    => $option_query->row['type'],
									'quantity'                => $option_value_query->row['quantity'],
									'subtract'                => $option_value_query->row['subtract'],
									'price'                   => $option_value_query->row['price'],
									'price_prefix'            => $option_value_query->row['price_prefix'],
									'points'                  => $option_value_query->row['points'],
									'points_prefix'           => $option_value_query->row['points_prefix'],									
									'weight'                  => $option_value_query->row['weight'],
									'weight_prefix'           => $option_value_query->row['weight_prefix']
								);								
							}
						} 
						elseif ($option_query->row['type'] == 'checkbox' && is_array($option_value)) 
						{
							foreach ($option_value as $product_option_value_id) 
							{
								$option_value_query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND pov.product_option_id = '" . (int)$product_option_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
									
								if ($option_value_query->num_rows) 
								{
									if ($option_value_query->row['price_prefix'] == '+') 
									{
										$option_price += $option_value_query->row['price'];
									} 
									elseif ($option_value_query->row['price_prefix'] == '-') 
									{
										$option_price -= $option_value_query->row['price'];
									}
	
									if ($option_value_query->row['points_prefix'] == '+') 
									{
										$option_points += $option_value_query->row['points'];
									} 
									elseif ($option_value_query->row['points_prefix'] == '-') 
									{
										$option_points -= $option_value_query->row['points'];
									}
																	
									if ($option_value_query->row['weight_prefix'] == '+') 
									{
										$option_weight += $option_value_query->row['weight'];
									} 
									elseif ($option_value_query->row['weight_prefix'] == '-') 
									{
										$option_weight -= $option_value_query->row['weight'];
									}
									
									if ($option_value_query->row['subtract'] && (!$option_value_query->row['quantity'] || ($option_value_query->row['quantity'] < $result['quantity']))) 
									{
										$stock = false;
									}
										
									$option_data[] = array(
										'product_option_id'       => $product_option_id,
										'product_option_value_id' => $product_option_value_id,
										'option_id'               => $option_query->row['option_id'],
										'option_value_id'         => $option_value_query->row['option_value_id'],
										'name'                    => $option_query->row['name'],
										'option_value'            => $option_value_query->row['name'],
										'type'                    => $option_query->row['type'],
										'quantity'                => $option_value_query->row['quantity'],
										'subtract'                => $option_value_query->row['subtract'],
										'price'                   => $option_value_query->row['price'],
										'price_prefix'            => $option_value_query->row['price_prefix'],
										'points'                  => $option_value_query->row['points'],
										'points_prefix'           => $option_value_query->row['points_prefix'],
										'weight'                  => $option_value_query->row['weight'],
										'weight_prefix'           => $option_value_query->row['weight_prefix']
									);								
								}
							}						
						} 
						elseif ($option_query->row['type'] == 'text' || 
								$option_query->row['type'] == 'textarea' || 
								$option_query->row['type'] == 'file' || 
								$option_query->row['type'] == 'date' || 
								$option_query->row['type'] == 'datetime' || 
								$option_query->row['type'] == 'time') 
						{
							$option_data[] = array(
								'product_option_id'       => $product_option_id,
								'product_option_value_id' => '',
								'option_id'               => $option_query->row['option_id'],
								'option_value_id'         => '',
								'name'                    => $option_query->row['name'],
								'option_value'            => $option_value,
								'type'                    => $option_query->row['type'],
								'quantity'                => '',
								'subtract'                => '',
								'price'                   => '',
								'price_prefix'            => '',
								'points'                  => '',
								'points_prefix'           => '',								
								'weight'                  => '',
								'weight_prefix'           => ''
							);						
						}
					}
				}	 
			
				$result['price'] = $price;
				$result['total'] = $total;
				$result['option'] = $option_data;
				$json['order_product'][] = $result;
			}
		}
		
		//print_r($json);
		
		echo json_encode($json);
	}
	
	private function unset_session()
	{
		$this->cart->clear();
			
		unset($this->session->data['shipping_method']);
		unset($this->session->data['shipping_methods']);			
		unset($this->session->data['payment_method']);
		unset($this->session->data['payment_methods']);
		unset($this->session->data['coupon']);
		unset($this->session->data['reward']);
		unset($this->session->data['voucher']);
		unset($this->session->data['vouchers']);
		
		unset($this->session->data['recurring']);
		unset($this->session->data['recurring_frequency']);
	}
	
	public function json()
	{
		$this->unset_session();
		$json = array();
		
		print_r($this->request->post);
		
		$this->unset_session();
		echo json_encode($json);
	}
}
?>