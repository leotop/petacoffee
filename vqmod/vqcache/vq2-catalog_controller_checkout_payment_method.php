<?php  
class ControllerCheckoutPaymentMethod extends Controller {
  	public function index() {
		$this->language->load('checkout/checkout');

        $this->data['button_voucher'] = $this->language->get('button_voucher');
        $this->data['entry_voucher'] = $this->language->get('entry_voucher');
        $this->data['text_voucher'] = $this->language->get('text_voucher');
        $this->data['show_during_checkout'] = $this->config->get('show_during_checkout');
      
		
		$this->load->model('account/address');
		
		if ($this->customer->isLogged() && isset($this->session->data['payment_address_id'])) {
			$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);		
		} elseif (isset($this->session->data['guest'])) {
			$payment_address = $this->session->data['guest']['payment'];
		}	
		
		if (!empty($payment_address)) {
			// Totals
			$total_data = array();					
			$total = 0;
			$taxes = $this->cart->getTaxes();
			
			$this->load->model('setting/extension');
			
			$sort_order = array(); 
			
			$results = $this->model_setting_extension->getExtensions('total');
			
			foreach ($results as $key => $value) {
				$sort_order[$key] = $this->config->get($value['code'] . '_sort_order');
			}
			
			array_multisort($sort_order, SORT_ASC, $results);
			
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
					$this->load->model('total/' . $result['code']);
		
					$this->{'model_total_' . $result['code']}->getTotal($total_data, $total, $taxes);
				}
			}
			
			// Payment Methods
			$method_data = array();
			
			$this->load->model('setting/extension');
			
			$results = $this->model_setting_extension->getExtensions('payment');
	
			foreach ($results as $result) {
				if ($this->config->get($result['code'] . '_status')) {
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
			
			$this->session->data['payment_methods'] = $method_data;	
			
		}			
		
		$this->data['text_payment_method'] = $this->language->get('text_payment_method');
		$this->data['text_comments'] = $this->language->get('text_comments');

        $this->language->load('total/gift_wrap');
        $this->data['text_gift_wrap_heading'] = $this->language->get('text_gift_wrap_heading');
        $this->data['text_gift_wrap_info'] = $this->language->get('text_gift_wrap_info');
        $this->data['text_gift_wrap_heading'] = $this->language->get('text_gift_wrap_heading');
        $this->data['text_gift_wrap_note'] = $this->language->get('text_gift_wrap_note');
        $this->data['gift_wrap_status'] = $this->config->get('gift_wrap_status');
        $this->data['gift_wrap_show_on_shipping'] = $this->config->get('gift_wrap_show_on_shipping');
        $this->data['gift_wrap_show_on_payment'] = $this->config->get('gift_wrap_show_on_payment');
        $this->data['gift_wrap_use_note_field'] = $this->config->get('gift_wrap_use_note_field');
        $this->data['gift_wrap_fee'] = $this->currency->format($this->tax->calculate($this->config->get('gift_wrap_fee'), $this->config->get('gift_wrap_tax_class_id'), $this->config->get('config_tax')));
        if($this->config->get('gift_wrap_calculation_method') == 'per_qty'){
          $this->data['gift_wrap_fee_note'] = $this->language->get('text_note_per_qty');
        } else if($this->config->get('gift_wrap_calculation_method') == 'per_product'){
          $this->data['gift_wrap_fee_note'] = $this->language->get('text_note_per_product');
        } else {
          $this->data['gift_wrap_fee_note'] = '';
        }
        if(isset($this->session->data['gift_wrap'])){
          $this->data['gift_wrap'] = true;
        } else {
          $this->data['gift_wrap'] = false;
        }
        if(isset($this->session->data['gift_wrap_note'])){
          $this->data['gift_wrap_note'] = $this->session->data['gift_wrap_note'];
        } else {
          $this->data['gift_wrap_note'] = '';
        }
      

		$this->data['button_continue'] = $this->language->get('button_continue');
   
		if (empty($this->session->data['payment_methods'])) {
			$this->data['error_warning'] = sprintf($this->language->get('error_no_payment'), $this->url->link('information/contact'));
		} else {
			$this->data['error_warning'] = '';
		}	

		if (isset($this->session->data['payment_methods'])) {
			$this->data['payment_methods'] = $this->session->data['payment_methods']; 
		} else {
			$this->data['payment_methods'] = array();
		}
	  
		if (isset($this->session->data['payment_method']['code'])) {
			$this->data['code'] = $this->session->data['payment_method']['code'];
		} else {
			$this->data['code'] = '';
		}
		
		if (isset($this->session->data['comment'])) {
			
        $this->data['comment'] = preg_replace('/' . $this->language->get('text_gift_wrap_note') . '(.*)/ims', '', $this->session->data['comment']);
      
		} else {
			$this->data['comment'] = '';
		}
		
		if ($this->config->get('config_checkout_id')) {
			$this->load->model('catalog/information');
			
			$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
			
			if ($information_info) {
				$this->data['text_agree'] = sprintf($this->language->get('text_agree'), $this->url->link('information/information/info', 'information_id=' . $this->config->get('config_checkout_id'), 'SSL'), $information_info['title'], $information_info['title']);
			} else {
				$this->data['text_agree'] = '';
			}
		} else {
			$this->data['text_agree'] = '';
		}
		
		if (isset($this->session->data['agree'])) { 
			$this->data['agree'] = $this->session->data['agree'];
		} else {
			$this->data['agree'] = '';
		}
		
		
		
		
		
			
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/checkout/payment_method.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/checkout/payment_method.tpl';
		} else {
			$this->template = 'default/template/checkout/payment_method.tpl';
		}
		
		$this->response->setOutput($this->render());
  	}
	
	public function validate() {
		$this->language->load('checkout/checkout');

        $this->data['button_voucher'] = $this->language->get('button_voucher');
        $this->data['entry_voucher'] = $this->language->get('entry_voucher');
        $this->data['text_voucher'] = $this->language->get('text_voucher');
        $this->data['show_during_checkout'] = $this->config->get('show_during_checkout');
      
		
		$json = array();
		
		// Validate if payment address has been set.
		$this->load->model('account/address');
		
		if ($this->customer->isLogged() && isset($this->session->data['payment_address_id'])) {
			$payment_address = $this->model_account_address->getAddress($this->session->data['payment_address_id']);		
		} elseif (isset($this->session->data['guest'])) {
			$payment_address = $this->session->data['guest']['payment'];
		}	
				
		if (empty($payment_address)) {
			$json['redirect'] = $this->url->link('checkout/checkout', '', 'SSL');
		}		
		
		// Validate cart has products and has stock.			
		if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
			$json['redirect'] = $this->url->link('checkout/cart');				
		}	
		
		// Validate minimum quantity requirments.			
		$products = $this->cart->getProducts();
				
		foreach ($products as $product) {
			$product_total = 0;
				
			foreach ($products as $product_2) {
				if ($product_2['product_id'] == $product['product_id']) {
					$product_total += $product_2['quantity'];
				}
			}		
			
			if ($product['minimum'] > $product_total) {
				$json['redirect'] = $this->url->link('checkout/cart');
				
				break;
			}				
		}
		
		
		
		
											
		if (!$json) {
			if (!isset($this->request->post['payment_method'])) {
				$json['error']['warning'] = $this->language->get('error_payment');
			} elseif (!isset($this->session->data['payment_methods'][$this->request->post['payment_method']])) {
				$json['error']['warning'] = $this->language->get('error_payment');
			}	
							
			if ($this->config->get('config_checkout_id')) {
				$this->load->model('catalog/information');
				
				$information_info = $this->model_catalog_information->getInformation($this->config->get('config_checkout_id'));
				
				if ($information_info && !isset($this->request->post['agree'])) {
					$json['error']['warning'] = sprintf($this->language->get('error_agree'), $information_info['title']);
				}
			}
			
			if (!$json) {
				$this->session->data['payment_method'] = $this->session->data['payment_methods'][$this->request->post['payment_method']];
			  
				$this->session->data['comment'] = strip_tags($this->request->post['comment']);

        if ($this->config->get('gift_wrap_show_on_payment')) {
          if (isset($this->request->post['gift_wrap']) && $this->request->post['gift_wrap']) {
            $this->session->data['gift_wrap'] = $this->request->post['gift_wrap'];
			if (isset($this->request->post['gift_wrap_note'])) {
				$this->session->data['gift_wrap_note'] = strip_tags($this->request->post['gift_wrap_note']);
			}
          } else {
            unset($this->session->data['gift_wrap']);
            unset($this->session->data['gift_wrap_note']);
          }
        }
      
			}							
		}
		
		$this->response->setOutput(json_encode($json));
	}
}
?>