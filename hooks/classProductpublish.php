<?php

/**
 * 
 */
class classPublish {

    function __construct() {
        
        //Order Complete/Edit Hook
        add_action('woocommerce_payment_complete', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_pending', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_failed', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_on-hold', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_processing', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_completed', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_refunded', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_cancelled', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_pending_to_processing_notification', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_pending_to_completed_notification', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_pending_to_on-hold_notification', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_failed_to_processing_notification', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_failed_to_completed_notification', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_failed_to_on-hold_notification', array($this, 'custom_process_order'));
        add_action('woocommerce_order_status_completed_notification', array($this, 'custom_process_order'));
		add_action('woocommerce_thankyou', array($this, 'custom_process_order'));
		
		// hooks for create/edit customer
		add_action('user_register', array($this, 'custom_process_customer'));
		add_action('woocommerce_api_create_customer', array($this, 'custom_process_customer'));
		add_action('woocommerce_api_create_customer_data', array($this, 'custom_process_customer'));
		add_action('woocommerce_api_edit_customer', array($this, 'custom_process_customer'));
		add_action('woocommerce_api_edit_customer_data', array($this, 'custom_process_customer'));		
		add_action('woocommerce_created_customer', array($this, 'custom_process_customer'));
		add_action('updated_user_meta', array($this, 'custom_process_customer2'), 10, 4);
		
		
		//These two hooks will work when product categories are added or updated.
        add_action('create_product_cat', array($this, 'custom_process_category'));
        add_action('edited_product_cat', array($this, 'custom_process_category'));		 
		
		//publish function
		add_action('grey_ferret_publish_job', array($this, 'grey_ferret_publish_function'));
		}
		
		public function custom_process_customer($customer_id) {
			if (get_option('greyFerretEnabled')) {
				$greyFerretEnabled = get_option('greyFerretEnabled');
			}
			if (get_option('jobStatus')) {
				$jobStatus = get_option('jobStatus');
			}
			$flag = true;
			if($greyFerretEnabled != '' && $jobStatus != '') {
				$res = $this->publishCustomerById($customer_id);
				if($res != true) {
					$flag = false;
				} else {
					update_option('lastcustomer', $customer_id);
				}	
			}			
			
		}
		
		public function custom_process_customer2($meta_id, $customer_id, $meta_key, $_meta_value) {
			if (get_option('greyFerretEnabled')) {
				$greyFerretEnabled = get_option('greyFerretEnabled');
			}
			if (get_option('jobStatus')) {
				$jobStatus = get_option('jobStatus');
			}
			$flag = true;
			if($greyFerretEnabled != '' && $jobStatus != '') {
				$res = $this->publishCustomerById($customer_id);
				if($res != true) {
					$flag = false;
				} else {
					update_option('lastcustomer', $customer_id);
				}	
			}			
			
		}
		
		public function custom_process_category($category_id) {
			if (get_option('greyFerretEnabled')) {
				$greyFerretEnabled = get_option('greyFerretEnabled');
			}
			if (get_option('jobStatus')) {
				$jobStatus = get_option('jobStatus');
			}
			$flag = true;
			if($greyFerretEnabled && $jobStatus) {
				$res = $this->publishCategoriesById($category_id);
				if($res != true) {
					$flag = false;
				} else {
					update_option('lastcategory', $category_id);
				}		
			}			
		}
		
		public function custom_process_order($order_id) {
			if (get_option('greyFerretEnabled')) {
				$greyFerretEnabled = get_option('greyFerretEnabled');
			}
			if (get_option('jobStatus')) {
				$jobStatus = get_option('jobStatus');
			}
			$flag = true;
			if($greyFerretEnabled && $jobStatus) {
				$res = $this->publishOrderbyId($order_id);
				if($res != true) {
					$flag = false;
				} else {
					update_option('lastorder', $order_id);
				}
			}
		}
		
		public function publishJob() {
			if (get_option('greyFerretPublishDate')) {
				$greyFerretPublishDate = get_option('greyFerretPublishDate');
			}
			if (get_option('greyFerretPublishTime')) {
				$greyFerretPublishTime = get_option('greyFerretPublishTime');
			}
			$date = new DateTime('' . $greyFerretPublishDate . ' ' . $greyFerretPublishTime);
			$scheduleTimeStamp =  $date->getTimestamp();
			if(wp_next_scheduled('grey_ferret_publish_job')) {				
				wp_clear_scheduled_hook( 'grey_ferret_publish_job');
			}
			update_option('jobStatus', 'Scheduled');
			wp_schedule_single_event($scheduleTimeStamp, 'grey_ferret_publish_job');
			return 'success';
		}
		
		
	
		public function grey_ferret_publish_function() {	
			update_option('jobStatus', 'Running');			
			$cus_res = $this->publishCustomers();			
			if($cus_res == true) {		
				$cat_res = $this->publishCategories();
				if($cat_res == true) {	
					$ord_res = $this->publishOrders();
					if($ord_res == true) {
						update_option('jobStatus', 'Completed');
					} else
						update_option('jobStatus', 'Not completed. Error encountered in Orders.');
				} else
					update_option('jobStatus', 'Not completed. Error encountered in Categories.');
			} else
				update_option('jobStatus', 'Not completed. Error encountered in Customers.');
		}

		public function publishCustomers() {		
			$wc_customers = get_users( '' );
			$flag = true;
			foreach ( $wc_customers as $wc_customer ) {
				$res = $this->publishCustomerById($wc_customer->ID);
				if($res != true) {
					$flag = false;
					break;
				}
				update_option('lastcustomer', $wc_customer->ID);
			}
			$file = fopen('logscus.txt', 'a');
			fwrite($file, 'flag : ' . $flag);
			fclose();
			return $flag;
		 }
		 
		public function publishCustomerById($id) {				
				$flag = true;
				$grey_ferret_customer = array();
				$user = get_user_by('id', $id);
				global $wpdb;
				$results = $wpdb->get_results( 'SELECT * FROM wp_usermeta WHERE user_id = '. $id, OBJECT );
				foreach ($results as $page) {
					if($page->meta_key == 'first_name')
						$firstname = $page->meta_value;
					if($page->meta_key == 'last_name')
						$lastname = $page->meta_value;
					if($page->meta_key == 'billing_address_1')
						$grey_ferret_customer["Address1"] = $page->meta_value;
					if($page->meta_key == 'billing_address_2')
						$grey_ferret_customer["Address2"] = $page->meta_value;
					if($page->meta_key == 'billing_city')
						$grey_ferret_customer["city"] = $page->meta_value;
					if($page->meta_key == 'billing_state')
						$grey_ferret_customer["state"] = $page->meta_value;
					if($page->meta_key == 'billing_postcode')
						$grey_ferret_customer["pinCode"] = $page->meta_value;
					if($page->meta_key == 'billing_country')
						$grey_ferret_customer["country"] = $page->meta_value;
					if($page->meta_key == 'billing_phone')
						$grey_ferret_customer["mobileNumber"] = $page->meta_value;
				}				
				if($firstname=='') {
					$firstname = 'No name';
				}
				if($lastname=='') {
					$lastname = 'No name';
				}
				$grey_ferret_customer["Email_Del_Reason"] = '';
				$grey_ferret_customer["customerId"] = $id;
				$grey_ferret_customer["firstName"] = $firstname;
				$grey_ferret_customer["lastName"] = $lastname;
				$grey_ferret_customer["email1"] = (isset($user->user_email) && $user->user_email!='') ? $user->user_email : 'abc@example.com';
				$grey_ferret_customer["activeStatus"] = 'Y';
				$grey_ferret_customer["createdBy"] = "Woocommerce";
				$grey_ferret_customer["maritalStatus"] = '';
				$grey_ferret_customer["memberType"] = '';
				$grey_ferret_customer["occupation"] = '';
				$res = $this->grey_ferret_post_curl('customer', $grey_ferret_customer);
				if($res != true) {
					$flag = false;
				}
				return $flag;
		 }

		public function publishCategories() {
			$flag = true;
			$taxonomy     = 'product_cat';
			$orderby      = 'name';  
			$show_count   = 0;      
			$pad_counts   = 0;      
			$hierarchical = 1;  
			$title        = '';  
			$empty        = 0;

			$args = array(
				'taxonomy'     => $taxonomy,
				'orderby'      => $orderby,
				'show_count'   => $show_count,
				'pad_counts'   => $pad_counts,
				'hierarchical' => $hierarchical,
				'title_li'     => $title,
				'hide_empty'   => $empty
			);
			$all_categories = get_categories( $args );
			foreach ($all_categories as $cat) {
				$category_id = $cat->cat_ID;								
				$res = $this->publishCategoriesById($category_id);
				if($res != true) {
					$flag = false;
					break;
				}
				update_option('lastcategory', $category_id);
			}
			return $flag;
		}

		public function publishCategoriesById($id){
			$flag = true;
			$category = get_term($id, 'product_cat');			
			$grey_ferret_category["categoryDesc"] = isset($category->description) ? $category->description : '';
			$grey_ferret_category["categoryId"] = $id;
			$grey_ferret_category["categoryName"] = isset($category->name) ? $category->name : '';
			$grey_ferret_category["createdBy"] = "Woocommerce";
			$grey_ferret_category["parentCategoryId"] = isset($category->parent) ? $category->parent : '';
			$res = $this->grey_ferret_post_curl('category', $grey_ferret_category);
			if($res != true) {
				$flag = false;
			}
			return $flag;
		}

		public function publishOrders() {
			$flag = true;
			$filters = array(
			'post_status' => 'any',
			'post_type' => 'shop_order',
			'posts_per_page' => -1,
			'orderby' => 'modified',
			'order' => 'ASC'
			);

			$loop = new WP_Query($filters);

			while ($loop->have_posts()) {
				$loop->the_post();
				$order_id = $loop->post->ID;
				$res = $this->publishOrderbyId($order_id);
				if($res != true) {
					$flag = false;
					break;
				}
				update_option('lastorder', $order_id);
				$order = new WC_Order($loop->post->ID);
			}
			return $flag;
		}
		
		public function publishOrderbyId($id) {
			$flag = true;
			$order = new WC_Order($id);
			if($order->post->post_status == 'wc-completed') {
				$grey_ferret_order = array();
				$grey_ferret_order["Order_id"] = $id;
				$grey_ferret_order["customer_id"] = $order->get_user_id();
				$grey_ferret_order["createdBy"] = "Woocommerce";
				$grey_ferret_order["currency_code"] = get_woocommerce_currency();
				$grey_ferret_order["date_created"] = $order->order_date;
				$grey_ferret_order["purchaseDate"] = $order->order_date;
				$grey_ferret_order["products"] = array();
				$i = 0;
				foreach ($order->get_items() as $key => $lineItem) {
					$terms = get_the_terms($lineItem['product_id'], 'product_cat' );
					$product_cat_id = $terms[0]->term_id;
					$category = get_term($product_cat_id, 'product_cat');
					$product = get_post($lineItem['product_id']);
					$grey_ferret_order["products"][$i]["categoryId"] = $product_cat_id;
					$grey_ferret_order["products"][$i]["categoryName"] = isset($category->name) ? $category->name : 'No name';
					$grey_ferret_order["products"][$i]["discount"] = 0.000;
					$grey_ferret_order["products"][$i]["qty"] = isset($lineItem["qty"]) ? $lineItem["qty"] : 'No qty';
					$grey_ferret_order["products"][$i]["sku"] = isset(get_post_meta($lineItem['product_id'])['_sku'][0]) ? get_post_meta($lineItem['product_id'])['_sku'][0] : 'No sku';
					$grey_ferret_order["products"][$i]["skuName"] = isset($product->post_title) ? $product->post_title : 'No sku name';
					$grey_ferret_order["products"][$i]["totalAmount"] = $lineItem["line_total"];
					$i++;
				}
				$res = $this->grey_ferret_post_curl('order', $grey_ferret_order);
				if($res != true) {
					$flag = false;
				}
			}			
			return $flag;
		}
	
		public function grey_ferret_post_curl($type, $data) {
			$flag = true;
			if (get_option('greyFerretUsername')) {
				$greyFerretUsername = get_option('greyFerretUsername');
			}
			if (get_option('greyFerretPassword')) {
				$greyFerretPassword = get_option('greyFerretPassword');
			}
			if (get_option('greyFerretApiUrl')) {
				$greyFerretApiUrl = get_option('greyFerretApiUrl');
			}
			$data_temp = array();
			$data_temp[0] = $data;
			$data_string = json_encode($data_temp);
			$service_url = $greyFerretApiUrl . '/' . $type;
			$curl = curl_init($service_url);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_USERPWD, $greyFerretUsername . ":" . $greyFerretPassword);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				 "Content-Type: application/json"
			));
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			$curl_response = curl_exec($curl);
			$response = json_decode($curl_response);
			$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if($status != 200) {
				$flag = false;
			}
			curl_close($curl);	
			return $flag;
		}

}

new classPublish;
