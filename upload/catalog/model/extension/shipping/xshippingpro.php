<?php 
class ModelExtensionShippingXshippingpro extends Model {

    function getQuote($address) {

        $this->load->language('extension/shipping/xshippingpro');

        $language_id=$this->config->get('config_language_id');
        $store_id=(isset($_POST['store_id']))?$_POST['store_id']:$this->config->get('config_store_id');
        $payment_method=isset($this->session->data['payment_method']['code'])?$this->session->data['payment_method']['code']:'';
        
        if(isset($this->session->data['default']['payment_method']['code'])) $payment_method = $this->session->data['default']['payment_method']['code'];
        
        $is_admin = (isset($_REQUEST['route']) && strpos($_REQUEST['route'],'api')!==false) ? true:false;
        $is_quote = (isset($_REQUEST['route']) && strpos($_REQUEST['route'],'shipping/quote')!==false) ? true:false;
        if (isset($_GET['store_id']) && $_GET['store_id'] != "") {
            $store_id = $_GET['store_id'];
        }

        
        if (!isset($address['zone_id'])) $address['zone_id'] = '';
        if (!isset($address['country_id'])) $address['country_id'] = '';
        if (!isset($address['city'])) $address['city'] = '';
        if (!isset($address['postcode'])) $address['postcode'] = '';

        
        /*Quick checkout fucking bug fix*/
        if (!$address['zone_id']
            && isset($this->session->data['shipping_address'])
            && isset($this->session->data['shipping_address']['zone_id'])
            && $this->session->data['shipping_address']['zone_id']) {
            $address['zone_id'] = $this->session->data['shipping_address']['zone_id'];
        }

        /*Quick checkout fucking bug fix*/
        if (!$address['country_id']
            && isset($this->session->data['shipping_address'])
            && isset($this->session->data['shipping_address']['country_id'])
            && $this->session->data['shipping_address']['country_id']) {
            $address['country_id'] = $this->session->data['shipping_address']['country_id'];
        } 

        /*Quick checkout f***ing bug fix*/
        if (!$address['city']
            && isset($this->session->data['shipping_address'])
            && isset($this->session->data['shipping_address']['city'])
            && $this->session->data['shipping_address']['city']) {
          $address['city'] = $this->session->data['shipping_address']['city'];
        }  

        /*Quick checkout f***ing bug fix*/
        if (!$address['postcode']
            && isset($this->session->data['shipping_address'])
            && isset($this->session->data['shipping_address']['postcode'])
            && $this->session->data['shipping_address']['postcode']) {
          $address['postcode'] = $this->session->data['shipping_address']['postcode'];
        } 

        /* still not set, try from payment address */
        /*Quick checkout fucking bug fix*/
        if (!$address['zone_id']
            && isset($this->session->data['payment_address'])
            && isset($this->session->data['payment_address']['zone_id'])
            && $this->session->data['payment_address']['zone_id']) {
            $address['zone_id'] = $this->session->data['payment_address']['zone_id'];
        }

        /*Quick checkout fucking bug fix*/
        if (!$address['country_id']
            && isset($this->session->data['payment_address'])
            && isset($this->session->data['payment_address']['country_id'])
            && $this->session->data['payment_address']['country_id']) {
            $address['country_id'] = $this->session->data['payment_address']['country_id'];
        } 

        /*Quick checkout f***ing bug fix*/
        if (!$address['city']
            && isset($this->session->data['payment_address'])
            && isset($this->session->data['payment_address']['city'])
            && $this->session->data['payment_address']['city']) {
          $address['city'] = $this->session->data['payment_address']['city'];
        }  

        /*Quick checkout f***ing bug fix*/
        if (!$address['postcode']
            && isset($this->session->data['payment_address'])
            && isset($this->session->data['payment_address']['postcode'])
            && $this->session->data['payment_address']['postcode']) {
          $address['postcode'] = $this->session->data['payment_address']['postcode'];
        } 
        /* end of address adjustment */

        if (!$address['country_id']) {
            $address['country_id'] = $this->config->get('config_country_id');
        }

        /* all option has failed, lets fetch from address book */
        if (!$address['postcode'] && !$address['city'] && $this->customer->isLogged()) {
            $this->load->model('account/address');
            $customer_address = $this->model_account_address->getAddress($this->customer->getAddressId());
            if ($customer_address) {
                $address['postcode'] = $customer_address['postcode'];
                $address['city'] = $customer_address['city'];
            }
        }

        $method_data = array();
        $quote_data = array();
        $sort_data = array(); 

        $xshippingpro_heading=$this->config->get('shipping_xshippingpro_heading');
        $xshippingpro_group=$this->config->get('shipping_xshippingpro_group');
        $xshippingpro_group_limit=$this->config->get('shipping_xshippingpro_group_limit');
        $xshippingpro_sub_group=$this->config->get('shipping_xshippingpro_sub_group');
        $xshippingpro_sub_group_limit=$this->config->get('shipping_xshippingpro_sub_group_limit');
        $xshippingpro_sub_group_name=$this->config->get('shipping_xshippingpro_sub_group_name');
        $xshippingpro_debug=$this->config->get('shipping_xshippingpro_debug');

        $xshippingpro_group=($xshippingpro_group)?$xshippingpro_group:'no_group';
        $xshippingpro_group_limit=($xshippingpro_group_limit)?(int)$xshippingpro_group_limit:1;

        $xshippingpro_sub_group=($xshippingpro_sub_group)?$xshippingpro_sub_group:array();
        $xshippingpro_sub_group_limit=($xshippingpro_sub_group_limit)?$xshippingpro_sub_group_limit:array();
        $xshippingpro_sub_group_name=($xshippingpro_sub_group_name)?$xshippingpro_sub_group_name:array();

        $xshippingpro_sorting=$this->config->get('shipping_xshippingpro_sorting');
        $xshippingpro_sorting = ($xshippingpro_sorting)?(int)$xshippingpro_sorting:1;

        $currency_code = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
        $currency_id = $this->currency->getId($currency_code);

        /* product profile */
        $multi_category = false;
        $cart_product_ids = array();
        $cart_categories = array();
        $cart_manufacturers = array();
        $cart_options = array();
        $cart_locations = array();
        $cart_volume = 0;
        
        /* basic cart */
        $_xtaxes = $this->cart->getTaxes();
        $cart_products = $this->cart->getProducts();
        $cart_weight = $this->cart->getWeight(); 
        $cart_quantity = $this->cart->countProducts();
        $cart_subtotal=$this->cart->getSubTotal();
        $cart_total = $this->cart->getTotal();

        /* total module */
        $grand_total = 0;
        $grand_shipping = 0;
        $coupon_value = 0;
        $reward = 0;
        

        $coupon_code = '';
        if (isset($this->session->data['default']['coupon']) && $this->session->data['default']['coupon']) {
            $coupon_code = $this->session->data['default']['coupon'];
        }
        if (isset($this->session->data['coupon']) && $this->session->data['coupon']) {
            $coupon_code = $this->session->data['coupon'];
        }

        if ($coupon_code) {
             $coupon_code = strtolower($coupon_code);
        }

        $_flags = array(
            'coupon' => false,
            'grand' => false,
            'profile' => false
        );
        $detailProfileType = array('volume','dimensional','volumetric','no_category','no_manufacturer','no_location');
        $operators= array('+','-','/','*');
        $debugging=array();
        $shipping_group_methods=array();
        $isGrandFound = false; 
        $isSubGroupFound = false;
        $hiddenMethods = array();
        $hiddenInactiveMethods = array();

        $methods = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xshippingpro` order by `sort_order` asc")->rows;

        foreach($methods as $single_method) {

            $no_of_tab = $single_method['tab_id'];
            $xshippingpro = $single_method['method_data'];
            $xshippingpro = @unserialize(@base64_decode($xshippingpro));
            if (!is_array($xshippingpro)) $xshippingpro = array();

            $debugging_message=array();

            if (!isset($xshippingpro['customer_group'])) $xshippingpro['customer_group']=array();
            if (!isset($xshippingpro['geo_zone_id'])) $xshippingpro['geo_zone_id']=array();
            if (!isset($xshippingpro['product_category'])) $xshippingpro['product_category']=array();
            if (!isset($xshippingpro['product_product'])) $xshippingpro['product_product']=array();
            if (!isset($xshippingpro['store'])) $xshippingpro['store']=array();
            if (!isset($xshippingpro['manufacturer'])) $xshippingpro['manufacturer']=array();
            if (!isset($xshippingpro['payment'])) $xshippingpro['payment']=array();
            if (!isset($xshippingpro['days'])) $xshippingpro['days']=array();
            if (!isset($xshippingpro['rate_start'])) $xshippingpro['rate_start']=array();
            if (!isset($xshippingpro['rate_end'])) $xshippingpro['rate_end']=array();
            if (!isset($xshippingpro['rate_total'])) $xshippingpro['rate_total']=array();
            if (!isset($xshippingpro['rate_block'])) $xshippingpro['rate_block']=array();
            if (!isset($xshippingpro['rate_partial'])) $xshippingpro['rate_partial']=array();
            if (!isset($xshippingpro['country'])) $xshippingpro['country']=array();

            if (!is_array($xshippingpro['customer_group'])) $xshippingpro['customer_group']=array();
            if (!is_array($xshippingpro['geo_zone_id'])) $xshippingpro['geo_zone_id']=array();
            if (!is_array($xshippingpro['product_category'])) $xshippingpro['product_category']=array();
            if (!is_array($xshippingpro['product_product'])) $xshippingpro['product_product']=array();
            if (!is_array($xshippingpro['store'])) $xshippingpro['store']=array();
            if (!is_array($xshippingpro['manufacturer'])) $xshippingpro['manufacturer']=array();
            if (!is_array($xshippingpro['payment'])) $xshippingpro['payment']=array();
            if (!is_array($xshippingpro['days'])) $xshippingpro['days']=array();
            if (!is_array($xshippingpro['rate_start'])) $xshippingpro['rate_start']=array();
            if (!is_array($xshippingpro['rate_end'])) $xshippingpro['rate_end']=array();
            if (!is_array($xshippingpro['rate_total'])) $xshippingpro['rate_total']=array();
            if (!is_array($xshippingpro['rate_block'])) $xshippingpro['rate_block']=array();
            if (!is_array($xshippingpro['rate_partial'])) $xshippingpro['rate_partial']=array();
            if (!is_array($xshippingpro['country'])) $xshippingpro['country']=array();

            if (!isset($xshippingpro['inc_weight'])) $xshippingpro['inc_weight']='';
            if (!isset($xshippingpro['dimensional_overfule'])) $xshippingpro['dimensional_overfule']='';
            if (!isset($xshippingpro['dimensional_factor']) || !$xshippingpro['dimensional_factor'])$xshippingpro['dimensional_factor']= ($xshippingpro['rate_type']=='volume')?1:6000;

            if (!isset($xshippingpro['desc'])) $xshippingpro['desc']=array();
            if (!is_array($xshippingpro['desc'])) $xshippingpro['desc']=array();
            if (!isset($xshippingpro['name'])) $xshippingpro['name']=array();
            if (!is_array($xshippingpro['name'])) $xshippingpro['name']=array();

            if (!isset($xshippingpro['customer_group_all'])) $xshippingpro['customer_group_all']='';
            if (!isset($xshippingpro['geo_zone_all'])) $xshippingpro['geo_zone_all']='';
            if (!isset($xshippingpro['store_all'])) $xshippingpro['store_all']='';
            if (!isset($xshippingpro['manufacturer_all'])) $xshippingpro['manufacturer_all']='';
            if (!isset($xshippingpro['postal_all'])) $xshippingpro['postal_all']='';
            if (!isset($xshippingpro['coupon_all'])) $xshippingpro['coupon_all']='';
            if (!isset($xshippingpro['payment_all'])) $xshippingpro['payment_all']='';
            if (!isset($xshippingpro['postal'])) $xshippingpro['postal']='';
            if (!isset($xshippingpro['coupon'])) $xshippingpro['coupon']='';
            if (!isset($xshippingpro['postal_rule'])) $xshippingpro['postal_rule']='inclusive';
            if (!isset($xshippingpro['coupon_rule'])) $xshippingpro['coupon_rule']='inclusive';
            if (!isset($xshippingpro['time_start'])) $xshippingpro['time_start']='';
            if (!isset($xshippingpro['time_end'])) $xshippingpro['time_end']='';
            if (!isset($xshippingpro['rate_final'])) $xshippingpro['rate_final']='single';
            if (!isset($xshippingpro['rate_percent'])) $xshippingpro['rate_percent']='sub';
            if (!isset($xshippingpro['rate_min'])) $xshippingpro['rate_min']=0;
            if (!isset($xshippingpro['rate_max'])) $xshippingpro['rate_max']=0;
            if (!isset($xshippingpro['rate_add'])) $xshippingpro['rate_add']=0;
            if (!isset($xshippingpro['cart_adjust'])) $xshippingpro['cart_adjust']=0;
            //if (!isset($xshippingpro['modifier_ignore'])) $xshippingpro['modifier_ignore']='';
            if (!isset($xshippingpro['country_all'])) $xshippingpro['country_all']='';

            if (!isset($xshippingpro['manufacturer_rule'])) $xshippingpro['manufacturer_rule']='2'; 
            if (!isset($xshippingpro['additional'])) $xshippingpro['additional'] = 0;
            if (!isset($xshippingpro['additional_per'])) $xshippingpro['additional_per'] = 1;
            if (!isset($xshippingpro['additional_limit'])) $xshippingpro['additional_limit'] = PHP_INT_MAX;
            if (!isset($xshippingpro['logo'])) $xshippingpro['logo']='';
            if (!isset($xshippingpro['group'])) $xshippingpro['group']=0;

            if (!isset($xshippingpro['order_total_start'])) $xshippingpro['order_total_start']=0;
            if (!isset($xshippingpro['order_total_end'])) $xshippingpro['order_total_end']=0;
            if (!isset($xshippingpro['weight_start'])) $xshippingpro['weight_start']=0;
            if (!isset($xshippingpro['weight_end'])) $xshippingpro['weight_end']=0;
            if (!isset($xshippingpro['quantity_start'])) $xshippingpro['quantity_start']=0;
            if (!isset($xshippingpro['quantity_end'])) $xshippingpro['quantity_end']=0;
            if (!isset($xshippingpro['mask'])) $xshippingpro['mask']='';
            if (!isset($xshippingpro['equation'])) $xshippingpro['equation']='';

            if (!isset($xshippingpro['option'])) $xshippingpro['option']='1';
            if (!isset($xshippingpro['product_option'])) $xshippingpro['product_option']=array();
            if (!is_array($xshippingpro['product_option'])) $xshippingpro['product_option']=array();

            if (!isset($xshippingpro['hide']))$xshippingpro['hide']=array();
            if (!is_array($xshippingpro['hide']))$xshippingpro['hide']=array();
            if (!isset($xshippingpro['city_all']))$xshippingpro['city_all']='';
            if (!isset($xshippingpro['city']))$xshippingpro['city']='';
            if (!isset($xshippingpro['city_rule']))$xshippingpro['city_rule']='inclusive';

            if (!isset($xshippingpro['location_rule'])) $xshippingpro['location_rule']='1';
            if (!isset($xshippingpro['location'])) $xshippingpro['location']=array();
            if (!is_array($xshippingpro['location'])) $xshippingpro['location']=array();
            if (!isset($xshippingpro['method_specific'])) $xshippingpro['method_specific']='';
            if (!isset($xshippingpro['ingore_product_rule'])) $xshippingpro['ingore_product_rule']='';
            if (!isset($xshippingpro['product_or'])) $xshippingpro['product_or']='';
            if (!isset($xshippingpro['hide_inactive']))$xshippingpro['hide_inactive']=array();
            if (!is_array($xshippingpro['hide_inactive']))$xshippingpro['hide_inactive']=array();

            if(!isset($xshippingpro['currency_all'])) $xshippingpro['currency_all']='';
            if(!isset($xshippingpro['currency'])) $xshippingpro['currency']= array();
            if(!is_array($xshippingpro['currency']))$xshippingpro['currency']=array();
            if(!isset($xshippingpro['date_start'])) $xshippingpro['date_start']='';
            if(!isset($xshippingpro['date_end'])) $xshippingpro['date_end']='';

            if (!$xshippingpro['additional']) $xshippingpro['additional'] = 0;
            if (!$xshippingpro['additional_per']) $xshippingpro['additional_per'] = 1;
            if (!$xshippingpro['additional_limit']) $xshippingpro['additional_limit'] = PHP_INT_MAX;

            /* reset to "for any" if no values were set */
            $xshippingpro =  $this->resetEmptyRule($xshippingpro);

            /* backward compatibility - if all set, it means no manufacturer were set */
            if (isset($xshippingpro['manufacturer_all']) && $xshippingpro['manufacturer_all']) $xshippingpro['manufacturer_rule']='1';
            if ($xshippingpro['rate_type'] == 'total_method' || $xshippingpro['rate_type'] == 'sub_method' || $xshippingpro['rate_type'] == 'quantity_method' || $xshippingpro['rate_type'] == 'weight_method' || $xshippingpro['rate_type'] == 'dimensional_method' || $xshippingpro['rate_type'] == 'volume_method') {
                $xshippingpro['method_specific'] = '1';
            }

            if($xshippingpro['rate_type'] == 'sub_method') {
                $xshippingpro['rate_type'] = 'sub';
            } 

            if($xshippingpro['rate_type'] == 'total_method') {
                $xshippingpro['rate_type'] = 'total';
            }

            if($xshippingpro['rate_type'] == 'weight_method') {
                $xshippingpro['rate_type'] = 'weight';
            }

            if($xshippingpro['rate_type'] == 'dimensional_method') {
                $xshippingpro['rate_type'] = 'dimensional';
            }

            if($xshippingpro['rate_type'] == 'volume_method') {
                $xshippingpro['rate_type'] = 'volume';
            }

            /* change all to lowser cases*/
            if ($xshippingpro['location']) {
                $xshippingpro['location'] = array_map('strtolower', $xshippingpro['location']);
                $xshippingpro['location'] = array_map('trim', $xshippingpro['location']);
            }


            $shipping_group_methods[intval($xshippingpro['group'])][]=$no_of_tab;

            if (!$_flags['grand'] && ($xshippingpro['rate_type'] == 'grand_shipping'
                || $xshippingpro['rate_type']=='grand')) {

                $this->getGrandTotal($grand_total, $grand_shipping, $_xtaxes);
                $_flags['grand'] = true;
            }

            if (!$_flags['coupon'] && ($xshippingpro['rate_type'] == 'total_coupon' || $xshippingpro['equation'])) {
                $coupon_value = $this->evaluateATotal('coupon', $cart_total, $_xtaxes);
                $reward = $this->evaluateATotal('reward', $cart_total, $_xtaxes);
                $_flags['coupon'] = true;
            }


            if (!$_flags['profile'] && (in_array($xshippingpro['rate_type'], $detailProfileType)
                || $xshippingpro['category'] != 1
                || $xshippingpro['product'] != 1
                || $xshippingpro['manufacturer_rule'] != 1
                || $xshippingpro['option'] != 1
                || $xshippingpro['location_rule'] != 1
                || $xshippingpro['equation'] != '')) {

                    $product_profile = $this->getProductProfile($cart_products);
                    $cart_categories = $product_profile['cart_categories'];
                    $cart_product_ids = $product_profile['cart_product_ids'];
                    $cart_manufacturers = $product_profile['cart_manufacturers'];
                    $cart_options = $product_profile['cart_options'];
                    $cart_locations = $product_profile['cart_locations'];
                    $cart_volume = $product_profile['cart_volume'];
                    $multi_category = $product_profile['multi_category'];
                    $_flags['profile'] = true;
            }


            $status = true;
            $is_product_specified = false;

            if ($xshippingpro['geo_zone_id'] && $xshippingpro['geo_zone_all']!=1) {
                $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id in (" . implode(',',$xshippingpro['geo_zone_id']) . ") AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')"); 
            }

            if ($xshippingpro['geo_zone_all']!=1) {
                if ($xshippingpro['geo_zone_id'] && $query->num_rows==0) {
                    $status = false;
                    $debugging_message[]='GEO Zone';
                } 
            }

            if ($xshippingpro['city_all']!=1) {
                $city = isset($address['city']) ? strtolower(trim($address['city'])) : '';
                $cities = explode(',',trim($xshippingpro['city']));
                $city_rule = ($xshippingpro['city_rule']=='inclusive')?false:true;

                $cities = array_map('strtolower', $cities);
                $cities = array_map('trim', $cities);

                if (in_array($city, $cities)===$city_rule) {
                    $status = false;
                    $debugging_message[]='City - ('.$city.')';
                } 
            }

            if ($xshippingpro['country_all']!=1) {
                if (!in_array((int)$address['country_id'], $xshippingpro['country'])) {
                    $status = false;
                    $debugging_message[]='Country';
                } 
            }

            if (!$xshippingpro['status']) {
                $status = false;
                $debugging_message[]='Status';
            }

            /*store checking*/
            if ($xshippingpro['store_all']!=1) {
                if (!in_array((int)$store_id,$xshippingpro['store'])) {
                    $status = false;
                    $debugging_message[]='Store';
                }
            }

            /* currency */
            if($xshippingpro['currency_all']!=1){
              if(!in_array((int)$currency_id,$xshippingpro['currency'])){
                  $status = false;
                  $debugging_message[]='Currency ('.$currency_id.')';
              }
            }

            $method_categories=array();
            $exclude_categories = array();
            /* If products are assigned to multiple categories and cateogry rule is 4, 6 and 7, re-calcaluate method categories */
            if ($multi_category && ($xshippingpro['category'] == 4 || $xshippingpro['category'] == 6 || $xshippingpro['category'] == 7)) {
                
                foreach($cart_products as $product) {
                    if (array_intersect($xshippingpro['product_category'], $product['categories'])) {
                        $method_categories = array_merge($method_categories, $product['categories']); 
                    } else {
                        $exclude_categories = array_merge($exclude_categories, $product['categories']);  
                    } 
                }
                $method_categories = array_unique($method_categories);
                $method_categories = array_diff($method_categories, $exclude_categories); 
                $xshippingpro['product_category'] = $method_categories ? $method_categories : $xshippingpro['product_category'];
            }

            $resultant_category = array();
            $resultant_products = array();
            $resultant_manufacturers = array();
            $resultant_options = array();
            $resultant_locations = array();

            if ($xshippingpro['category'] !=1) {
                $resultant_category = array_intersect($xshippingpro['product_category'],$cart_categories);
                $is_product_specified = true;
            }

            if ($xshippingpro['product'] !=1) {
                $resultant_products = array_intersect($xshippingpro['product_product'],$cart_product_ids);
                $is_product_specified = true;
            }

            if ($xshippingpro['manufacturer_rule'] !=1) {
                $resultant_manufacturers = array_intersect($xshippingpro['manufacturer'],$cart_manufacturers);
                $is_product_specified = true;
            }

            if ($xshippingpro['option'] !=1) {
                $resultant_options = array_intersect($xshippingpro['product_option'],$cart_options);
                $is_product_specified = true;
            }

            if ($xshippingpro['location_rule'] !=1) {
                $resultant_locations = array_intersect($xshippingpro['location'],$cart_locations);
                $is_product_specified = true;
            }

            // print_r($xshippingpro['product_category']);
            // print_r($resultant_category);

            /*Customer group checking*/
            if (isset($_POST['customer_group_id']) && $_POST['customer_group_id']) {
                $customer_group_id=$_POST['customer_group_id'];
            }
            elseif (isset($_GET['customer_group_id']) && $_GET['customer_group_id']) {
                $customer_group_id=$_GET['customer_group_id'];
            }
            elseif ($this->customer->isLogged()) {
                $customer_group_id = $this->customer->getGroupId();
            } elseif (isset($this->session->data['customer']) && isset($this->session->data['customer']['customer_group_id']) && $this->session->data['customer']['customer_group_id']) {
                    $customer_group_id = $this->session->data['customer']['customer_group_id'];     
            } else {
                $customer_group_id = 0;
            }

            if (!in_array($customer_group_id,$xshippingpro['customer_group']) && $xshippingpro['customer_group_all']!=1) {
                $status = false; 
                $debugging_message[]='Customer Group';
            }

            
            /*Payment checking*/        
            if ($xshippingpro['payment_all'] != 1 && $payment_method) {
                if($xshippingpro['payment']){
                    if(!in_array($payment_method,$xshippingpro['payment'])){
                        $status = false; 
                        $debugging_message[]='Payment';
                    }  
                }

                if(!$xshippingpro['payment'] && $payment_method){  
                    $status = false;  
                    $debugging_message[]='Payment';
                }       
            }

            /*postal checking*/
            if ($xshippingpro['postal_all'] != 1) {
                $postal=$xshippingpro['postal']; 
                $postal_rule=$xshippingpro['postal_rule'];
                $postal_rule=($postal_rule=='inclusive')?true:false;
                $postal_found=false;
                if ($postal && isset($address['postcode'])) {
                    $deliver_postal = str_replace('-','',$address['postcode']); 
                    $postal=explode(',',trim($postal));
                    foreach($postal as $postal_code) {
                        $postal_code=trim($postal_code);

                        /* regex ifrst otherwise dash in rex can interfere range*/
                        if (substr($postal_code,0,1) == '/') {
                            if (preg_match($postal_code, trim($deliver_postal))) {
                                $postal_found=true; 
                                break;
                            }
                        }

                        /* In case of range postal code - only numeric */
                        elseif (strpos($postal_code,'-')!==false && substr_count($postal_code,'-')==1 ) {
                            list($start_postal,$end_postal)=    explode('-',$postal_code); 

                            $start_postal=(int)$start_postal;
                            $end_postal=(int)$end_postal;

                            if ( $deliver_postal >= $start_postal &&  $deliver_postal <= $end_postal) {
                                $postal_found=true;
                            }
                        }
                        /* End of range checking*/

                        /* In case of range postal code wiht prefix*/
                        elseif (strpos($postal_code,'-')!==false && substr_count($postal_code,'-')==2) {
                            list($prefix,$start_postal,$end_postal)=    explode('-',$postal_code); 
                            $start_postal=(int)$start_postal;
                            $end_postal=(int)$end_postal;

                            if ($start_postal<=$end_postal) {
                                for($i=$start_postal;$i<=$end_postal;$i++) {

                                    if (preg_match('/^'.str_replace(array('\*','\?'),array('(.*?)','[a-zA-Z0-9]'),preg_quote($prefix.$i)).'$/i',trim($deliver_postal))) {
                                        $postal_found=true; 
                                        break; 
                                    }

                                }
                            }
                        }
                        /* End of range checking*/
                        /* In case of range postal code wiht prefix and sufiix*/
                        elseif (strpos($postal_code,'-')!==false && substr_count($postal_code,'-')==3) {
                            list($prefix,$start_postal,$end_postal,$sufiix)=    explode('-',$postal_code); 
                            $start_postal=(int)$start_postal;
                            $end_postal=(int)$end_postal;

                            if ($start_postal<=$end_postal) {
                                for($i=$start_postal;$i<=$end_postal;$i++) {

                                    if (preg_match('/^'.str_replace(array('\*','\?'),array('(.*?)','[a-zA-Z0-9]'),preg_quote($prefix.$i.$sufiix)).'$/i',trim($deliver_postal))) {
                                        $postal_found=true;  
                                        break;
                                    }
                                }
                            }
                        }
                        /* End of range checking*/

                        /* In case of wildcards use code*/
                        elseif (strpos($postal_code,'*')!==false || strpos($postal_code,'?')!==false) {

                            if (preg_match('/^'.str_replace(array('\*','\?'),array('(.*?)','[a-zA-Z0-9]'),preg_quote($postal_code)).'$/i',trim($deliver_postal))) {
                                $postal_found=true;  
                            }
                        }
                        /* End of wildcards checking*/
                        else {

                            if (trim(strtolower($deliver_postal))==strtolower($postal_code)) {
                                $postal_found=true; 
                            } 
                        }
                    }

                    if ((boolean)$postal_found !== $postal_rule) {
                        $status = false;
                        $debugging_message[]='Zip/Postal -'.$address['postcode'];
                    } 
                }     
            }

            /*coupon checking*/
            if ($xshippingpro['coupon_all'] != 1) {
                $coupon=$xshippingpro['coupon']; 
                $coupon_rule=$xshippingpro['coupon_rule'];

                if ($coupon) {
                    $coupon=explode(',',trim($coupon));
                    $coupon = array_map('trim', $coupon);
                    $coupon = array_map('strtolower', $coupon);
                    $coupon_rule=($coupon_rule=='inclusive')?false:true;

                    if ($coupon_rule===false && !$coupon_code) {
                        $status = false;
                        $debugging_message[]='Coupon';
                    }

                    if ($coupon_code && in_array(trim($coupon_code),$coupon)===$coupon_rule) {
                        $status = false;
                        $debugging_message[]='Coupon';
                    } 
                }     
            }

            /*Manufacturer checking*/
            $applicable_manufacturer = $cart_manufacturers;
            $manufacturer_status = $this->validateProductData('manufacturer_rule', 'manufacturer', $resultant_manufacturers, $cart_manufacturers, $xshippingpro, $applicable_manufacturer);

            /*category checking*/
            $applicable_category = $cart_categories;
            $category_status = $this->validateProductData('category', 'product_category', $resultant_category, $cart_categories, $xshippingpro, $applicable_category);

            /*product checking*/
            $applicable_product = $cart_product_ids;
            $product_status = $this->validateProductData('product', 'product_product', $resultant_products, $cart_product_ids, $xshippingpro, $applicable_product);

            /*product option*/
            $applicable_option=$cart_options;
            $option_status = $this->validateProductData('option', 'product_option', $resultant_options, $cart_options, $xshippingpro, $applicable_option);

            /*product locations*/
            $applicable_locations = $cart_locations;
            $location_status = $this->validateProductData('location_rule', 'location', $resultant_locations, $cart_locations, $xshippingpro, $applicable_locations);

            $product_rules = ($manufacturer_status && $category_status && $product_status && $option_status && $location_status);
            if ($xshippingpro['product_or']) {
                if ($xshippingpro['manufacturer_rule'] != 1) {
                    $product_rules |= $manufacturer_status;
                }
                if ($xshippingpro['category'] != 1) {
                    $product_rules |= $category_status;
                }
                if ($xshippingpro['product'] != 1) {
                    $product_rules |= $product_status;
                }
                if ($xshippingpro['option'] != 1) {
                    $product_rules |= $option_status;
                }
                if ($xshippingpro['location_rule'] != 1) {
                    $product_rules |= $location_status;
                } 
            }

            /* debugging purpose */
            if (!$product_rules) {
                $status = false;
                if (!$manufacturer_status) {
                    $debugging_message[]= 'Manufacturer';
                }
                if (!$category_status) {
                    $debugging_message[]= 'Category';
                }
                if (!$product_status) {
                    $debugging_message[]= 'Product';
                }
                if (!$option_status) {
                     $debugging_message[]= 'Option';
                }
                if (!$location_status) {
                     $debugging_message[]= 'Location';
                }
            }


            /*Days of week checking*/
            $day=date('w');
            if (!in_array($day,$xshippingpro['days'])) {
                $status = false; 
                $debugging_message[]='Day Option';
            }
            /* Day checking*/

            /* Date checking */
            $date = date('Y-m-d');
            if ($xshippingpro['date_start'] != "" && $xshippingpro['date_end']) {
                if ($date < $xshippingpro['date_start'] ||  $date > $xshippingpro['date_end']) {
                    $status = false; 
                    $debugging_message[]='Date Rule ('.$date.')';
                }
            }

            /*time checking*/

            $time=date('G'); /* 'G' return 0-23 */
            if ($xshippingpro['time_start'] != "" && $xshippingpro['time_end']) {
                
                $time_start = (int)$xshippingpro['time_start'];
                $time_end = (int)$xshippingpro['time_end'];

                if ($time_start >= 12 && $time_start > $time_end) {
                    $time_start -= 12;
                    $time_end +=12;
                    if ($time >= 12) $time -=12;
                }

                if ($time < $time_start || $time >= $time_end) {
                    $status = false; 
                    $debugging_message[]='Time Setting H: '.$time;
                }  
            }


            /*Day checking*/

            $cart_dimensional_weight = 0;
            /* Calculate dimension weight*/
            if ($xshippingpro['rate_type'] == 'dimensional') {
                foreach($cart_products as $inc=>$product) {
                    $product_dimensional_weight=($product['volume']/$xshippingpro['dimensional_factor'])*$product['weight'];
                    if ($xshippingpro['dimensional_overfule'] && $product_dimensional_weight < $product['weight']) {
                        $product_dimensional_weight= $product['weight'];
                    }
                    $cart_products[$inc]['dimensional'] = $product_dimensional_weight;
                    $cart_dimensional_weight += $product_dimensional_weight;
                }
            }
            /* End of dimension weight*/

            /* Calculate volumetric weight*/
            $volumetric_weight = 0;
            if ($xshippingpro['rate_type'] == 'volumetric') {
                foreach($cart_products as $inc => $product) {
                    $product_volumetric_weight = ($product['volume'] / $xshippingpro['dimensional_factor']);  
                    if ($xshippingpro['dimensional_overfule'] && $product_volumetric_weight < $product['weight']) {
                        $product_volumetric_weight = $product['weight'];
                    }
                    $cart_products[$inc]['volumetric'] = $product_volumetric_weight;
                    $volumetric_weight += $product_volumetric_weight;
                }
            }
            /* End of volumetric weight*/

            /* Calculate method wise data if needed*/
            $method_quantity = $is_product_specified ? 0 : $cart_quantity;  
            $method_weight = $is_product_specified ? 0 : $cart_weight;
            $method_total = $is_product_specified ? 0 : $cart_total;
            $method_sub = $is_product_specified ? 0 : $cart_subtotal;
            $method_volume = $is_product_specified ? 0 : $cart_volume;
            $method_dimensional_weight = $is_product_specified ? 0 : $cart_dimensional_weight;
            $method_volumetric_weight = $is_product_specified ? 0 : $volumetric_weight;
            $method_products = $is_product_specified ? array() : $cart_products;


            if ($is_product_specified && ($xshippingpro['method_specific'] || $xshippingpro['equation'])) {

                foreach($cart_products as $product) {

                    $count_on = false;
                    $force_off = false;

                    if ($xshippingpro['manufacturer_rule'] > 1 && in_array($product['manufacturer_id'],$applicable_manufacturer)) {
                        $count_on |= true;
                    } 
                    if ($xshippingpro['category'] > 1 && array_intersect($product['categories'],$applicable_category)) {  
                        $count_on |= true;
                    }

                    if ($xshippingpro['product'] > 1 && in_array($product['product_id'],$applicable_product)) {   
                        $count_on |= true;
                    }

                    if ($xshippingpro['option'] > 1 && array_intersect($product['options'],$applicable_option)) {   
                        $count_on |= true;
                    }

                    if ($xshippingpro['location_rule'] > 1 && in_array($product['location'],$applicable_locations)) {   
                        $count_on |= true;
                    }
                    
                    /* additional check for rule 5 and 7 i.e except ...*/
                    if (($xshippingpro['manufacturer_rule']==5 || $xshippingpro['manufacturer_rule']==7)
                        && in_array($product['manufacturer_id'], $xshippingpro['manufacturer'])) {
                        $force_off |= true;
                    }
                    if (($xshippingpro['category']==5 || $xshippingpro['category']==7)
                        && array_intersect($product['categories'], $xshippingpro['product_category'])) {
                        $force_off |= true;
                    }
                    if (($xshippingpro['product']==5 || $xshippingpro['product']==7)
                        && in_array($product['product_id'], $xshippingpro['product_product'])) {
                        $force_off |= true;
                    }
                    if (($xshippingpro['option']==5 || $xshippingpro['option']==7)
                        && array_intersect($product['options'], $xshippingpro['product_option'])) {
                        $force_off |= true;
                    }
                    if (($xshippingpro['location_rule']==5 || $xshippingpro['location_rule']==7)
                        && in_array($product['location'], $xshippingpro['location'])) {
                        $force_off |= true;
                    }

                    if (!$count_on || $force_off) continue;

                    $method_products[] = $product;
                    $method_quantity += $product['quantity'];
                    $method_weight += $product['weight'];

                    $product['tax_class_id']= isset($product['tax_class_id'])?$product['tax_class_id']:0;
                    $method_total +=  $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'];

                    $method_sub += $product['total']; 
                    $method_volume += isset($product['volume']) ? $product['volume'] : 0;
                    $method_dimensional_weight += isset($product['dimensional']) ? $product['dimensional']:0;
                    $method_volumetric_weight += isset($product['volumetric']) ? $product['volumetric']:0; 
            }
        }


        /*rate calculation*/
        $cost=0;
        $percent_to_be_considered = 0; 
        if ($xshippingpro['rate_percent'] == 'sub' || $xshippingpro['rate_percent'] == 'sub_shipping') {
              $percent_to_be_considered = $xshippingpro['method_specific'] ? $method_sub : $cart_subtotal; 
        }
        if ($xshippingpro['rate_percent'] == 'total' || $xshippingpro['rate_percent'] == 'total_shipping') {
              $percent_to_be_considered = $xshippingpro['method_specific'] ? $method_total : $cart_total;
        }

        if ($xshippingpro['rate_type']=='flat') {
            if (substr(trim($xshippingpro['cost']), -1)=='%') {
                $percent=rtrim(trim($xshippingpro['cost']),'%'); 
                $cost=(float)(($percent*$percent_to_be_considered)/100);
            }else{
                $cost=(float)$xshippingpro['cost'];  
            }  
        } else {

            $target_value=0;
            /* do a intersect becoz multi-cat rule may affect applicable cats*/
            $noOfCategory = count(array_intersect($applicable_category, $cart_categories));
            $noOfManufacturer = count(array_intersect($applicable_manufacturer, $cart_manufacturers));
            $noOfLocation = count(array_intersect($applicable_locations, $cart_locations));

            if ($xshippingpro['rate_type']=='no_category') {
                $target_value = $noOfCategory;

            }
            if ($xshippingpro['rate_type']=='no_manufacturer') {
                $target_value = $noOfManufacturer;
            }
            if ($xshippingpro['rate_type']=='no_location') {
                $target_value = $noOfLocation;
            }
            if ($xshippingpro['rate_type']=='quantity') {
                $target_value= $xshippingpro['method_specific'] ? $method_quantity : $cart_quantity;
            }

            if ($xshippingpro['rate_type']=='weight') { 
                $target_value= $xshippingpro['method_specific'] ? $method_weight : $cart_weight;
            }

            if ($xshippingpro['rate_type']=='volume') {
                $target_value= $xshippingpro['method_specific'] ? $method_volume : $cart_volume; 
            }

            if ($xshippingpro['rate_type']=='dimensional') {
                $target_value= $xshippingpro['method_specific'] ? $method_dimensional_weight : $cart_dimensional_weight;
            }

            if ($xshippingpro['rate_type']=='volumetric') {
                $target_value= $xshippingpro['method_specific'] ? $method_volumetric_weight : $volumetric_weight; 
            }

            if ($xshippingpro['rate_type']=='total') {
                $target_value= $xshippingpro['method_specific'] ? $method_total : $cart_total; 
            }

            if ($xshippingpro['rate_type']=='sub') {
                $target_value= $xshippingpro['method_specific'] ? $method_sub : $cart_subtotal; 
            }

            if ($xshippingpro['rate_type'] == 'total_coupon') {
                $target_value = $xshippingpro['method_specific'] ? ($method_total + $coupon_value + $reward)  : ($cart_total + $coupon_value + $reward);
            } 

            if ($xshippingpro['rate_type']=='grand_shipping') {
                $target_value = $grand_shipping;  
            }

            if ($xshippingpro['rate_type']=='grand') {
                $target_value = $grand_total;  
            }

            /* add cart value */
            if ($xshippingpro['cart_adjust']) {
                $cart_modifier = substr(trim($xshippingpro['cart_adjust']),0,1);
                $cart_modifier = in_array($cart_modifier,$operators) ? $cart_modifier : '+';
                $cart_modification = 0;

                if (substr(trim($xshippingpro['cart_adjust']), -1)=='%') {
                    $cart_modifier_percent = rtrim(trim($xshippingpro['cart_adjust']),'%'); 
                    $cart_modification = (float)(($cart_modifier_percent*$percent_to_be_considered)/100);    
                } else {
                    $cart_modification = (float)$xshippingpro['cart_adjust'];   
                }

                if ($cart_modifier=='+') $target_value +=$cart_modification; 
                if ($cart_modifier=='-') $target_value -=$cart_modification; 
                if ($cart_modifier=='*') $target_value *=$cart_modification; 
                if ($cart_modifier=='/') $target_value /=$cart_modification; 
            }

            if ($xshippingpro['rate_type']=='individual_quantity'
                || $xshippingpro['rate_type']=='individual_weight'
                || $xshippingpro['rate_type']=='individual_volume') {
               
               $sum_up = 0;
               foreach ($method_products as $product) {
                  if ($xshippingpro['rate_type']=='individual_quantity') {
                     $target_value = $product['quantity'];
                  } else if($xshippingpro['rate_type']=='individual_weight') {
                     $target_value = $product['weight'];
                  } else {
                     $target_value = $product['volume'];
                  }

                  $this->getPrice($xshippingpro['rate_start'],$xshippingpro['rate_end'],$xshippingpro['rate_total'],$xshippingpro['rate_block'],$xshippingpro['rate_partial'],$xshippingpro['additional'], $xshippingpro['additional_per'], $xshippingpro['additional_limit'], $target_value, $percent_to_be_considered, $cost, 'single');
                  $sum_up += $cost;
               }
               $cost = $sum_up;

            } else {
                if (!$this->getPrice($xshippingpro['rate_start'], $xshippingpro['rate_end'], $xshippingpro['rate_total'], $xshippingpro['rate_block'], $xshippingpro['rate_partial'], $xshippingpro['additional'], $xshippingpro['additional_per'], $xshippingpro['additional_limit'], $target_value, $percent_to_be_considered, $cost, $xshippingpro['rate_final'])) {
                    if (!$xshippingpro['equation'] && !$xshippingpro['rate_min'] && !$xshippingpro['rate_add']) {
                        $status = false; 
                        $debugging_message[]='Rate Type - '.$xshippingpro['rate_type'].' ('.$target_value.')';
                    }  
                }
            }

              
             /* find modifier and adjust percentate related with modifier */

             if ($xshippingpro['rate_percent']=='shipping') {
                $percent_to_be_considered = $cost;
             }
             if ($xshippingpro['rate_percent']=='sub_shipping') {
                $percent_to_be_considered = $xshippingpro['method_specific'] ? $method_sub : $cart_subtotal; 
                $percent_to_be_considered += $cost;
             }
             if ($xshippingpro['rate_percent']=='total_shipping') {
                $percent_to_be_considered = $xshippingpro['method_specific'] ? $method_total : $cart_total;
                $percent_to_be_considered += $cost;
             }

              $rate_min = $xshippingpro['rate_min'];
              if(substr(trim($rate_min), -1) == '%') {
                  $rate_min = rtrim(trim($rate_min),'%'); 
                  $rate_min = (float)(($rate_min*$percent_to_be_considered)/100);    
              }
              $rate_min = (float)$rate_min;
             

              $rate_max = $xshippingpro['rate_max'];
              if(substr(trim($rate_max), -1)=='%') {
                  $rate_max = rtrim(trim($rate_max),'%'); 
                  $rate_max = (float)(($rate_max*$percent_to_be_considered)/100);    
              }
              $rate_max = (float)$rate_max;
              

             if ($rate_min && $rate_min > $cost) {
                $cost = $rate_min;
             }

             if ($rate_max && $rate_max < $cost) {
                $cost = $rate_max;
             }

            $eq_shipping = $cost;
            $eq_modifier = 0;

            $modifier = substr(trim($xshippingpro['rate_add']),0,1);
            $modifier = in_array($modifier,$operators)?$modifier:'+';
            $xshippingpro['rate_add']=str_replace($operators,'',$xshippingpro['rate_add']);
            $modification=0;
            if (substr(trim($xshippingpro['rate_add']), -1)=='%') {
                $add_percent=rtrim(trim($xshippingpro['rate_add']),'%'); 
                $modification=(float)(($add_percent*$percent_to_be_considered)/100);     
            } else {
                $modification=(float)$xshippingpro['rate_add']; 
            }

            if ($modification) {
                if ($modifier=='+') $cost +=$modification; 
                if ($modifier=='-') $cost -=$modification; 
                if ($modifier=='*') $cost *=$modification; 
                if ($modifier=='/') $cost /=$modification; 
                $eq_modifier = $modification;
            }

            /*Equation*/
            $placholder = array('{cartTotal}','{cartQnty}','{cartWeight}', '{shipping}', '{modifier}', '{cartVolume}', '{noOfCategory}', '{noOfManufacturer}', '{noOfLocation}', '{cartTotalAsPerProductRule}', '{cartQntyAsPerProductRule}', '{cartWeightAsPerProductRule}', '{cartVolumeAsPerProductRule}', '{couponValue}');

            $replacer = array($cart_total, $cart_quantity, $cart_weight, $eq_shipping, $eq_modifier, $cart_volume, $noOfCategory, $noOfManufacturer, $noOfLocation, $method_total, $method_quantity, $method_weight, $method_volume, $coupon_value);

            if ($xshippingpro['equation']) {
                $individual_placeholders = array('{anyProductPrice}','{anyProductWeight}','{anyProductQuantity}', '{anyProductVolume}', '{anyProductWidth}', '{anyProductHeight}', '{anyProductLength}');

                if (strpos($xshippingpro['equation'], '?') !== false
                    && preg_match('/'.implode($individual_placeholders, '|').'/', $xshippingpro['equation'])) {
                    $equation = $xshippingpro['equation']; 
                    $equation = str_replace($placholder, $replacer, $equation);
                    $eq_cost = 0;
                    foreach ($method_products as $product) {
                        $individual_replacers = array($product['price'], $product['weight'], $product['quantity'], $product['volume'], $product['width'], $product['height'], $product['length']);
                        $individual_equation = str_replace($individual_placeholders, $individual_replacers, $equation);
                        $eq_cost = (float)$this->calculate_string($individual_equation);
                        if ($eq_cost) break;
                    }
                    if ($eq_cost) {
                        $cost = $eq_cost;
                    } else {
                        $status = false;
                        $debugging_message[]='Equation - Any Typed';
                    }

                } else {
                    $equation = $xshippingpro['equation']; 
                    $equation = str_replace($placholder, $replacer, $equation);
                    $cost = (float)$this->calculate_string($equation);
                }
            }
        }

        /* additional Ranges checking*/
        if ((float)$xshippingpro['order_total_end']>0) {

            if ($cart_total < (float)$xshippingpro['order_total_start'] || $cart_total> (float)$xshippingpro['order_total_end']) {
                $status = false;
                $debugging_message[]='Additional Order Total Ranges';
            } 
        }

        if ((float)$xshippingpro['weight_end']>0) {

            if ($cart_weight < (float)$xshippingpro['weight_start'] || $cart_weight > (float)$xshippingpro['weight_end']) {
                $status = false;
                $debugging_message[]='Additional Weight Ranges';
            }
        }

        if ((int)$xshippingpro['quantity_end']>0) {

            if ($cart_quantity < (int)$xshippingpro['quantity_start'] || $cart_quantity > (int)$xshippingpro['quantity_end']) {
                $status = false;
                $debugging_message[]='Additional Quantity Ranges';
            }
        }

        /* End of ranges of checking*/      

        /*Ended rate cal*/
        
        if(!isset($xshippingpro['display'])) $xshippingpro['display'] = '';
        if (!$xshippingpro['display']) {
            $xshippingpro['display'] = isset($xshippingpro['name'][$language_id]) ? isset($xshippingpro['name'][$language_id]) : '';
        }

        if (!isset($xshippingpro['name'][$language_id]) || !$xshippingpro['name'][$language_id]) {
            $status = false;
            $debugging_message[]='Name Missing';
        }


        if (!$status) {
            $debugging[]=array('name'=>$xshippingpro['display'],'filter'=>$debugging_message,'index'=>$no_of_tab);
        }

        if ($xshippingpro['inc_weight']==1 && $cart_weight>0) {
            $xshippingpro['name'][$language_id].=' ('.$this->weight->format($cart_weight, $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point')).')';
        }

        $method_desc= (isset($xshippingpro['desc'][$language_id]) && $xshippingpro['desc'][$language_id] && !$is_admin) ? '<div style="color: #999999;font-size: 11px;display:block" class="x-shipping-desc">'.$xshippingpro['desc'][$language_id].'</div>' : '';

        /* make description dynamic */
        if ($method_desc && $xshippingpro['rate_type'] !='flat') {
            $method_desc = str_replace($placholder, $replacer, $method_desc);
            if (preg_match('/\(\(.*\)\)/', $method_desc)) {
                preg_match_all('/\(.*\)/', $method_desc, $matches);
                $matches = $matches[0];
                foreach ($matches as $match) {
                    if (strrpos($match, '((') !== false) {
                        $equation = (float)$this->calculate_string($match);
                        $method_desc = str_replace($match, $equation, $method_desc);
                    }
                }
            }
        }


        if (intval($xshippingpro['group'])) {
            $isSubGroupFound = true;
        }

        /* cache for inactive hide */
        if (!$status) { 
            if(count($xshippingpro['hide_inactive']) > 0) {
                $hiddenInactiveMethods[$no_of_tab] = array(
                    'hide' => $xshippingpro['hide_inactive'],
                    'display' => $xshippingpro['display']
                );
            }
        }

        if ($status) { 

            if(count($xshippingpro['hide']) > 0) {
                $hiddenMethods[$no_of_tab] = array(
                    'hide' => $xshippingpro['hide'],
                    'display' => $xshippingpro['display']
                    );
            }

            $quote_desc = ($is_quote) ? html_entity_decode($method_desc) : '';
            $quote_data['xshippingpro'.$no_of_tab] = array(
                'code'         => 'xshippingpro'.'.xshippingpro'.$no_of_tab,
                'title'        => $xshippingpro['name'][$language_id],
                'desc'         => $method_desc ? html_entity_decode($method_desc) : '',
                'display' => $xshippingpro['display'],
                'logo'         => $xshippingpro['logo'],
                'image'         => $xshippingpro['logo'], /* for other checkout module*/
                'imgSrc' => $xshippingpro['logo'] ? '<img style="margin-right: 5px;" src="'.$xshippingpro['logo'].'" />' : '',
                'cost'         => $cost,
                'group'         => intval($xshippingpro['group']),
                'sort_order'         => intval($xshippingpro['sort_order']),
                'tax_class_id' => $xshippingpro['tax_class_id'],
                'text'         => ($xshippingpro['mask'])? $xshippingpro['mask'].$quote_desc: $this->currency->format($this->tax->calculate($cost, $xshippingpro['tax_class_id'], $this->config->get('config_tax')),$currency_code).$quote_desc
                );
         }
    }

    /* Hide methods from hide option*/
    if($hiddenMethods) {
        foreach($hiddenMethods as $hide_by => $hide_single) {
            foreach($hide_single['hide'] as $no_of_tab) {
                if(isset($quote_data['xshippingpro'.$no_of_tab])) {
                    $debugging[]=array('name'=>$quote_data['xshippingpro'.$no_of_tab]['display'],'filter'=>array('Hide by '.$hide_single['display'].' when active'),'index'=>$no_of_tab);
                    unset($quote_data['xshippingpro'.$no_of_tab]);
                }
            }  
        }
    }

    /* Hide methods from hide_inactive option*/
    if($hiddenInactiveMethods) {
        foreach($hiddenInactiveMethods as $hide_by => $hide_single) {
            foreach($hide_single['hide'] as $no_of_tab) {
                if(isset($quote_data['xshippingpro'.$no_of_tab])) {
                    $debugging[]=array('name'=>$quote_data['xshippingpro'.$no_of_tab]['display'],'filter'=>array('Hide by '.$hide_single['display'].' when inactive'),'index'=>$no_of_tab);
                    unset($quote_data['xshippingpro'.$no_of_tab]);
                }
            }  
        }
    }

    /*Finding sub grouping*/
    if ($isSubGroupFound) { 

        $grouping_methods=array();
        foreach($quote_data as $xkey=>$single) {
            $single['xkey']=$xkey;
            $grouping_methods[$single['group']][]=$single;    
        }

        $new_quote_data=array();

        foreach($grouping_methods as $sub_group_id=>$grouping_method) {

            if ($sub_group_id && $xshippingpro_sub_group[$sub_group_id] =='and' && count($grouping_method)!=count($shipping_group_methods[$sub_group_id])) {
                continue;
            }

            if (count($grouping_method)==1 || empty($sub_group_id) || $xshippingpro_sub_group[$sub_group_id] =='no_group') {

                $append_methods = array();
                foreach($grouping_method as $single) {
                    $append_methods[$single['xkey']]= $single;  
                }

                $new_quote_data = array_merge($new_quote_data,$append_methods);
                continue;
            }

            $sub_group_type = $xshippingpro_sub_group[$sub_group_id];
            $sub_group_limit = isset($xshippingpro_sub_group_limit[$sub_group_id])?$xshippingpro_sub_group_limit[$sub_group_id]:1;
            $sub_group_name = isset($xshippingpro_sub_group_name[$sub_group_id])?$xshippingpro_sub_group_name[$sub_group_id]:'';

            if (isset($grouping_method)) {
                $new_quote_data = array_merge($new_quote_data,$this->findGroup($grouping_method, $sub_group_type, $sub_group_limit, $sub_group_name));
            }

        }

        $quote_data= $new_quote_data;  

    }

    /* find top grouping*/
    if ($xshippingpro_group != 'no_group') {

        $grouping_methods=array();
        foreach($quote_data as $xkey=>$single) {
            $single['xkey']=$xkey;
            $grouping_methods[$single['sort_order']][]=$single;    
        }

        $new_quote_data=array();
        foreach($grouping_methods as $group_id=>$grouping_method) {

            if (count($grouping_method)==1) {

                $append_methods = array();
                foreach($grouping_method as $single) {
                    $append_methods[$single['xkey']]= $single;  
                }

                $new_quote_data = array_merge($new_quote_data,$append_methods);
                continue;
            }

            if (isset($grouping_method)) {
                $new_quote_data = array_merge($new_quote_data,$this->findGroup($grouping_method, $xshippingpro_group, $xshippingpro_group_limit));
            }   
        }

        $quote_data= $new_quote_data;   
    }


    /*Sorting final method*/
    $sort_order = array();
    $price_order = array();
    $name_order = array();
    foreach ($quote_data as $key => $value) {
        $sort_order[$key] = $value['sort_order'];
        $price_order[$key] = $value['cost'];
        $name_order[$key] = $value['title'];
    }

    if ( $xshippingpro_sorting == 2) {
        array_multisort($price_order, SORT_ASC, $quote_data);
    }
    elseif ( $xshippingpro_sorting == 3) {
        array_multisort($price_order, SORT_DESC, $quote_data);
    }
    elseif ( $xshippingpro_sorting == 4) {
        array_multisort($name_order, SORT_ASC, $quote_data);
    }
    elseif ( $xshippingpro_sorting == 5) {
        array_multisort($name_order, SORT_DESC, $quote_data);
    }
    else {
        array_multisort($sort_order, SORT_ASC, $quote_data);
    }


    $xshippingpro_heading=isset($xshippingpro_heading[$language_id])?$xshippingpro_heading[$language_id]:'';

    $method_data = array(
        'code'       => 'xshippingpro',
        'title'      => ($xshippingpro_heading) ? html_entity_decode($xshippingpro_heading) : $this->language->get('text_title'),
        'quote'      => $quote_data,
        'sort_order' => $this->config->get('shipping_xshippingpro_sort_order'),
        'error'      => false
        );  

    if ($xshippingpro_debug && $debugging  && !$is_admin) {
        $log_file = DIR_LOGS . 'xshippingpro.log';
        $ocm_logs = '';
        foreach($debugging as $debug) {
           $ocm_logs .= '<blockquote class="blockquote">
                           <b>Method Name:</b> '.$debug['name'].'<br />
                           <b>Method ID:</b> '.$debug['index'].'<br />
                           <b>Was Restricted By Rules:</b> '.implode(',&nbsp;&nbsp;',$debug['filter']).'
                         </blockquote>';
        }
        @file_put_contents($log_file, $ocm_logs);
    }

    if (!$quote_data) return array();
    return $method_data;
}


private function findGroup($group_method, $group_type, $group_limit, $group_name='') {

    $language_id=$this->config->get('config_language_id');
    $currency_code = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
    $return = array();
    $replacer = array();
    $replacer_price = array();
    if ($group_type=='lowest') {

        $lowest=array();
        $lowest_sort=array();

        foreach($group_method as $group_id=>$method) {

            $lowest_sort[$group_id]=$method['cost'];
            $lowest[$group_id]=$method;
            array_push($replacer, $method['title']);
            array_push($replacer_price, $this->currency->format((float)$method['cost'], $currency_code, false, true));
        }

        array_multisort($lowest_sort, SORT_ASC, $lowest);

        for($i=0;$i<$group_limit;$i++) {
            if (isset($lowest[$i]) && is_array($lowest[$i]) && $lowest[$i]) {   
                $return[$lowest[$i]['xkey']]= $lowest[$i]; 
            }
        }

    }


    if ($group_type=='highest') {


        $highest=array();
        $highest_sort=array();

        foreach($group_method as $group_id=>$method) {
            $highest_sort[$group_id]=$method['cost'];
            $highest[$group_id]=$method;
            array_push($replacer, $method['title']);
            array_push($replacer_price, $this->currency->format((float)$method['cost'], $currency_code, false, true));
        }

        array_multisort($highest_sort, SORT_DESC, $highest);

        for($i=0;$i<$group_limit;$i++) {

            if (isset($highest[$i]) && is_array($highest[$i]) && $highest[$i]) {    
                $return[$highest[$i]['xkey']]= $highest[$i]; 
            }
        } 
    } 

    if ($group_type=='average') {

        $sum=0;
        foreach($group_method as $group_id=>$method) {
            $sum+=$method['cost'];
            array_push($replacer, $method['title']);
            array_push($replacer_price, $this->currency->format((float)$method['cost'], $currency_code, false, true));
        }

        if (count($group_method)>1) {
            $group_method[0]['cost']=$sum/count($group_method); 
            $group_method[0]['text']=$this->currency->format($this->tax->calculate($group_method[0]['cost'], $group_method[0]['tax_class_id'], $this->config->get('config_tax')),$currency_code);
        }

        $return[$group_method[0]['xkey']]= $group_method[0];             
    } 


    if ($group_type=='sum') {

        $sum=0;
        foreach($group_method as $group_id=>$method) {
            $sum+=$method['cost'];
            array_push($replacer, $method['title']);
            array_push($replacer_price, $this->currency->format((float)$method['cost'], $currency_code, false, true));
        }
        $group_method[0]['cost']=$sum;
        $group_method[0]['text']=$this->currency->format($this->tax->calculate($group_method[0]['cost'], $group_method[0]['tax_class_id'], $this->config->get('config_tax')),$currency_code);
        $return[$group_method[0]['xkey']]= $group_method[0];  
    } 


    if ($group_type=='and') {

        /* If AND success, show lowest price in case price is not equal*/
        $highest = 0;
        $target = 0;
        foreach($group_method as $group_id=>$method) {
            if ($method['cost'] > $highest) {
                $target = $group_id; 
                $highest = $method['cost'];
                array_push($replacer, $method['title']);
                array_push($replacer_price, $this->currency->format((float)$method['cost'], $currency_code, false, true));
            }
        }
        $return[$group_method[$target]['xkey']]= $group_method[$target]; 
    }

    $keywords = array('#1','#2','#3','#4','#5'); 
    $group_name = str_replace($keywords,$replacer, $group_name);

    $keywords = array('@1','@2','@3','@4','@5'); 
    $group_name = str_replace($keywords,$replacer_price, $group_name);

    if (count($return)==1 && $group_name) {
        foreach($return as $key => $method) {
            $return[$key]['title'] = $group_name;
        }
    }

    return $return;
} 

   private function getPrice($start_range,$end_range,$price_range,$block_range,$partial,$additional, $additional_per,$additional_limit, $target_value,$percent_to_be_considered,&$cost, $final_price) {

        $status = false;
        $block = 0;
        $end = 0;
        $cumulative = 0;
        foreach($start_range as $index => $start) {
            $start = (float)$start;
            $end = (float)$end_range[$index];
            if (substr(trim($price_range[$index]), -1)=='%') {
                $percent = rtrim(trim($price_range[$index]),'%'); 
                $cost = (float)(($percent*$percent_to_be_considered)/100);
            } else {
                $cost = (float)$price_range[$index];  
            } 
            if (round($start,3) <= round($target_value,3) && round($target_value,3) <= round($end,3)) {
                $status = true; 
                $end = $target_value;
            }
            $block=((float)$block_range[$index])?(float)$block_range[$index]:0; 
            $partialAllow= (isset($partial[$index]) && $partial[$index])?(int)$partial[$index]:0;
            if ($block > 0) {  
                /* round to complete block for iteration purpose. For negetive value, round to previous round and for positive value round to next round. Considering negetive cost as well for all x-* modules */
                if (!$partialAllow) {
                    if(is_float($end) && fmod($end,$block) != 0) {
                        $end = $cost < 0 ? ($end - fmod($end,$block)) : ($end - fmod($end,$block)) + $block;
                    }
                    else if($block >= 1 && ($end % $block) != 0) {
                       $end =  $cost < 0 ? ($end - ($end % $block)) : ($end - ($end % $block)) + $block; 
                    }
                }
                $no_of_blocks =0;
                if ($start == 0) {
                    $start = 1;
                }
                while( $start <= $end ) {
                    if ($partialAllow) {
                        $no_of_blocks =  ($end-$start) >= $block ? ($no_of_blocks+1) : ($no_of_blocks+($end-$start)/$block);
                    } else {
                        $no_of_blocks++;
                    }
                    $start += $block;
                }
                $cost = ($no_of_blocks * $cost);
            }
            $cumulative += $cost;
            if ($status) break; 
        }

        //if not found and additional price was set
        if (substr(trim($additional), -1) == '%') {
            $percent = rtrim(trim($additional),'%'); 
            $additional = (float)(($percent*$percent_to_be_considered)/100);
        } else {
            $additional = (float)$additional;  
        }
        if (!$status && $additional && $additional_limit >= $target_value) {
            if(!isset($end)) $end = 0;
            while( $end < $target_value ) {
                $cost += $additional;
                $cumulative += $additional;
                $end += $additional_per;
            }
            $status = true;
        }
        if ($final_price === 'cumulative') {
            $cost = $cumulative;
        }
        return $status;
    }

    private function calculate_string( $mathString ) {
    
        $mathString = trim($mathString);     // trim white spaces
        $mathString = preg_replace ('[^0-9\+-\*\/\(\) ]', '', $mathString);    // remove any non-numbers chars; exception for math operators

        if (!function_exists('create_function')) {
            $compute = create_function("", "return (" . html_entity_decode($mathString) . ");" );
        } else {
            eval (" \$compute = function() { return (" . html_entity_decode($mathString) . ");}; ");
        } 

       if (!isset($compute)) {
            $compute = function() {
               return 0;
            };
       }
       
       return 0 + $compute();
    }

    private function validateProductData($rule_field, $data_field, $resultant_data, $cart_data, $xshippingpro, &$applicable) {

             $status = true;

            if ($xshippingpro[$rule_field] == 1) return $status;

            if ($xshippingpro[$rule_field] == 2) {
                if (!$xshippingpro['ingore_product_rule']) {
                    if (count($resultant_data) != count($xshippingpro[$data_field])) {
                        $status = false; 
                    }
                }
                $applicable=$xshippingpro[$data_field];
            }

            if ($xshippingpro[$rule_field]==3) {
                if (!$xshippingpro['ingore_product_rule']) {
                    if (!$resultant_data) {
                        $status = false; 
                    }
                }
                $applicable=$xshippingpro[$data_field];
            }

            if ($xshippingpro[$rule_field]==4) {
                if (!$xshippingpro['ingore_product_rule']) {
                    if (count($resultant_data)!=count($xshippingpro[$data_field]) || count($resultant_data)!=count($cart_data)) {
                        $status = false; 
                    }
                }
                $applicable=$xshippingpro[$data_field];
            }

            if ($xshippingpro[$rule_field]==5) {
                if (!$xshippingpro['ingore_product_rule']) {
                    if ($resultant_data) {
                        $status = false; 
                    }
                }
                $applicable= array_diff($cart_data, $xshippingpro[$data_field]); 
            }

            if ($xshippingpro[$rule_field]==6) {
                if (!$xshippingpro['ingore_product_rule']) {
                    if (!$resultant_data || count($resultant_data)!=count($cart_data)) {
                        $status = false; 
                    }
                }
                $applicable=$xshippingpro[$data_field];
            }

            if ($xshippingpro[$rule_field]==7) {
                if (!$xshippingpro['ingore_product_rule']) {
                    if ($resultant_data && count($resultant_data)==count($cart_data)) {
                        $status = false; 
                    }
                }
                $applicable = array_diff($cart_data, $xshippingpro[$data_field]);
            }

            return $status;
    }


    private function evaluateATotal($module_name, $total, $_xtaxes) {
        
            $module_value = 0;
            $xtotals = array();
            $xtaxes = $_xtaxes;
            $xtotal = $total;

            // Because __call can not keep var references so we put them into an array. 
            $xtotal_data = array(
                'totals' => &$xtotals,
                'taxes'  => &$xtaxes,
                'total'  => &$xtotal
            );

            if ($this->config->get('total_'.$module_name.'_status')) {
                $this->load->model('extension/total/'.$module_name);
                $this->{'model_extension_total_'.$module_name}->getTotal($xtotal_data);
            }

            if (isset($xtotal_data['totals'][0]['code']) && $xtotal_data['totals'][0]['code'] == $module_name) {
                $module_value = $xtotal_data['totals'][0]['value'];
            }

            return $module_value;
    }

    private function getGrandTotal(&$grand_total, &$grand_shipping, $_xtaxes) {
                $this->load->model('setting/extension');
                $total_mods = $this->model_setting_extension->getExtensions('total');

                $xtotals = array();
                $xtaxes = $_xtaxes;
                $xtotal = 0;

                // Because __call can not keep var references so we put them into an array. 
                $xtotal_data = array(
                    'totals' => &$xtotals,
                    'taxes'  => &$xtaxes,
                    'total'  => &$xtotal
                );

                $sort_order = array();
                foreach ($total_mods as $key => $value) {
                    $sort_order[$key] = $this->config->get('total_'.$value['code'] . '_sort_order');
                }

                array_multisort($sort_order, SORT_ASC, $total_mods);
                $isTotalFound = false;
                foreach ($total_mods as $total_mod) {

                    if ($total_mod['code']=='shipping') {
                        $grand_shipping = $xtotal_data['total'];
                        continue;
                    } 

                    if ($this->config->get('total_'.$total_mod['code'] . '_status')) {
                        $this->load->model('extension/total/' . $total_mod['code']);

                        $this->{'model_extension_total_' . $total_mod['code']}->getTotal($xtotal_data);
                        if ($total_mod['code']=='total') {
                            $grand_total = $xtotal_data['total'];
                            $isTotalFound = true;
                            break;
                        }
                    }
                }

                if (!$grand_total && !$isTotalFound) $grand_total = $this->_xcart_total;
                return true;
    }

    private function getProductProfile(&$cart_products) {
            $this->load->model('catalog/product');

            $cart_categories=array();
            $cart_product_ids=array();
            $cart_manufacturers=array();
            $cart_options = array();
            $cart_locations = array();
            $cart_volume=0;
            $multi_category=false;

            foreach($cart_products as $inc=>$product) {
                $product_categories=$this->model_catalog_product->getCategories($product['product_id']);
                $cart_product_ids[]=$product['product_id']; 
                $cart_products[$inc]['categories']=array();
                if ($product_categories) {
                    if (count($product_categories)>1)$multi_category=true;
                    foreach($product_categories as $category) {
                        $cart_categories[]=$category['category_id'];  
                        $cart_products[$inc]['categories'][]=$category['category_id']; //store for future use 
                    } 
                }

                $length_class_id = $product['length_class_id'] ? $product['length_class_id'] : $this->config->get('config_length_class_id');
                $product['length'] = $this->length->convert($product['length'], $length_class_id, $this->config->get('config_length_class_id'));
                $product['width'] = $this->length->convert($product['width'], $length_class_id, $this->config->get('config_length_class_id'));
                $product['height'] = $this->length->convert($product['height'], $length_class_id, $this->config->get('config_length_class_id'));
                $cart_products[$inc]['length'] = $product['length'];
                $cart_products[$inc]['width'] = $product['width'];
                $cart_products[$inc]['height'] = $product['height'];

                
                $product_volume = (($product['width']*$product['height']*$product['length'])*$product['quantity']);
                $cart_volume+=$product_volume; 
                $cart_products[$inc]['volume']=$product_volume; //store for future use
                $cart_products[$inc]['dimensional']=0; // just initialize for now. Will calc later for method wise
                $weight_class_id = $product['weight_class_id'] ? $product['weight_class_id'] : $this->config->get('config_weight_class_id');
                $cart_products[$inc]['weight'] = $this->weight->convert($product['weight'], $weight_class_id, $this->config->get('config_weight_class_id'));

                $product_info=$this->model_catalog_product->getProduct($product['product_id']);
                if ($product_info) {
                    $cart_manufacturers[]=$product_info['manufacturer_id'];
                    $cart_products[$inc]['manufacturer_id']=$product_info['manufacturer_id']; //store for future use
                    $location = trim(strtolower($product_info['location']));
                    $cart_products[$inc]['location'] = $location; //store for future use
                    $cart_locations[] = $location;
                }
                
                $cart_products[$inc]['options']=array();
                if (isset($product['option']) && $product['option'] && is_array($product['option'])) {
                    foreach($product['option'] as $option) {
                        if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox') {
                            $cart_options[]=$option['option_value_id'];  
                            $cart_products[$inc]['options'][]=$option['option_value_id']; //store for future use 
                        }
                    }
                }
            } 

            $cart_categories=array_unique($cart_categories);
            $cart_product_ids=array_unique($cart_product_ids);
            $cart_manufacturers=array_unique($cart_manufacturers);
            $cart_options=array_unique($cart_options);
            $cart_locations = array_unique($cart_locations);

            return array(
                'cart_categories' => $cart_categories,
                'cart_product_ids' => $cart_product_ids,
                'cart_manufacturers' => $cart_manufacturers,
                'cart_options' => $cart_options,
                'cart_locations' => $cart_locations,
                'cart_volume' => $cart_volume,
                'multi_category' => $multi_category
            );

    }

    private function resetEmptyRule($xshippingpro) {
        $rules = [
            'geo_zone_id' => 'geo_zone_all',
            'city' => 'city_all',
            'country' => 'country_all',
            'store' => 'store_all',
            'currency' => 'currency_all',
            'customer_group' => 'customer_group_all',
            'payment' => 'payment_all',
            'postal' => 'postal_all',
            'coupon' => 'coupon_all',
            'product_category' => 'category',
            'product_product' => 'product',
            'product_option' => 'option',
            'manufacturer' => 'manufacturer_rule',
            'location' => 'location_rule'
        ];
        
        foreach ($rules as $key => $value) {
            if (!$xshippingpro[$key]) {
                $xshippingpro[$value] = 1;
            }
        }
        
        /* reset delimitter to comma */
        $fields = [
            'city',
            'coupon',
            'postal'
        ];
        foreach ($fields as $field) {
            if ($xshippingpro[$field]) {
                $xshippingpro[$field] = str_replace(PHP_EOL, ',', $xshippingpro[$field]);
            }
        }
        return $xshippingpro;
    }
}