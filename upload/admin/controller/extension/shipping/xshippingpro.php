<?php
define('XSHIPPINGPRO_VERSION', '2.9.0');
define('XSHIPPINGPRO_ID', '13705');
class ControllerExtensionShippingXshippingpro extends Controller {
    private $error = array();
    
    public function index() {   

        $this->load->language('extension/shipping/xshippingpro');

        $this->document->setTitle($this->language->get('heading_title'));
        
        $this->load->model('setting/setting');
        $this->load->model('extension/xshippingpro/xshippingpro');

         /* লাইসেন্স বেরিফিকেসন  */
        $_m_url = $this->url->link('extension/shipping/xshippingpro', 'user_token=' . $this->session->data['user_token'], true);
        if (isset($this->request->get['skipkey'])) {
            $this->session->data['server_unavilable'] = true;
            $this->response->redirect($_m_url);
        }
        
        if (isset($this->request->post['_xverify'])) {
            if (!$this->request->post['key']) {
                $this->session->data['warning'] = 'Please enter a valid order #'; 
            } else {
                $vr = $this->getPS($this->request->post['key'], XSHIPPINGPRO_ID);
                if ($vr['success']) {
                   $this->_wpd($this->request->post['key']);
                   $this->session->data['success'] = 'Thank you very much for verifying your purchase.'; 
                } else {
                  $this->session->data['warning'] = $vr['error'];
                }
            }
            $this->response->redirect($_m_url);
        }
        $rs = $this->_rpd();
        $data['_v'] = $rs ? '' : $this->_v($_m_url);
        /* লাইসেন্স শেষ */

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            if(isset($this->request->post['action']) && $this->request->post['action']=='import') {
                $this->import();
                $this->response->redirect($this->url->link('extension/shipping/xshippingpro', 'user_token=' . $this->session->data['user_token'], 'SSL'));
            }

            $this->session->data['success'] = $this->language->get('text_success'); 
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true));
        }
        
        $this->checkOCMOD();
        if (isset($this->request->get['ocmod'])) {
            $this->installOCMOD();
        }

        if($this->model_extension_xshippingpro_xshippingpro->isDBBUPdateAvail()){
            $this->model_extension_xshippingpro_xshippingpro->install();
        }

        $data['x_version'] = XSHIPPINGPRO_VERSION;
        $data['x_id'] = XSHIPPINGPRO_ID;

        $data['heading_title'] = $this->language->get('heading_title');

        $data['tab_rate'] = $this->language->get('tab_rate');
        $data['entry_name'] = $this->language->get('entry_name');
        $data['entry_order_total'] = $this->language->get('entry_order_total');
        $data['entry_order_weight'] = $this->language->get('entry_order_weight');
        $data['entry_quantity'] = $this->language->get('entry_quantity');
        $data['entry_to'] = $this->language->get('entry_to');
        $data['entry_order_hints'] = $this->language->get('entry_order_hints');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_none'] = $this->language->get('text_none');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['ignore_modifier'] = $this->language->get('ignore_modifier');
        $data['tip_weight'] = $this->language->get('tip_weight');
        $data['tip_total'] = $this->language->get('tip_total');
        $data['tip_quantity'] = $this->language->get('tip_quantity');
        
        $data['entry_cost'] = $this->language->get('entry_cost');
        $data['entry_tax'] = $this->language->get('entry_tax');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_customer_group'] = $this->language->get('entry_customer_group');
        $data['text_all'] = $this->language->get('text_all');
        $data['text_category'] = $this->language->get('text_category');
        $data['text_category_any'] = $this->language->get('text_category_any');
        $data['text_category_all'] = $this->language->get('text_category_all');
        $data['text_category_least'] = $this->language->get('text_category_least');
        $data['text_category_least_with_other'] = $this->language->get('text_category_least_with_other'); 
        $data['text_category_except_other'] = $this->language->get('text_category_except_other');
        
        $data['text_grand_total'] = $this->language->get('text_grand_total');
        $data['text_category_except'] = $this->language->get('text_category_except');
        $data['text_category_exact'] = $this->language->get('text_category_exact');
        $data['entry_category'] = $this->language->get('entry_category');
        $data['entry_weight_include'] = $this->language->get('entry_weight_include');
        $data['entry_desc'] = $this->language->get('entry_desc');
        $data['text_select_all'] = $this->language->get('text_select_all');
        $data['text_unselect_all'] = $this->language->get('text_unselect_all');
        $data['text_any'] = $this->language->get('text_any');
        $data['module_status'] = $this->language->get('module_status');
        $data['text_heading'] = $this->language->get('text_heading');
        $data['entry_store'] = $this->language->get('entry_store');
        $data['entry_manufacturer'] = $this->language->get('entry_manufacturer');
        $data['text_product'] = $this->language->get('text_product');
        $data['text_product_any'] = $this->language->get('text_product_any');
        $data['text_product_all'] = $this->language->get('text_product_all');
        $data['text_product_least'] = $this->language->get('text_product_least');
        $data['text_product_least_with_other'] = $this->language->get('text_product_least_with_other');
        $data['text_product_exact'] = $this->language->get('text_product_exact');
        $data['text_product_except'] = $this->language->get('text_product_except');
        $data['text_product_except_other'] = $this->language->get('text_product_except_other');
        $data['entry_product'] = $this->language->get('entry_product');
        $data['text_debug'] = $this->language->get('text_debug');

        $data['text_description'] = $this->language->get('text_description');
        $data['text_desc_estimate_popup'] = $this->language->get('text_desc_estimate_popup');
        $data['text_desc_delivery_method'] = $this->language->get('text_desc_delivery_method');
        $data['text_desc_confirmation'] = $this->language->get('text_desc_confirmation');
        $data['text_desc_site_order_detail'] = $this->language->get('text_desc_site_order_detail');
        $data['text_desc_admin_order_detail'] = $this->language->get('text_desc_admin_order_detail');
        $data['text_desc_order_email'] = $this->language->get('text_desc_order_email');
        $data['text_desc_order_invoice'] = $this->language->get('text_desc_order_invoice');
        
        $data['text_manufacturer_rule'] = $this->language->get('text_manufacturer_rule');
        $data['text_manufacturer_any'] = $this->language->get('text_manufacturer_any');
        $data['text_manufacturer_all'] = $this->language->get('text_manufacturer_all');
        $data['text_manufacturer_least'] = $this->language->get('text_manufacturer_least');
        $data['text_manufacturer_least_with_other'] = $this->language->get('text_manufacturer_least_with_other');
        $data['text_manufacturer_exact'] = $this->language->get('text_manufacturer_exact');
        $data['text_manufacturer_except'] = $this->language->get('text_manufacturer_except');
        $data['text_manufacturer_except_other'] = $this->language->get('text_manufacturer_except_other');
        $data['tip_manufacturer_rule'] = $this->language->get('tip_manufacturer_rule');
        
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_save_continue'] = $this->language->get('button_save_continue');
        $data['tab_general'] = $this->language->get('tab_general');
        $data['text_method_remove'] = $this->language->get('text_method_remove');
        $data['text_method_copy'] = $this->language->get('text_method_copy');

        $data['text_group_shipping_mode'] = $this->language->get('text_group_shipping_mode');
        $data['text_no_grouping'] = $this->language->get('text_no_grouping');
        $data['text_lowest'] = $this->language->get('text_lowest');
        $data['text_highest'] = $this->language->get('text_highest');
        $data['text_average'] = $this->language->get('text_average');
        $data['text_sum'] = $this->language->get('text_sum');
        $data['text_and'] = $this->language->get('text_and');
        $data['text_add_new_method'] = $this->language->get('text_add_new_method');
        $data['text_remove'] = $this->language->get('text_remove');
        $data['text_general'] = $this->language->get('text_general');
        $data['text_criteria_setting'] = $this->language->get('text_criteria_setting');
        $data['text_category_product'] = $this->language->get('text_category_product');
        $data['text_price_setting'] = $this->language->get('text_price_setting');
        $data['text_others'] = $this->language->get('text_others');
        $data['text_zip_postal'] = $this->language->get('text_zip_postal');
        $data['text_enter_zip'] = $this->language->get('text_enter_zip');
        $data['text_zip_rule'] = $this->language->get('text_zip_rule');
        $data['text_zip_rule_inclusive'] = $this->language->get('text_zip_rule_inclusive');
        $data['text_zip_rule_exclusive'] = $this->language->get('text_zip_rule_exclusive');
        $data['text_coupon'] = $this->language->get('text_coupon');
        $data['text_enter_coupon'] = $this->language->get('text_enter_coupon');
        $data['text_coupon_rule'] = $this->language->get('text_coupon_rule');
        $data['text_coupon_rule_inclusive'] = $this->language->get('text_coupon_rule_inclusive');
        $data['text_coupon_rule_exclusive'] = $this->language->get('text_coupon_rule_exclusive');
        $data['text_rate_type'] = $this->language->get('text_rate_type');
        $data['text_rate_flat'] = $this->language->get('text_rate_flat');
        $data['text_rate_quantity'] = $this->language->get('text_rate_quantity');
        $data['text_rate_weight'] = $this->language->get('text_rate_weight');
        $data['text_rate_volume'] = $this->language->get('text_rate_volume'); 
        $data['text_rate_total_coupon'] = $this->language->get('text_rate_total_coupon');
        $data['text_rate_total'] = $this->language->get('text_rate_total');
        $data['text_rate_sub_total'] = $this->language->get('text_rate_sub_total');
        $data['text_unit_range'] = $this->language->get('text_unit_range');
        $data['text_delete_all'] = $this->language->get('text_delete_all');
        $data['text_csv_import'] = $this->language->get('text_csv_import');
        $data['text_start'] = $this->language->get('text_start');
        $data['text_end'] = $this->language->get('text_end');
        $data['text_cost'] = $this->language->get('text_cost');
        $data['text_qnty_block'] = $this->language->get('text_qnty_block');
        $data['text_add_new'] = $this->language->get('text_add_new');
        $data['text_final_cost'] = $this->language->get('text_final_cost');
        $data['text_final_single'] = $this->language->get('text_final_single');
        $data['text_final_cumulative'] = $this->language->get('text_final_cumulative');
        $data['text_percentage_related'] = $this->language->get('text_percentage_related');
        $data['text_percent_sub_total'] = $this->language->get('text_percent_sub_total');
        $data['text_percent_total'] = $this->language->get('text_percent_total');
        $data['text_price_adjustment'] = $this->language->get('text_price_adjustment');
        $data['text_price_min'] = $this->language->get('text_price_min');
        $data['text_price_max'] = $this->language->get('text_price_max');
        $data['text_price_add'] = $this->language->get('text_price_add');
        $data['text_days_week'] = $this->language->get('text_days_week');
        $data['text_time_period'] = $this->language->get('text_time_period');
        $data['text_sunday'] = $this->language->get('text_sunday');
        $data['text_monday'] = $this->language->get('text_monday');
        $data['text_tuesday'] = $this->language->get('text_tuesday');
        $data['text_wednesday'] = $this->language->get('text_wednesday');
        $data['text_thursday'] = $this->language->get('text_thursday');
        $data['text_friday'] = $this->language->get('text_friday');
        $data['text_saturday'] = $this->language->get('text_saturday');
        $data['text_country'] = $this->language->get('text_country');
        
        $data['tip_weight_include'] = $this->language->get('tip_weight_include');
        $data['tip_sorting_own'] = $this->language->get('tip_sorting_own');
        $data['tip_status_own'] = $this->language->get('tip_status_own');
        $data['tip_store'] = $this->language->get('tip_store');
        $data['tip_geo'] = $this->language->get('tip_geo');
        $data['tip_manufacturer'] = $this->language->get('tip_manufacturer');
        $data['tip_customer_group'] = $this->language->get('tip_customer_group');
        $data['tip_zip'] = $this->language->get('tip_zip');
        $data['tip_coupon'] = $this->language->get('tip_coupon');
        $data['tip_category'] = $this->language->get('tip_category');
        $data['tip_product'] = $this->language->get('tip_product');
        $data['tip_rate_type'] = $this->language->get('tip_rate_type');
        $data['tip_cost'] = $this->language->get('tip_cost');
        $data['tip_unit_start'] = $this->language->get('tip_unit_start');
        $data['tip_unit_end'] = $this->language->get('tip_unit_end');
        $data['tip_unit_price'] = $this->language->get('tip_unit_price');
        $data['tip_unit_ppu'] = $this->language->get('tip_unit_ppu');
        $data['tip_single_commulative'] = $this->language->get('tip_single_commulative');
        $data['tip_percentage'] = $this->language->get('tip_percentage');
        $data['tip_price_adjust'] = $this->language->get('tip_price_adjust');
        $data['tip_day'] = $this->language->get('tip_day');
        $data['tip_time'] = sprintf($this->language->get('tip_time'), date('h:i:s A'));
        $data['tip_heading'] = $this->language->get('tip_heading');
        $data['tip_status_global'] = $this->language->get('tip_status_global');
        $data['tip_sorting_global'] = $this->language->get('tip_sorting_global');
        $data['tip_grouping'] = $this->language->get('tip_grouping');
        $data['tip_debug'] = $this->language->get('tip_debug');
        $data['tip_desc'] = $this->language->get('tip_desc');
        $data['tip_import'] = $this->language->get('tip_import');
        $data['tip_postal_code'] = $this->language->get('tip_postal_code');
        //$data['tip_multi_category'] = $this->language->get('tip_multi_category');
        //$data['text_multi_category'] = $this->language->get('text_multi_category');
        $data['entry_all'] = $this->language->get('entry_all');
        $data['entry_any'] = $this->language->get('entry_any');
        $data['tip_group_limit'] = $this->language->get('tip_group_limit');
        $data['text_group_limit'] = $this->language->get('text_group_limit');
        $data['no_unit_row'] = $this->language->get('no_unit_row');
        
        $data['text_partial'] = $this->language->get('text_partial');
        $data['tip_partial'] = $this->language->get('tip_partial');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_additional'] = $this->language->get('text_additional');
        $data['tip_additional'] = $this->language->get('tip_additional');
        $data['text_additional_till'] = $this->language->get('text_additional_till');

        $data['text_dimensional_weight'] = $this->language->get('text_dimensional_weight');
        $data['text_dimensional_factor'] = $this->language->get('text_dimensional_factor');
        $data['text_dimensional_overrule'] = $this->language->get('text_dimensional_overrule'); 
        $data['text_logo'] = $this->language->get('text_logo');
        $data['tip_text_logo'] = $this->language->get('tip_text_logo');    
        
        $data['text_sort_manual'] = $this->language->get('text_sort_manual'); 
        $data['text_sort_type'] = $this->language->get('text_sort_type'); 
        $data['text_sort_price_asc'] = $this->language->get('text_sort_price_asc'); 
        $data['text_sort_price_desc'] = $this->language->get('text_sort_price_desc'); 
        $data['tip_text_sort_type'] = $this->language->get('tip_text_sort_type'); 
        $data['tab_general_global'] = $this->language->get('tab_general_global');  
        $data['tab_general_general'] = $this->language->get('tab_general_general'); 
        $data['text_sort_name_asc'] = $this->language->get('text_sort_name_asc');
        $data['text_sort_name_desc'] = $this->language->get('text_sort_name_desc');

        $data['text_export'] = $this->language->get('text_export');   
        $data['tip_export'] = $this->language->get('tip_export');   
        $data['text_import'] = $this->language->get('text_import');   
        $data['tip_import'] = $this->language->get('tip_import');  
        $data['tab_import_export'] = $this->language->get('tab_import_export');    
        $data['error_import'] = $this->language->get('error_import'); 
        $data['text_mask_price'] = $this->language->get('text_mask_price');     

        $data['text_percent_shipping'] = $this->language->get('text_percent_shipping'); 
        $data['text_percent_sub_total_shipping'] = $this->language->get('text_percent_sub_total_shipping'); 
        $data['text_percent_total_shipping'] = $this->language->get('text_percent_total_shipping'); 
        $data['tip_group_name'] = $this->language->get('tip_group_name');
        $data['entry_group_name'] = $this->language->get('entry_group_name'); 

        $data['text_equation'] = $this->language->get('text_equation');
        $data['text_eq_placeholder'] = $this->language->get('text_eq_placeholder');
        $data['tip_equation'] = $this->language->get('tip_equation'); 
        $data['text_equation_help'] = $this->language->get('text_equation_help'); 
        $data['text_admin_name'] = $this->language->get('text_admin_name');
        $data['text_admin_name_tip'] = $this->language->get('text_admin_name_tip'); 
        $data['text_name_tip'] = $this->language->get('text_name_tip');  
        $data['text_hide'] = $this->language->get('text_hide');  
        $data['text_hide_inactive'] = $this->language->get('text_hide_inactive'); 
        $data['text_location_rule'] = $this->language->get('text_location_rule'); 
        $data['text_location_any'] = $this->language->get('text_location_any'); 
        $data['text_location_all'] = $this->language->get('text_location_all'); 
        $data['text_location_least'] = $this->language->get('text_location_least'); 
        $data['text_location_least_with_other'] = $this->language->get('text_location_least_with_other');
        $data['text_location_exact'] = $this->language->get('text_location_exact'); 
        $data['text_location_except'] = $this->language->get('text_location_except'); 
        $data['text_location_except_other'] = $this->language->get('text_location_except_other'); 
        $data['entry_location'] = $this->language->get('entry_location'); 
        $data['check_all'] = $this->language->get('check_all'); 
        $data['uncheck_all'] = $this->language->get('uncheck_all'); 
        $data['select_multiple'] = $this->language->get('select_multiple'); 
        $data['grand_total_before_shiping'] = $this->language->get('grand_total_before_shiping'); 
        $data['text_hide_placeholder'] = $this->language->get('text_hide_placeholder');
        $data['text_no_of_location'] = $this->language->get('text_no_of_location');  
        $data['text_mask_title'] = $this->language->get('text_mask_title'); 

        $data['text_method_specific'] = $this->language->get('text_method_specific'); 
        $data['text_cat_product_ignore'] = $this->language->get('text_cat_product_ignore'); 
        $data['text_cat_product_ignore_tip'] = $this->language->get('text_cat_product_ignore_tip'); 
        $data['text_batch_select'] = $this->language->get('text_batch_select'); 
        $data['text_search'] = $this->language->get('text_search'); 
        $data['text_name'] = $this->language->get('text_name'); 
        $data['text_selection_mode'] = $this->language->get('text_selection_mode'); 
        $data['text_selection_mode_exact'] = $this->language->get('text_selection_mode_exact'); 
        $data['text_selection_mode_exact_sub'] = $this->language->get('text_selection_mode_exact_sub'); 
        $data['text_selection_mode_except'] = $this->language->get('text_selection_mode_except'); 
        $data['text_selection_select'] = $this->language->get('text_selection_select'); 

        $data['text_option'] = $this->language->get('text_option');
        $data['tip_option'] = $this->language->get('tip_option');
        $data['text_option_any'] = $this->language->get('text_option_any');
        $data['text_option_all'] = $this->language->get('text_option_all');
        $data['text_option_least'] = $this->language->get('text_option_least');
        $data['text_option_least_with_other'] = $this->language->get('text_option_least_with_other');
        $data['text_option_exact'] = $this->language->get('text_option_exact');
        $data['text_option_except'] = $this->language->get('text_option_except');
        $data['text_option_except_other'] = $this->language->get('text_option_except_other');
        $data['entry_option'] = $this->language->get('entry_option');  
        $data['entry_payment'] = $this->language->get('entry_payment');
        $data['tip_payment'] = $this->language->get('tip_payment');
        $data['text_geo_address'] = $this->language->get('text_geo_address');   
        $data['text_delivery'] = $this->language->get('text_delivery');  
        $data['text_payment'] = $this->language->get('text_payment');  

        $data['text_city_rule'] = $this->language->get('text_city_rule'); 
        $data['tip_city'] = $this->language->get('tip_city');  
        $data['text_city'] = $this->language->get('text_city');  
        $data['text_city_enter_tip'] = $this->language->get('text_city_enter_tip'); 
        $data['text_city_enter'] = $this->language->get('text_city_enter'); 
        $data['text_city_rule_inclusive'] = $this->language->get('text_city_rule_inclusive');
        $data['text_city_rule_exclusive'] = $this->language->get('text_city_rule_exclusive');
        $data['text_coupon_tip'] = $this->language->get('text_coupon_tip');
        $data['text_country_tip'] = $this->language->get('text_country_tip');
        $data['text_no_of_category'] = $this->language->get('text_no_of_category');
        $data['text_no_of_manufacturers'] = $this->language->get('text_no_of_manufacturers');
        $data['text_help'] = $this->language->get('text_help');
        $data['text_documentation'] = $this->language->get('text_documentation');
        $data['text_update'] = $this->language->get('text_update');
        $data['text_volumetric_weight'] = $this->language->get('text_volumetric_weight');
        $data['text_weight_eq'] = $this->language->get('text_weight_eq');
        $data['text_or_mode'] = $this->language->get('text_or_mode');
        $data['text_or_mode_tip'] = $this->language->get('text_or_mode_tip');
        $data['text_currency'] = $this->language->get('text_currency');
        $data['text_currency_tip'] = $this->language->get('text_currency_tip');
        $data['text_cart_value'] = $this->language->get('text_cart_value');
        $data['tip_cart_value'] = $this->language->get('tip_cart_value');

        $data['text_date_range'] = $this->language->get('text_date_range');
        $data['entry_date_end'] = $this->language->get('entry_date_end');
        $data['entry_date_start'] = $this->language->get('entry_date_start');
        $data['text_date_tip'] = sprintf($this->language->get('text_date_tip'), date('Y-m-d'));
        $data['rate_individual_quantity'] = $this->language->get('rate_individual_quantity');
        $data['rate_individual_weight'] = $this->language->get('rate_individual_weight');
        $data['rate_individual_volume'] = $this->language->get('rate_individual_volume');
        $data['cat_filter_no_data'] = $this->language->get('cat_filter_no_data');
        $data['cat_selection'] = $this->language->get('cat_selection');
        $data['cat_inc_sub'] = $this->language->get('cat_inc_sub');
        $data['text_chooose_selected'] = $this->language->get('text_chooose_selected');
        $data['cat_search'] = $this->language->get('cat_search');
        $data['text_remove_all'] = $this->language->get('text_remove_all');
        $data['text_placeholders'] = $this->language->get('text_placeholders');
        $data['text_debug_hint'] = $this->language->get('text_debug_hint');
        $data['text_debug_button'] = $this->language->get('text_debug_button');
        

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['warning'])) {
            $data['error_warning'] = $this->session->data['warning'];
            unset($this->session->data['warning']);
        } 
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        array_walk($data, function(&$value, $key){
            if(strpos($key,'tip') >= 0) {
                $value = str_replace(array("\r", "\n"), '', $value);
            }
        }, $data);

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true)
            );
        $data['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('extension/shipping/xshippingpro', 'user_token=' . $this->session->data['user_token'], true)
            );
        
        $data['action'] = $this->url->link('extension/shipping/xshippingpro', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=shipping', true);
        $data['export'] = $this->url->link('extension/shipping/xshippingpro/export', 'user_token=' . $this->session->data['user_token'], true);
        
        $data['user_token']=$this->session->data['user_token'];
        $data['method_data'] = $this->model_extension_xshippingpro_xshippingpro->getData();

        if (isset($this->request->post['shipping_xshippingpro_status'])) {
            $data['shipping_xshippingpro_status'] = $this->request->post['shipping_xshippingpro_status'];
        } else {
            $data['shipping_xshippingpro_status'] = $this->config->get('shipping_xshippingpro_status');
        }
        
        if (isset($this->request->post['shipping_xshippingpro_sort_order'])) {
            $data['shipping_xshippingpro_sort_order'] = $this->request->post['shipping_xshippingpro_sort_order'];
        } else {
            $data['shipping_xshippingpro_sort_order'] = $this->config->get('shipping_xshippingpro_sort_order');
        }

        if (isset($this->request->post['shipping_xshippingpro_group'])) {
            $data['shipping_xshippingpro_group'] = $this->request->post['shipping_xshippingpro_group'];
        } else {
            $data['shipping_xshippingpro_group'] = $this->config->get('shipping_xshippingpro_group');
        }

        if (isset($this->request->post['shipping_xshippingpro_group_limit'])) {
            $data['shipping_xshippingpro_group_limit'] = $this->request->post['shipping_xshippingpro_group_limit'];
        } else {
            $data['shipping_xshippingpro_group_limit'] = $this->config->get('shipping_xshippingpro_group_limit');
        }

        if (isset($this->request->post['shipping_xshippingpro_sorting'])) {
            $data['shipping_xshippingpro_sorting'] = $this->request->post['shipping_xshippingpro_sorting'];
        } else {
            $data['shipping_xshippingpro_sorting'] = $this->config->get('shipping_xshippingpro_sorting');
        }

        if (isset($this->request->post['shipping_xshippingpro_heading'])) {
            $data['shipping_xshippingpro_heading'] = $this->request->post['shipping_xshippingpro_heading'];
        } else {
            $data['shipping_xshippingpro_heading'] = $this->config->get('shipping_xshippingpro_heading');
        }


        if (isset($this->request->post['shipping_xshippingpro_desc_mail'])) {
            $data['shipping_xshippingpro_desc_mail'] = isset($this->request->post['shipping_xshippingpro_desc_mail'])?1:0;
        } else {
            $data['shipping_xshippingpro_desc_mail'] = $this->config->get('shipping_xshippingpro_desc_mail');
        } 


        if (isset($this->request->post['shipping_xshippingpro_debug'])) {
            $data['shipping_xshippingpro_debug'] = $this->request->post['shipping_xshippingpro_debug'];
        } else {
            $data['shipping_xshippingpro_debug'] = $this->config->get('shipping_xshippingpro_debug');
        }

        if (isset($this->request->post['shipping_xshippingpro_sub_group'])) {
            $data['shipping_xshippingpro_sub_group'] = $this->request->post['shipping_xshippingpro_sub_group'];
        } else {
            $data['shipping_xshippingpro_sub_group'] = $this->config->get('shipping_xshippingpro_sub_group');
        }

        if (isset($this->request->post['shipping_xshippingpro_sub_group_limit'])) {
            $data['shipping_xshippingpro_sub_group_limit'] = $this->request->post['shipping_xshippingpro_sub_group_limit'];
        } else {
            $data['shipping_xshippingpro_sub_group_limit'] = $this->config->get('shipping_xshippingpro_sub_group_limit');
        }

        if (isset($this->request->post['shipping_xshippingpro_sub_group_name'])) {
            $data['shipping_xshippingpro_sub_group_name'] = $this->request->post['shipping_xshippingpro_sub_group_name'];
        } else {
            $data['shipping_xshippingpro_sub_group_name'] = $this->config->get('shipping_xshippingpro_sub_group_name');
        }


        $this->load->model('localisation/tax_class');
        $data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();
        
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        $this->load->model('setting/store');
        $data['stores'] = $this->model_setting_store->getStores();
        $data['stores']=  array_merge(array(array('store_id'=>0,'name'=>$this->language->get('store_default'))),$data['stores']);

        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();
        $data['language_id']=$this->config->get('config_language_id');

        $this->load->model('localisation/currency');
        $data['currencies'] = $this->model_localisation_currency->getCurrencies();


        if(intval(str_replace('.','',VERSION)) >=  2101) {
            $this->load->model('customer/customer_group');
            $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();
        } else {
            $this->load->model('sale/customer_group');
            $data['customer_groups'] = $this->model_sale_customer_group->getCustomerGroups();
        }
        $data['customer_groups'][] = array('customer_group_id' => 0, 'name' => $this->language->get('text_guest_checkout'));
        
        /* Payment rule*/
        $xpayment_methods = array();
        $payment_mods=array();
        $xpayment_installed=false;
        $result=$this->db->query("select * from " . DB_PREFIX . "extension where type='payment'");
        if($result->rows){
            foreach($result->rows as $row){
                $payment_mods[$row['code']]=$this->getModuleName($row['code'],$row['type']);  
                if($row['code']=='xpayment') $xpayment_installed=true;
            }
        }
        
        $data['payment_mods'] = $payment_mods;

        /* For X-Payment */
        if($xpayment_installed) {

            $this->load->model('extension/payment/xpayment');
            $xpayment= $this->model_extension_payment_xpayment->getData();
            foreach($xpayment as $single_method) {
                $no_of_tab = $single_method['tab_id'];
                $method_data = $single_method['method_data'];
                $method_data = @unserialize(@base64_decode($method_data));
                if(!is_array($method_data)) $method_data = array();

                if(!isset($method_data['name']))$method_data['name']=array();
                if(!is_array($method_data['name']))$method_data['name']=array();
                $method_name = (!isset($method_data['name'][$data['language_id']]) || !$method_data['name'][$data['language_id']]) ? 'Untitled Method '.$no_of_tab : $method_data['name'][$data['language_id']]; 
                $code = 'xpayment'.$no_of_tab;
                $xpayment_methods[$code]=$method_name;
            }
        }

        $data['xpayment'] = $xpayment_methods;

        /*End of X-Payment*/

        $data['language_dir'] = 'view/image/flags/';

        if(intval(str_replace('.','',VERSION)) >= 2200) { 
            $data['language_dir'] = 'language/';

            foreach($data['languages'] as $inc=>$language) {
                $data['languages'][$inc]['image'] = $language['code'].'/'.$language['code'].'.png'; 
            }
        }

        $this->load->model('localisation/country');
        $data['countries'] = $this->model_localisation_country->getCountries();

        $data['group_options']=array('no_group'=>$this->language->get('text_no_grouping'),'lowest'=>$this->language->get('text_lowest'),'highest'=>$this->language->get('text_highest'),'average'=>$this->language->get('text_average'),'sum'=>$this->language->get('text_sum'),'and'=>$this->language->get('text_and'));
        
        $data['sort_options']=array(
            '1'=>$this->language->get('text_sort_manual'),
            '2'=>$this->language->get('text_sort_price_asc'),
            '3'=>$this->language->get('text_sort_price_desc'),
            '4'=>$this->language->get('text_sort_name_asc'),
            '5'=>$this->language->get('text_sort_name_desc')
        );       

        $data['text_group_none']=$this->language->get('text_group_none');
        $data['entry_group']=$this->language->get('entry_group'); 
        $data['entry_group_tip']=$this->language->get('entry_group_tip');  
        $data['text_group_name']=$this->language->get('text_group_name'); 
        $data['text_group_type']=$this->language->get('text_group_type');   
        $data['text_method_group']=$this->language->get('text_method_group'); 
        $data['tip_method_group']= $this->language->get('tip_method_group');

        /* default values of global setting*/
        if (!$data['shipping_xshippingpro_group']) $data['shipping_xshippingpro_group'] = 'no_group';
        if (!$data['shipping_xshippingpro_heading']) {
            foreach ($data['languages'] as $key => $value) {
                $data['shipping_xshippingpro_heading'][$value['language_id']] = 'Shipping Options';
            }
        }

        $eq_placeholders = array(
            '{cartTotal}' => $this->language->get('text_eq_cart_total'),
            '{cartWeight}' => $this->language->get('text_eq_cart_weight'),
            '{cartQnty}' => $this->language->get('text_eq_cart_qnty'),
            '{cartVolume}' => $this->language->get('text_eq_cart_vol'),
            '{cartTotalAsPerProductRule}' => $this->language->get('text_eq_method_total'),
            '{cartWeightAsPerProductRule}' => $this->language->get('text_eq_method_weight'),
            '{cartQntyAsPerProductRule}' => $this->language->get('text_eq_method_qnty'),
            '{cartVolumeAsPerProductRule}' => $this->language->get('text_eq_method_vol'),
            '{shipping}' => $this->language->get('text_eq_shipping'),
            '{modifier}' => $this->language->get('text_eq_modifier'),
            '{noOfManufacturer}' => $this->language->get('text_eq_no_man'),
            '{noOfLocation}' => $this->language->get('text_eq_no_loc'),
            '{couponValue}' => $this->language->get('text_eq_coupon')
        );

        $anyEqPlaceholders = array(
            '{anyProductPrice}' => $this->language->get('text_eq_any_price'),
            '{anyProductWeight}' => $this->language->get('text_eq_any_weight'),
            '{anyProductQuantity}' => $this->language->get('text_eq_any_quantity'),
            '{anyProductVolume}' => $this->language->get('text_eq_any_vol'),
            '{anyProductWidth}' => $this->language->get('text_eq_any_width'),
            '{anyProductHeight}' => $this->language->get('text_eq_any_height'),
            '{anyProductLength}' => $this->language->get('text_eq_any_length')
        );

        $text_eq_any_help = $this->language->get('text_eq_any_help');
        $data['eq_placeholders'] = $this->getEquationPlaceholders($eq_placeholders, $anyEqPlaceholders, $text_eq_any_help);

        $data['shipping_xshippingpro_sub_groups_count']=10;
        $data['tpl']= $this->getFormData($data, true);
        $data['sub_groups'] = $this->getSubGroups($data);
        
        $data['methods']=$this->getMethodList($data);
        $data['form_data']=$this->getFormData($data);

        /* To maintain design compatiblity, add some css to OC >= 3.1.x */
        $oc_3_1_style = '
            h3.panel-title {
                font-size: 15px;
                font-weight: normal;
                display: inline-block;
                margin: 0;
                padding: 0;
            }
        ';
        if (version_compare(VERSION, '3.0.2.0') > 0) {
            $data['additional_css'] = $oc_3_1_style;
            $data['css_display'] = 'flex';
            $data['dp_option'] = 'locale';
        } else {
            $data['additional_css'] = ''; 
            $data['css_display'] = 'block';
            $data['dp_option'] = '';
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        
        $this->response->setOutput($this->load->view('extension/shipping/xshippingpro', $data));
    }

    public function quick_save() {

        $this->load->language('extension/shipping/xshippingpro');
        $this->load->model('extension/xshippingpro/xshippingpro');
        $json=array();
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            
            $save=array();
            if(isset($this->request->post['xshippingpro']) && isset($this->request->get['tab_id'])) {
                $save['method_data']=base64_encode(serialize($this->request->post['xshippingpro']));
                $save['tab_id'] = $this->request->get['tab_id'];
                $save['sort_order'] = (int)$this->request->get['sort_order'];
                $this->model_extension_xshippingpro_xshippingpro->addData($save);
                $json['success']=1;
            }
            else {
                $json['error']='error! - unable to save';
            }

        } else{

            $json['error']=$this->language->get('error_permission');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json)); 
    } 

    public function save_general(){

        $this->load->language('extension/shipping/xshippingpro');
        $this->load->model('setting/setting');
        $json=array();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            
            $save=array();
            $save['shipping_xshippingpro_status']=$this->request->post['shipping_xshippingpro_status'];
            $save['shipping_xshippingpro_group']=$this->request->post['shipping_xshippingpro_group'];
            $save['shipping_xshippingpro_group_limit']=$this->request->post['shipping_xshippingpro_group_limit'];
            $save['shipping_xshippingpro_heading']=$this->request->post['shipping_xshippingpro_heading'];
            $save['shipping_xshippingpro_sort_order']=$this->request->post['shipping_xshippingpro_sort_order']; 
            $save['shipping_xshippingpro_desc_mail']=isset($this->request->post['shipping_xshippingpro_desc_mail'])?1:0;
            $save['shipping_xshippingpro_debug']=$this->request->post['shipping_xshippingpro_debug'];
            $save['shipping_xshippingpro_sorting']=$this->request->post['shipping_xshippingpro_sorting'];
            $save['shipping_xshippingpro_sub_group']=$this->request->post['shipping_xshippingpro_sub_group'];
            $save['shipping_xshippingpro_sub_group_limit']=$this->request->post['shipping_xshippingpro_sub_group_limit'];
            $save['shipping_xshippingpro_sub_group_name']=$this->request->post['shipping_xshippingpro_sub_group_name'];

            if(isset($this->request->post['shipping_xshippingpro_status'])) {
                $this->model_setting_setting->editSetting('shipping_xshippingpro', $save);
                $json['success']=1;
            }
            else {
                $json['error']='error! - unable to save';
            }

        } else{
            $json['error']=$this->language->get('error_permission');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json)); 

    }

    public function delete() {

        $this->load->language('extension/shipping/xshippingpro');
        $this->load->model('extension/xshippingpro/xshippingpro');
        $json=array();

        if (($this->request->server['REQUEST_METHOD'] == 'GET') && $this->validate()) {

            if($this->request->get['tab_id']) {
                $this->model_extension_xshippingpro_xshippingpro->deleteData($this->request->get['tab_id']);
                $json['success']=1;
            }
            else {
                $json['error']='error! - unable to delete';
            }

        } else{
            $json['error']=$this->language->get('error_permission');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json)); 
    } 

    public function export(){

        $export = array();

        if(isset($this->request->get['no'])) {
            $this->exportMethod($this->request->get['no']);
        }

        $this->load->model('extension/xshippingpro/xshippingpro');

        $export['method_data'] = $this->model_extension_xshippingpro_xshippingpro->getData();

        if (isset($this->request->post['shipping_xshippingpro_status'])) {
            $export['shipping_xshippingpro_status'] = $this->request->post['shipping_xshippingpro_status'];
        } else {
            $export['shipping_xshippingpro_status'] = $this->config->get('shipping_xshippingpro_status');
        }
        
        if (isset($this->request->post['shipping_xshippingpro_sort_order'])) {
            $export['shipping_xshippingpro_sort_order'] = $this->request->post['shipping_xshippingpro_sort_order'];
        } else {
            $export['shipping_xshippingpro_sort_order'] = $this->config->get('shipping_xshippingpro_sort_order');
        }

        if (isset($this->request->post['shipping_xshippingpro_group'])) {
            $export['shipping_xshippingpro_group'] = $this->request->post['shipping_xshippingpro_group'];
        } else {
            $export['shipping_xshippingpro_group'] = $this->config->get('shipping_xshippingpro_group');
        }

        if (isset($this->request->post['shipping_xshippingpro_group_limit'])) {
            $export['shipping_xshippingpro_group_limit'] = $this->request->post['shipping_xshippingpro_group_limit'];
        } else {
            $export['shipping_xshippingpro_group_limit'] = $this->config->get('shipping_xshippingpro_group_limit');
        }

        if (isset($this->request->post['shipping_xshippingpro_sorting'])) {
            $export['shipping_xshippingpro_sorting'] = $this->request->post['shipping_xshippingpro_sorting'];
        } else {
            $export['shipping_xshippingpro_sorting'] = $this->config->get('shipping_xshippingpro_sorting');
        }

        if (isset($this->request->post['shipping_xshippingpro_heading'])) {
            $export['shipping_xshippingpro_heading'] = $this->request->post['shipping_xshippingpro_heading'];
        } else {
            $export['shipping_xshippingpro_heading'] = $this->config->get('shipping_xshippingpro_heading');
        }


        if (isset($this->request->post['shipping_xshippingpro_desc_mail'])) {
            $export['shipping_xshippingpro_desc_mail'] = isset($this->request->post['shipping_xshippingpro_desc_mail'])?1:0;
        } else {
            $export['shipping_xshippingpro_desc_mail'] = $this->config->get('shipping_xshippingpro_desc_mail');
        } 


        if (isset($this->request->post['shipping_xshippingpro_debug'])) {
            $export['shipping_xshippingpro_debug'] = $this->request->post['shipping_xshippingpro_debug'];
        } else {
            $export['shipping_xshippingpro_debug'] = $this->config->get('shipping_xshippingpro_debug');
        }

        if (isset($this->request->post['shipping_xshippingpro_sub_group'])) {
            $export['shipping_xshippingpro_sub_group'] = $this->request->post['shipping_xshippingpro_sub_group'];
        } else {
            $export['shipping_xshippingpro_sub_group'] = $this->config->get('shipping_xshippingpro_sub_group');
        }

        if (isset($this->request->post['shipping_xshippingpro_sub_group_limit'])) {
            $export['shipping_xshippingpro_sub_group_limit'] = $this->request->post['shipping_xshippingpro_sub_group_limit'];
        } else {
            $export['shipping_xshippingpro_sub_group_limit'] = $this->config->get('shipping_xshippingpro_sub_group_limit');
        }

        if (isset($this->request->post['shipping_xshippingpro_sub_group_name'])) {
            $export['shipping_xshippingpro_sub_group_name'] = $this->request->post['shipping_xshippingpro_sub_group_name'];
        } else {
            $export['shipping_xshippingpro_sub_group_name'] = $this->config->get('shipping_xshippingpro_sub_group_name');
        }

        $out = base64_encode(serialize($export));  
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Length: " . strlen($out));
        header("Content-type: text/txt");
        header("Content-Disposition: attachment; filename=xshippingpro.txt");
        echo $out;
        exit;
    } 

    public function exportMethod($tab_id) {

        $this->load->model('extension/xshippingpro/xshippingpro');

        $method_row = $this->model_extension_xshippingpro_xshippingpro->getDataByTabId($tab_id);
        
        if(!$method_row) return false;
        
        $method_data = $method_row['method_data'];
        $method_data = @unserialize(@base64_decode($method_data));
        if(!is_array($method_data)) $method_data = array();


        $csv_terminated = "\n";
        $csv_separator = ",";
        $csv_enclosed = '"';
        $csv_escaped = "\\";
        $out="";
        
        $heading = array('Start','End','Cost','Per Unit Block','Allow Partial');
        foreach($heading as $head) {        
            $out .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed,            
                stripslashes($head)) . $csv_enclosed;           
            $out .= $csv_separator;

        }
        
        $out= rtrim($out,$csv_separator);       
        $out .= $csv_terminated;

        $language_id = $this->config->get('config_language_id');
        $method_name = (!isset($method_data['name'][$language_id]) || !$method_data['name'][$language_id]) ? 'Untitled Method '.$no_of_tab : $method_data['name'][$language_id]; 

        if(isset($method_data['rate_start']) && is_array($method_data['rate_start'])) {

            foreach ($method_data['rate_start'] as $inc=>$rate_start) { 

                if(!isset($method_data['rate_partial'][$inc])) $method_data['rate_partial'][$inc]='0'; 

                $out .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed,            
                    stripslashes($rate_start)) . $csv_enclosed;         
                $out .= $csv_separator;

                $out .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed,            
                    stripslashes($method_data['rate_end'][$inc])) . $csv_enclosed;          
                $out .= $csv_separator;

                $out .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed,            
                    stripslashes($method_data['rate_total'][$inc])) . $csv_enclosed;            
                $out .= $csv_separator;

                $out .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed,            
                    stripslashes($method_data['rate_block'][$inc])) . $csv_enclosed;            
                $out .= $csv_separator;

                $out .= $csv_enclosed . str_replace($csv_enclosed, $csv_escaped . $csv_enclosed,            
                    stripslashes($method_data['rate_partial'][$inc])) . $csv_enclosed;          

                $out .= $csv_terminated;
            }
        }   

        $filename = str_replace(array('#',' ',"'",'"','!','@','#','$','%','^','&','*','(',')','~','`'),'_',$method_name).'.csv'; 

        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Length: " . strlen($out));
        header("Content-type: text/x-csv");
        header("Content-Disposition: attachment; filename=$filename");
        echo $out;
        exit;

    }


    public function import(){

        $this->load->model('setting/setting');
        $this->load->model('extension/xshippingpro/xshippingpro');
        $success = false;

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && is_uploaded_file($this->request->files['file_import']['tmp_name']) && file_exists($this->request->files['file_import']['tmp_name'])) {
            
            $import_data = file_get_contents($this->request->files['file_import']['tmp_name']);
            if($import_data) {

                $import_data=@unserialize(@base64_decode($import_data));


                if(is_array($import_data) && (isset($import_data['method_data']) || isset($import_data['xshippingpro']))) {

                    $save=array();

                    if (isset($import_data['shipping_xshippingpro_status'])) {
                        $save['shipping_xshippingpro_status']= $import_data['shipping_xshippingpro_status'];
                    } else if (isset($import_data['xshippingpro_status'])) {
                        $save['shipping_xshippingpro_status']= $import_data['xshippingpro_status'];
                    } else {
                        $save['shipping_xshippingpro_status'] = 1; 
                    }

                    if (isset($import_data['shipping_xshippingpro_group'])) {
                        $save['shipping_xshippingpro_group']= $import_data['shipping_xshippingpro_group'];
                    } else if (isset($import_data['xshippingpro_group'])) {
                        $save['shipping_xshippingpro_group']= $import_data['xshippingpro_group'];
                    }

                    if (isset($import_data['shipping_xshippingpro_group_limit'])) {
                        $save['shipping_xshippingpro_group_limit']= $import_data['shipping_xshippingpro_group_limit'];
                    } else if (isset($import_data['xshippingpro_group_limit'])) {
                        $save['shipping_xshippingpro_group_limit']= $import_data['xshippingpro_group_limit'];
                    }

                    if (isset($import_data['shipping_xshippingpro_heading'])) {
                        $save['shipping_xshippingpro_heading']= $import_data['shipping_xshippingpro_heading'];
                    } else if (isset($import_data['xshippingpro_heading'])) {
                        $save['shipping_xshippingpro_heading']= $import_data['xshippingpro_heading'];
                    }

                    if (isset($import_data['shipping_xshippingpro_sort_order'])) {
                        $save['shipping_xshippingpro_sort_order']= $import_data['shipping_xshippingpro_sort_order'];
                    } else if (isset($import_data['xshippingpro_sort_order'])) {
                        $save['shipping_xshippingpro_sort_order']= $import_data['xshippingpro_sort_order'];
                    }

                    if (isset($import_data['shipping_xshippingpro_desc_mail'])) {
                        $save['shipping_xshippingpro_desc_mail']= 1;
                    } else if (isset($import_data['xshippingpro_desc_mail'])) {
                        $save['shipping_xshippingpro_desc_mail']= 1;
                    } else {
                        $save['shipping_xshippingpro_desc_mail']= 0;
                    }


                    if (isset($import_data['shipping_xshippingpro_debug'])) {
                        $save['shipping_xshippingpro_debug']= $import_data['shipping_xshippingpro_debug'];
                    } else if (isset($import_data['xshippingpro_debug'])) {
                        $save['shipping_xshippingpro_debug']= $import_data['xshippingpro_debug'];
                    }

                    if (isset($import_data['shipping_xshippingpro_sorting'])) {
                        $save['shipping_xshippingpro_sorting']= $import_data['shipping_xshippingpro_sorting'];
                    } else if (isset($import_data['xshippingpro_sorting'])) {
                        $save['shipping_xshippingpro_sorting']= $import_data['xshippingpro_sorting'];
                    }

                    if (isset($import_data['shipping_xshippingpro_sub_group'])) {
                        $save['shipping_xshippingpro_sub_group']= $import_data['shipping_xshippingpro_sub_group'];
                    } else if (isset($import_data['xshippingpro_sub_group'])) {
                        $save['shipping_xshippingpro_sub_group']= $import_data['xshippingpro_sub_group'];
                    }

                    if (isset($import_data['shipping_xshippingpro_sub_group_limit'])) {
                        $save['shipping_xshippingpro_sub_group_limit']= $import_data['shipping_xshippingpro_sub_group_limit'];
                    } else if (isset($import_data['xshippingpro_sub_group_limit'])) {
                        $save['shipping_xshippingpro_sub_group_limit']= $import_data['xshippingpro_sub_group_limit'];
                    }

                    if (isset($import_data['shipping_xshippingpro_sub_group_name'])) {
                        $save['shipping_xshippingpro_sub_group_name']= $import_data['shipping_xshippingpro_sub_group_name'];
                    } else if (isset($import_data['xshippingpro_sub_group_name'])) {
                        $save['shipping_xshippingpro_sub_group_name']= $import_data['xshippingpro_sub_group_name'];
                    }

                    $this->model_setting_setting->editSetting('shipping_xshippingpro', $save);

                    if (isset($import_data['xshippingpro']) && $import_data['xshippingpro']) {
                        $this->latencyImport($import_data['xshippingpro']);
                    }

                    if (isset($import_data['method_data']) && $import_data['method_data'] && is_array($import_data['method_data'])) {
                        foreach($import_data['method_data'] as $single) {
                            $this->model_extension_xshippingpro_xshippingpro->addData($single);
                        }
                    }
                    $success = true;

                }
            }       

        } 

        if($success) {
            $this->session->data['success'] = $this->language->get('text_success');
        } else {
            $this->session->data['warning'] = $this->language->get('error_import');
        }
        
    }

    public function latencyImport($data) {
        
        $this->load->model('extension/xshippingpro/xshippingpro');
        
        if ($data) {
            $data=unserialize(base64_decode($data)); 
        }
        
        if (!is_array($data)) $data=array();

        $methods = array();
        foreach($data as $key=>$value) {
            if($value  && is_array($value)) {
                foreach ($value as $tab_id => $field_value) {
                    $methods[$tab_id][$key] = $field_value;
                }
            }
        }

        foreach ($methods as $tab_id => $method_data) {
            $save = array();
            $save['method_data']=base64_encode(serialize($method_data));
            $save['tab_id'] = $tab_id;
            $this->model_extension_xshippingpro_xshippingpro->addData($save);
        }

    }     

    public function csv_upload(){

        ini_set('auto_detect_line_endings', true);
        $this->load->language('extension/shipping/xshippingpro');

        $json = array();
        if (!empty($this->request->files['file']['name'])) {
            $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));

            $allowed=  array('csv');
            if (!in_array(substr(strrchr($filename, '.'), 1), $allowed)) {
                $json['error'] = $this->language->get('error_filetype');
            }
            if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                $json['error'] = $this->language->get('error_partial');
            }
        }
        else{
            $json['error']=$this->language->get('error_upload');  
        }

        if (!$json && is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {

            $isFound=false;
            $json['data']=array();
            if (($handle = fopen($this->request->files['file']['tmp_name'], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $start=$data[0];  
                    $end=$data[1]; 
                    $cost=$data[2]; 
                    $pg=isset($data[3])?$data[3]:0; 
                    $pa=isset($data[4])?$data[4]:0; 
                    if(is_numeric($start) && is_numeric($end) && is_numeric($cost)){
                        $json['data'][]=array('start'=>(float)$start,'end'=>(float)$end,'cost'=>(float)$cost,'pg'=>(float)$pg,'pa'=>(int)$pa); 
                        $isFound=true;
                    }
                }
                fclose($handle);
            }
            if(!$isFound)$json['error']=$this->language->get('error_no_data');     
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json)); 

    }

    private function validate() {
        $this->load->language('extension/shipping/xshippingpro');
        if (!$this->user->hasPermission('modify', 'extension/shipping/xshippingpro')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->error) {
            return true;
        } else {
            return false;
        }   
    }
    
    public function copyMehthod()
    {
        $tabId=$this->requrest->get['tabId'];
    }
    
    public function install(){
        $this->load->model('extension/xshippingpro/xshippingpro');
        $this->model_extension_xshippingpro_xshippingpro->install();
    }

    public function uninstall(){        
        $this->load->model('extension/xshippingpro/xshippingpro');
        $this->model_extension_xshippingpro_xshippingpro->uninstall();
    }

    private function getFormData($data, $new_tab = false)
    {
        $this->load->model('catalog/category');
        $this->load->model('catalog/product');
        $this->load->model('catalog/option');
        $this->load->model('catalog/manufacturer');

        if ($new_tab) {
            $data['method_data'] = array(
                array('tab_id' => '__INDEX__', 'method_data' => '')
                );  
        }

        $defaul_values = $this->getInitialValues();

        $return='';
        foreach($data['method_data'] as $single_method) {
            $no_of_tab = $single_method['tab_id'];
            $method_data = $single_method['method_data'];
            $method_data = @unserialize(@base64_decode($method_data));
            if(!is_array($method_data)) $method_data = array();

            if ($new_tab) $method_data = $this->getDefaultValues();

            $method_data = array_merge($defaul_values, $method_data);

            /* backward compatibility - if all set, it means no manufacturer were set manufacturer_all is deprecated */
            if(isset($method_data['manufacturer_all']) && $method_data['manufacturer_all']) $method_data['manufacturer_rule']='1';
            if(isset($method_data['city']) && !$method_data['city']) $method_data['city_all']='1';
            if(isset($method_data['country']) && !$method_data['country']) $method_data['country_all']='1';
            if(isset($method_data['payment_all']) && !$method_data['payment_all'] && !$method_data['payment']) $method_data['payment_all']='1';
            if(isset($method_data['currency_all']) && !$method_data['currency_all'] && !$method_data['currency']) $method_data['currency_all']='1';

            if($method_data['rate_type'] == 'total_method' || $method_data['rate_type'] == 'sub_method' || $method_data['rate_type'] == 'quantity_method' || $method_data['rate_type'] == 'weight_method' || $method_data['rate_type'] == 'dimensional_method' || $method_data['rate_type'] == 'volume_method') {
                $method_data['method_specific'] = '1';
            }

            if($method_data['rate_type'] == 'sub_method') {
                $method_data['rate_type'] = 'sub';
            } 

            if($method_data['rate_type'] == 'total_method') {
                $method_data['rate_type'] = 'total';
            }

            if($method_data['rate_type'] == 'weight_method') {
                $method_data['rate_type'] = 'weight';
            }

            if($method_data['rate_type'] == 'dimensional_method') {
                $method_data['rate_type'] = 'dimensional';
            }

            if($method_data['rate_type'] == 'volume_method') {
                $method_data['rate_type'] = 'volume';
            }
            
            /* end of backward compatibility */

            

            $return.='<div id="shipping-'.$no_of_tab.'" class="tab-pane shipping">'
            .'<div class="form-group row display-name-row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-display'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['text_admin_name_tip'].'">'.$data['text_admin_name'].' </span></label>'
            .'<div class="col-sm-9">'
            .'<input style="width:250px" type="text" name="xshippingpro[display]" value="'.$method_data['display'].'" class="form-control display-name" id="input-display'.$no_of_tab.'" />'
            .'<div class="action-btn">'
            .'<button class="btn btn-warning btn-copy" data-toggle="tooltip" type="button" data-original-title="'.$data['text_method_copy'].'"><i class="fa fas fa-copy"></i></button>'
            .'<button class="btn btn-danger btn-delete" data-toggle="tooltip" type="button" data-original-title="'.$data['text_method_remove'].'"><i class="fa fas fa-trash fa-trash-alt"></i></button>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'<ul class="nav nav-tabs" id="language'.$no_of_tab.'">';

            $inc=0; 
            foreach ($data['languages'] as $language) { 
                $active_cls=($inc==0) ? 'active"':''; 
                $inc++;
                $return.='<li class="nav-item '.$active_cls.'"><a href="#language'.$language['language_id'].'_'.$no_of_tab.'" class="nav-link '.$active_cls.'" data-toggle="tab"><img src="'.$data['language_dir'].$language['image'].'" title="'.$language['name'].'" /> '.$language['name'].'</a></li>';
            } 
            $return.='</ul>'
            .'<div class="tab-content">';

            $inc=0;
            foreach ($data['languages'] as $language) { 
                $active_cls=($inc==0) ?' active':''; 
                $lang_cls=($inc==0)?'':'-lang'; $inc++; 
                if(!isset($method_data['name'][$language['language_id']]) || !$method_data['name'][$language['language_id']])$method_data['name'][$language['language_id']]='Untitled Method '.$no_of_tab; 
                if(!isset($method_data['desc'][$language['language_id']]) || !$method_data['desc'][$language['language_id']])$method_data['desc'][$language['language_id']]='';
                
                $return.='<div class="tab-pane'.$active_cls.'" id="language'.$language['language_id'].'_'.$no_of_tab.'">'
                .'<div class="form-group row required">'
                .'<label class="col-sm-3 control-label col-form-label" for="lang-name-'.$no_of_tab.''.$language['language_id'].'"><span data-toggle="tooltip" title="'.$data['text_name_tip'].'">'.$data['entry_name'].'</span></label>'
                .'<div class="col-sm-9">'
                .'<input type="text" name="xshippingpro[name]['.$language['language_id'].']" value="'.$method_data['name'][$language['language_id']].'" placeholder="'.$data['entry_name'].'" id="lang-name-'.$no_of_tab.''.$language['language_id'].'" class="form-control method-name'.$lang_cls.'" />'
                .'</div>'
                .'</div>'
                .'<div class="form-group row">'
                .'<label class="col-sm-3 control-label col-form-label" for="lang-desc-'.$no_of_tab.''.$language['language_id'].'"><span data-toggle="tooltip" title="'.$data['tip_desc'].'">'.$data['entry_desc'].' </span></label>'
                .'<div class="col-sm-9">'
                .'<input type="text" name="xshippingpro[desc]['.$language['language_id'].']" value="'.$method_data['desc'][$language['language_id']].'" placeholder="'.$data['entry_desc'].'" id="lang-desc-'.$no_of_tab.''.$language['language_id'].'" class="form-control" />'
                .'</div>'
                .'</div>'
                .'</div>';
            } 
            $return.='</div>'
            .'<ul class="nav nav-tabs method-tab" id="method-tab-'.$no_of_tab.'">'
            .'<li class="nav-item active"><a href="#common_'.$no_of_tab.'" class="nav-link active" data-toggle="tab">'.$data['text_general'].'</a></li>'
            .'<li class="nav-item"><a href="#criteria_'.$no_of_tab.'" class="nav-link" data-toggle="tab">'.$data['text_criteria_setting'].'</a></li>'
            .'<li class="nav-item"><a href="#catprod_'.$no_of_tab.'" class="nav-link" data-toggle="tab">'.$data['text_category_product'].'</a></li>'
            .'<li class="nav-item"><a href="#price_'.$no_of_tab.'" class="nav-link" data-toggle="tab">'.$data['text_price_setting'].'</a></li>'
            .'<li class="nav-item"><a href="#other_'.$no_of_tab.'" class="nav-link" data-toggle="tab">'.$data['text_others'].'</a></li>'
            .'</ul>' 
            .'<div class="tab-content method-content">'
            .'<div class="tab-pane active" id="common_'.$no_of_tab.'">'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-weight'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_weight_include'].'">'.$data['entry_weight_include'].'</span></label>'
            .'<div class="col-sm-9"><input '.(($method_data['inc_weight']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[inc_weight]" value="1" id="input-weight'.$no_of_tab.'" /></div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-tax-class'.$no_of_tab.'">'.$data['entry_tax'].'</label>'
            .'<div class="col-sm-9"><select id="input-tax-class'.$no_of_tab.'" name="xshippingpro[tax_class_id]" class="form-control" >'
            .'<option value="0">'.$data['text_none'].'</option>';

            foreach ($data['tax_classes'] as $tax_class) { 
                $return.='<option '.(($method_data['tax_class_id']==$tax_class['tax_class_id'])?'selected':'').' value="'.$tax_class['tax_class_id'].'">'.$tax_class['title'].'</option>';
            } 
            $return.='</select></div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-logo'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_text_logo'].'">'.$data['text_logo'].' </span></label>'
            .'<div class="col-sm-9"><input type="text" name="xshippingpro[logo]" value="'.$method_data['logo'].'" class="form-control" id="input-logo'.$no_of_tab.'" /></div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-sortorder'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_sorting_own'].'">'.$data['entry_sort_order'].' </span></label>'
            .'<div class="col-sm-9"><input type="text" name="xshippingpro[sort_order]" value="'.$method_data['sort_order'].'" class="form-control" id="input-sortorder'.$no_of_tab.'" /></div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-status'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_status_own'].'">'.$data['entry_status'].'</span></label>'
            .'<div class="col-sm-9"><select class="form-control" id="input-status'.$no_of_tab.'" name="xshippingpro[status]">'
            .'<option value="1" '.(($method_data['status']==1 || $method_data['status']=='')?'selected':'').'>'.$data['text_enabled'].'</option>'
            .'<option value="0" '.(($method_data['status']==0)?'selected':'').'>'.$data['text_disabled'].'</option>'
            .'</select></div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-group'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['entry_group_tip'].'">'.$data['entry_group'].'</span></label>'
            .'<div class="col-sm-9"><select class="form-control" id="input-group'.$no_of_tab.'" name="xshippingpro[group]">'
            .'<option value="0">'.$data['text_group_none'].'</option>';

            for($sg=1; $sg<=$data['shipping_xshippingpro_sub_groups_count'];$sg++) { 
                $return.='<option '.(($method_data['group']==$sg)?'selected':'').' value="'.$sg.'">Group'.$sg.'</option>';
            } 
            $return.='</select></div>'
            .'</div>'
            .'</div>'
            .'<div class="tab-pane" id="criteria_'.$no_of_tab.'">'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label"><span data-toggle="tooltip" title="'.$data['tip_store'].'">'.$data['entry_store'].'</span></label>' 
            .'<div class="col-sm-9">'
            .'<label class="any-class"><input '.(($method_data['store_all']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[store_all]" class="choose-any" value="1" />&nbsp;'.$data['text_any'].'</label>'
            .'<div class="well well-sm form-control xdata-box" style="height: 70px; overflow: auto;'.(($method_data['store_all']!='1')?'display:flex':'').'">'
            .'<div class="checkbox xshipping-checkbox">';

            foreach ($data['stores'] as $store) {
                $return.='<label>'
                .'<input '.((in_array($store['store_id'],$method_data['store']))?'checked':'').' type="checkbox" name="xshippingpro[store][]" value="'.$store['store_id'].'" />'.$store['name'].''
                .'</label>';
            } 
            $return.='</div>'
            .'</div>
              <div style="'.(($method_data['store_all']!='1')?'display:flex':'').'" class="checkbox-selection-wrap"><a href="#" class="check-all">'.$data['check_all'].'</a><a href="#" class="uncheck-all">'.$data['uncheck_all'].'</a></div>'
            .'</div>'
            .'</div>'

            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label"><span data-toggle="tooltip" title="'.$data['tip_geo'].'">'.$data['entry_geo_zone'].'</span></label>' 
            .'<div class="col-sm-9">'
            .'<label class="any-class"><input '.(($method_data['geo_zone_all']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[geo_zone_all]" class="choose-any" value="1" />&nbsp;'.$data['text_any'].'</label>'
            .'<div class="well well-sm form-control xdata-box" style="height: 100px; overflow: auto;'.(($method_data['geo_zone_all']!='1')?'display:flex':'').'">'
            .'<div class="checkbox xshipping-checkbox">';

            foreach ($data['geo_zones'] as $geo_zone) {

                $return.='<label>'
                .'<input '.((in_array($geo_zone['geo_zone_id'],$method_data['geo_zone_id']))?'checked':'').' type="checkbox" name="xshippingpro[geo_zone_id][]" value="'.$geo_zone['geo_zone_id'].'" />'.$geo_zone['name'].''
                .'</label>';
            } 
            $return.='</div>'
            .'</div>
              <div style="'.(($method_data['geo_zone_all']!='1')?'display:flex':'').'" class="checkbox-selection-wrap"><a href="#" class="check-all">'.$data['check_all'].'</a><a href="#" class="uncheck-all">'.$data['uncheck_all'].'</a></div>'
            .'</div>'
            .'</div>'

            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-city'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_city'].'">'.$data['text_city'].'</span></label>' 
            .'<div class="col-sm-9">'
            .'<label class="any-class"><input '.(($method_data['city_all']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[city_all]" class="choose-any-with" rel="city-option" value="1" id="input-city'.$no_of_tab.'" />'.$data['text_any'].'</label>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row city-option" '.(($method_data['city_all']!='1')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-city_data'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['text_city_enter_tip'].'">'.$data['text_city_enter'].'</span></label>'
            .'<div class="col-sm-9"><textarea class="form-control" id="input-city_data'.$no_of_tab.'" name="xshippingpro[city]" rows="8" cols="70" />'.$method_data['city'].'</textarea></div>'
            .'</div>'
            .'<div class="form-group row city-option" '.(($method_data['city_all']!='1')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-city-rule'.$no_of_tab.'">'.$data['text_city_rule'].'</label>'
            .'<div class="col-sm-9"><select class="form-control" id="input-city-rule'.$no_of_tab.'" name="xshippingpro[city_rule]">'
            .'<option value="inclusive" '.(($method_data['city_rule']=='inclusive')?'selected':'').'>'.$data['text_city_rule_inclusive'].'</option>'
            .'<option value="exclusive" '.(($method_data['city_rule']=='exclusive')?'selected':'').'>'.$data['text_city_rule_exclusive'].'</option>'
            .'</select></div>'
            .'</div>' 

            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label"><span data-toggle="tooltip" title="'.$data['text_country_tip'].'">'.$data['text_country'].'</span></label>' 
            .'<div class="col-sm-9">'
            .'<label class="any-class"><input '.(($method_data['country_all']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[country_all]" class="choose-any" value="1" />&nbsp;'.$data['text_any'].'</label>'
              .'<div class="well well-sm form-control xdata-box" style="height: 115px; overflow: auto;'.(($method_data['country_all']!='1')?'display:flex':'').'"><div class="checkbox xshipping-checkbox">';
            foreach ($data['countries'] as $country) {

                $return.='<label>'
                .'<input '.((in_array($country['country_id'],$method_data['country']))?'checked':'').' type="checkbox" name="xshippingpro[country][]" value="'.$country['country_id'].'" />'.$country['name'].''
                .'</label>';

            }

            $return.='</div>
             </div>
             <div style="'.(($method_data['country_all']!='1')?'display:flex':'').'" class="checkbox-selection-wrap"><a href="#" class="check-all">'.$data['check_all'].'</a><a href="#" class="uncheck-all">'.$data['uncheck_all'].'</a></div>'
            .'</div>'
            .'</div>'

            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label"><span data-toggle="tooltip" title="'.$data['tip_customer_group'].'">'.$data['entry_customer_group'].'</span></label>' 
            .'<div class="col-sm-9">'
            .'<label class="any-class"><input '.(($method_data['customer_group_all']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[customer_group_all]" class="choose-any" value="1" />&nbsp;'.$data['text_any'].'</label>'
            .'<div class="well well-sm form-control xdata-box" style="height: 70px; overflow: auto;'.(($method_data['customer_group_all']!='1')?'display:flex':'').'">'
            .'<div class="checkbox xshipping-checkbox">';

            foreach ($data['customer_groups'] as $customer_group) {

                $return.='<label>'
                .'<input '.((in_array($customer_group['customer_group_id'],$method_data['customer_group']))?'checked':'').' type="checkbox" name="xshippingpro[customer_group][]" value="'.$customer_group['customer_group_id'].'" />'.$customer_group['name'].''
                .'</label>';
            } 
            $return.='</div>'
            .'</div>
             <div style="'.(($method_data['customer_group_all']!='1')?'display:flex':'').'" class="checkbox-selection-wrap"><a href="#" class="check-all">'.$data['check_all'].'</a><a href="#" class="uncheck-all">'.$data['uncheck_all'].'</a></div>'
            .'</div>'
            .'</div>'

            .'<div class="form-group row">'
                .'<label class="col-sm-3 control-label col-form-label"><span data-toggle="tooltip" title="'.$data['text_currency_tip'].'">'.$data['text_currency'].'</span></label>' 
                 .'<div class="col-sm-9">'
                    .'<label class="any-class"><input '.(($method_data['currency_all']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[currency_all]" class="choose-any" value="1" />&nbsp;'.$data['text_any'].'</label>'
                    .'<div class="well well-sm form-control xdata-box" style="height: 100px; overflow: auto;'.(($method_data['currency_all']!='1')?'display:flex':'').'">'
                     .'<div class="checkbox xshipping-checkbox">';
                    
                    foreach ($data['currencies'] as $currency) {
                    
                     $return.='<label>'
                       .'<input '.((in_array($currency['currency_id'],$method_data['currency']))?'checked':'').' type="checkbox" name="xshippingpro[currency][]" value="'.$currency['currency_id'].'" />'.$currency['title'].''
                     .'</label>';
                      } 
                  $return.='</div>'
                   .'</div>'
                .'</div>'
               .'</div>'
            
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label"><span data-toggle="tooltip" title="'.$data['tip_payment'].'">'.$data['entry_payment'].'</span></label>' 
            .'<div class="col-sm-9">'
            .'<label class="any-class"><input '.(($method_data['payment_all']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[payment_all]" class="choose-any" value="1" />&nbsp;'.$data['text_any'].'</label>'
            .'<div class="well well-sm form-control xdata-box" style="height: 70px; overflow: auto;'.(($method_data['payment_all']!='1')?'display:flex':'').'">'
            .'<div class="checkbox xshipping-checkbox">';

             foreach ($data['payment_mods'] as $code=>$value) {
                             
                      if (isset($data[$code]) && is_array($data[$code])) {
                         $prefix=$value;
                         foreach($data[$code] as $code =>$value) {
                           $return.='<label>'
                                    .'<input '.((in_array($code,$method_data['payment']))?'checked':'').' type="checkbox" name="xshippingpro[payment][]" value="'.$code.'" />'.$prefix.'- '.$value.''
                                    .'</label>';
                          }
                         continue;
                       }
                  $return.='<label>'
                   .'<input '.((in_array($code,$method_data['payment']))?'checked':'').' type="checkbox" name="xshippingpro[payment][]" value="'.$code.'" />'.$value.''
                 .'</label>';
                 } 
                 
            $return.='</div>'
            .'</div>
              <div style="'.(($method_data['payment_all']!='1')?'display:flex':'').'" class="checkbox-selection-wrap"><a href="#" class="check-all">'.$data['check_all'].'</a><a href="#" class="uncheck-all">'.$data['uncheck_all'].'</a></div>'
            .'</div>'
            .'</div>'

            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-postal'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_zip'].'">'.$data['text_zip_postal'].'</span></label>' 
            .'<div class="col-sm-9">'
            .'<label class="any-class"><input '.(($method_data['postal_all']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[postal_all]" class="choose-any-with" rel="postal-option" value="1" id="input-postal'.$no_of_tab.'" />'.$data['text_any'].'</label>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row postal-option" '.(($method_data['postal_all']!='1')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-zip'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_postal_code'].'">'.$data['text_enter_zip'].'</span></label>'
            .'<div class="col-sm-9"><textarea class="form-control" id="input-zip'.$no_of_tab.'" name="xshippingpro[postal]" rows="8" cols="70" />'.$method_data['postal'].'</textarea></div>'
            .'</div>'
            .'<div class="form-group row postal-option" '.(($method_data['postal_all']!='1')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-zip-rule'.$no_of_tab.'">'.$data['text_zip_rule'].'</label>'
            .'<div class="col-sm-9"><select class="form-control" id="input-zip-rule'.$no_of_tab.'" name="xshippingpro[postal_rule]">'
            .'<option value="inclusive" '.(($method_data['postal_rule']=='inclusive')?'selected':'').'>'.$data['text_zip_rule_inclusive'].'</option>'
            .'<option value="exclusive" '.(($method_data['postal_rule']=='exclusive')?'selected':'').'>'.$data['text_zip_rule_exclusive'].'</option>'
            .'</select></div>'
            .'</div>'  

            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-coupon'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_coupon'].'">'.$data['text_coupon'].'</span></label>' 
            .'<div class="col-sm-9">'
            .'<label class="any-class"><input '.(($method_data['coupon_all']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[coupon_all]" class="choose-any-with" rel="coupon-option" value="1" id="input-coupon'.$no_of_tab.'" />'.$data['text_any'].'</label>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row coupon-option" '.(($method_data['coupon_all']!='1')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-coupon-here'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['text_coupon_tip'].'">'.$data['text_enter_coupon'].'</span></label>'
            .'<div class="col-sm-9"><textarea class="form-control" id="input-coupon-here'.$no_of_tab.'" name="xshippingpro[coupon]" rows="8" cols="70" />'.$method_data['coupon'].'</textarea></div>'
            .'</div>'
            .'<div class="form-group row coupon-option" '.(($method_data['coupon_all']!='1')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-coupon-rule'.$no_of_tab.'">'.$data['text_coupon_rule'].'</label>'
            .'<div class="col-sm-9"><select class="form-control" id="input-coupon-rule'.$no_of_tab.'" name="xshippingpro[coupon_rule]">'
            .'<option value="inclusive" '.(($method_data['coupon_rule']=='inclusive')?'selected':'').'>'.$data['text_coupon_rule_inclusive'].'</option>'
            .'<option value="exclusive" '.(($method_data['coupon_rule']=='exclusive')?'selected':'').'>'.$data['text_coupon_rule_exclusive'].'</option>'
            .'</select></div>'
            .'</div>'
            .'</div>' 
            .'<div class="tab-pane" id="catprod_'.$no_of_tab.'">'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-cat-rule'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_category'].'">'.$data['text_category'].'</span></label>'
            .'<div class="col-sm-9"><select id="input-cat-rule'.$no_of_tab.'" class="form-control selection" rel="category" name="xshippingpro[category]">'
            .'<option value="1" '.(($method_data['category']==1)?'selected':'').'>'.$data['text_category_any'].'</option>'
            .'<option value="6" '.(($method_data['category']==6)?'selected':'').'>'.$data['text_category_least'].'</option>'
            .'<option value="3" '.(($method_data['category']==3)?'selected':'').'>'.$data['text_category_least_with_other'].'</option>'
            .'<option value="4" '.(($method_data['category']==4)?'selected':'').'>'.$data['text_category_exact'].'</option>'
            .'<option value="2" '.(($method_data['category']==2)?'selected':'').'>'.$data['text_category_all'].'</option>'
            .'<option value="5" '.(($method_data['category']==5)?'selected':'').'>'.$data['text_category_except'].'</option>'
            .'<option value="7" '.(($method_data['category']==7)?'selected':'').'>'.$data['text_category_except_other'].'</option>'
            .'</select></div>'
            .'</div>'

            .'<div class="form-group row category" '.(($method_data['category']!=1)?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-category'.$no_of_tab.'">'.$data['entry_category'].'</label>'
            .'<div class="col-sm-9"><input type="text" name="category" value="" placeholder="'.$data['entry_category'].'" id="input-category'.$no_of_tab.'" class="form-control" />'
            .'<div class="well well-sm form-control xdata-box product-category" style="height: 150px; overflow: auto;">';
            foreach ($method_data['product_category'] as $category_id) {
                $category_info = $this->model_catalog_category->getCategory($category_id);

                if(!$category_info) {
                    $category_info['path'] = '';
                    $category_info['name'] = '';
                }

                if($category_info['path']) $category_info['path'] .=  '&nbsp;&nbsp;&gt;&nbsp;&nbsp;';
                $return.='<div class="product-category'.$category_id. '"><i class="fa fas fa-minus-circle"></i> '.$category_info['path'].$category_info['name'].'<input type="hidden" class="category" name="xshippingpro[product_category][]" value="'.$category_id.'" /></div>';
            }
            $return.='</div><a class="batch-selection" href="javascript:categoryBrowser();">'.$data['text_batch_select'].'</a><a class="remove-selection" rel="product-category" href="#">'.$data['text_remove_all'].'</a>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-product_rule'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_product'].'">'.$data['text_product'].'</span></label>'
            .'<div class="col-sm-9"><select id="input-product_rule'.$no_of_tab.'" class="form-control selection" rel="product" name="xshippingpro[product]">'
            .'<option value="1" '.(($method_data['product']==1)?'selected':'').'>'.$data['text_product_any'].'</option>'
            .'<option value="6" '.(($method_data['product']==6)?'selected':'').'>'.$data['text_product_least'].'</option>'
            .'<option value="3" '.(($method_data['product']==3)?'selected':'').'>'.$data['text_product_least_with_other'].'</option>'
            .'<option value="4" '.(($method_data['product']==4)?'selected':'').'>'.$data['text_product_exact'].'</option>'
            .'<option value="2" '.(($method_data['product']==2)?'selected':'').'>'.$data['text_product_all'].'</option>'
            .'<option value="5" '.(($method_data['product']==5)?'selected':'').'>'.$data['text_product_except'].'</option>'
            .'<option value="7" '.(($method_data['product']==7)?'selected':'').'>'.$data['text_product_except_other'].'</option>'
            .'</select></div>'
            .'</div>'
            .'<div class="form-group row product" ' .(($method_data['product']!=1)?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-product'.$no_of_tab.'">'.$data['entry_product'].'</label>'
            .'<div class="col-sm-9"><input type="text" name="product" value="" placeholder="'.$data['entry_product'].'" id="input-product'.$no_of_tab.'" class="form-control" />'
            .'<div class="well well-sm form-control xdata-box product-product" style="height: 150px; overflow: auto;">';
            foreach ($method_data['product_product'] as $product_id) {
                $product_info = $this->model_catalog_product->getProduct($product_id);
                if(!$product_info) {
                    $product_info['name'] = '';
                }
                $return.='<div class="product-product'.$product_id. '"><i class="fa fas fa-minus-circle"></i> '.(isset($product_info['name'])?$product_info['name']:'').'<input type="hidden" name="xshippingpro[product_product][]" value="'.$product_id.'" /></div>';

            }
            $return.='</div>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-option_rule'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_option'].'">'.$data['text_option'].'</span></label>'
            .'<div class="col-sm-9"><select id="input-option_rule'.$no_of_tab.'" class="form-control selection" rel="option" name="xshippingpro[option]">'
            .'<option value="1" '.(($method_data['option']==1)?'selected':'').'>'.$data['text_option_any'].'</option>'
            .'<option value="6" '.(($method_data['option']==6)?'selected':'').'>'.$data['text_option_least'].'</option>'
            .'<option value="3" '.(($method_data['option']==3)?'selected':'').'>'.$data['text_option_least_with_other'].'</option>'
            .'<option value="4" '.(($method_data['option']==4)?'selected':'').'>'.$data['text_option_exact'].'</option>'
            .'<option value="2" '.(($method_data['option']==2)?'selected':'').'>'.$data['text_option_all'].'</option>'
            .'<option value="5" '.(($method_data['option']==5)?'selected':'').'>'.$data['text_option_except'].'</option>'
            .'<option value="7" '.(($method_data['option']==7)?'selected':'').'>'.$data['text_option_except_other'].'</option>'
            .'</select></div>'
            .'</div>'
            .'<div class="form-group row option" ' .(($method_data['option']!=1)?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-option'.$no_of_tab.'">'.$data['entry_option'].'</label>'
            .'<div class="col-sm-9"><input type="text" name="input_option" value="" placeholder="'.$data['entry_option'].'" id="input-option'.$no_of_tab.'" class="form-control" />'
            .'<div class="well well-sm form-control xdata-box product-option" style="height: 150px; overflow: auto;">';
            foreach ($method_data['product_option'] as $option_value_id) {
                $optn_name = '';
                $option_value_info = $this->model_catalog_option->getOptionValue($option_value_id);
                if($option_value_info) {
                    $option_info = $this->model_catalog_option->getOption($option_value_info['option_id']);
                    if($option_info) {
                        $optn_name = strip_tags(html_entity_decode($option_info['name'], ENT_QUOTES, 'UTF-8')).'&nbsp;&nbsp;&gt;&nbsp;&nbsp;'.strip_tags(html_entity_decode($option_value_info['name'], ENT_QUOTES, 'UTF-8'));
                    }
                }
                $return.='<div class="product-option'.$option_value_id. '"><i class="fa fas fa-minus-circle"></i> '.$optn_name.'<input type="hidden" name="xshippingpro[product_option][]" value="'.$option_value_id.'" /></div>';

            }
            $return.='</div>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-manufacturer_rule'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_manufacturer_rule'].'">'.$data['text_manufacturer_rule'].'</span></label>'
            .'<div class="col-sm-9"><select id="input-manufacturer_rule'.$no_of_tab.'" class="form-control selection" rel="manufacturer" name="xshippingpro[manufacturer_rule]">'
            .'<option value="1" '.(($method_data['manufacturer_rule']==1)?'selected':'').'>'.$data['text_manufacturer_any'].'</option>'
            .'<option value="6" '.(($method_data['manufacturer_rule']==6)?'selected':'').'>'.$data['text_manufacturer_least'].'</option>'
            .'<option value="3" '.(($method_data['manufacturer_rule']==3)?'selected':'').'>'.$data['text_manufacturer_least_with_other'].'</option>'
            .'<option value="4" '.(($method_data['manufacturer_rule']==4)?'selected':'').'>'.$data['text_manufacturer_exact'].'</option>'
            .'<option value="2" '.(($method_data['manufacturer_rule']==2)?'selected':'').'>'.$data['text_manufacturer_all'].'</option>'
            .'<option value="5" '.(($method_data['manufacturer_rule']==5)?'selected':'').'>'.$data['text_manufacturer_except'].'</option>'
            .'<option value="7" '.(($method_data['manufacturer_rule']==7)?'selected':'').'>'.$data['text_manufacturer_except_other'].'</option>'
            .'</select></div>'
            .'</div>'
            .'<div class="form-group row manufacturer" ' .(($method_data['manufacturer_rule']!=1)?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-manufacturer'.$no_of_tab.'">'.$data['entry_manufacturer'].'</label>'
            .'<div class="col-sm-9"><input type="text" name="input_manufacturer" value="" placeholder="'.$data['entry_manufacturer'].'" id="input-manufacturer'.$no_of_tab.'" class="form-control" />'
            .'<div class="well well-sm form-control xdata-box product-manufacturer" style="height: 150px; overflow: auto;">';
            foreach ($method_data['manufacturer'] as $manufacturer_id) {
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);
                if($manufacturer_info) {
                    $return.='<div class="product-manufacturer'.$manufacturer_id. '"><i class="fa fas fa-minus-circle"></i> '.$manufacturer_info['name'].'<input type="hidden" name="xshippingpro[manufacturer][]" value="'.$manufacturer_id.'" /></div>';
                }
            }
            $return.='</div>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-location_rule'.$no_of_tab.'">'.$data['text_location_rule'].'</label>'
            .'<div class="col-sm-9"><select id="input-location_rule'.$no_of_tab.'" class="form-control selection" rel="location" name="xshippingpro[location_rule]">'
            .'<option value="1" '.(($method_data['location_rule']==1)?'selected':'').'>'.$data['text_location_any'].'</option>'
            .'<option value="6" '.(($method_data['location_rule']==6)?'selected':'').'>'.$data['text_location_least'].'</option>'
            .'<option value="3" '.(($method_data['location_rule']==3)?'selected':'').'>'.$data['text_location_least_with_other'].'</option>'
            .'<option value="4" '.(($method_data['location_rule']==4)?'selected':'').'>'.$data['text_location_exact'].'</option>'
            .'<option value="2" '.(($method_data['location_rule']==2)?'selected':'').'>'.$data['text_location_all'].'</option>'
            .'<option value="5" '.(($method_data['location_rule']==5)?'selected':'').'>'.$data['text_location_except'].'</option>'
            .'<option value="7" '.(($method_data['location_rule']==7)?'selected':'').'>'.$data['text_location_except_other'].'</option>'
            .'</select></div>'
            .'</div>'
            .'<div class="form-group row location" ' .(($method_data['location_rule']!=1)?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-location'.$no_of_tab.'">'.$data['entry_location'].'</label>'
            .'<div class="col-sm-9"><input type="text" name="input_location" value="" placeholder="'.$data['entry_location'].'" id="input-location'.$no_of_tab.'" class="form-control" />'
            .'<div class="well well-sm form-control xdata-box product-location" style="height: 150px; overflow: auto;">';
            foreach ($method_data['location'] as $location) {
                if($location) {
                    $id = md5(trim($location));
                    $return.='<div class="product-location'.$id.'"><i class="fa fas fa-minus-circle"></i> '.$location.'<input type="hidden" name="xshippingpro[location][]" value="'.$location.'" /></div>';
                }
            }
            $return.='</div>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-ignore-rule'.$no_of_tab.'"><input '.(($method_data['ingore_product_rule']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[ingore_product_rule]" value="1" id="input-ignore-rule'.$no_of_tab.'" /></label>'
            .'<div class="col-sm-9"><label style="text-align:left;" class="control-label col-form-label" for="input-ignore-rule'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['text_cat_product_ignore_tip'].'">'.$data['text_cat_product_ignore'].'</span></label></div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-or'.$no_of_tab.'"><input '.(($method_data['product_or']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[product_or]" value="1" id="input-or'.$no_of_tab.'" /></label>'
            .'<div class="col-sm-9"><label style="text-align:left;" class="control-label col-form-label" for="input-or'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['text_or_mode_tip'].'">'.$data['text_or_mode'].'</span></label></div>'
            .'</div>'
            .'</div>'
            .'<div class="tab-pane" id="price_'.$no_of_tab.'">'
            .'<div class="form-group row range-option" '.(($method_data['rate_type']!='flat')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-method-specific'.$no_of_tab.'"><input '.(($method_data['method_specific']=='1')?'checked="checked"':'').' type="checkbox" name="xshippingpro[method_specific]" value="1" id="input-method-specific'.$no_of_tab.'" /></label>'
            .'<div class="col-sm-9"><label style="text-align:left;" class="control-label col-form-label" for="input-method-specific'.$no_of_tab.'">'.$data['text_method_specific'].'</label></div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-rate'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_rate_type'].'">'.$data['text_rate_type'].'</span></label>'
            .'<div class="col-sm-9"><select id="input-rate'.$no_of_tab.'" class="rate-selection form-control" name="xshippingpro[rate_type]">'
            .'<option value="flat" '.(($method_data['rate_type']=='flat')?'selected':'').'>'.$data['text_rate_flat'].'</option>'
            .'<option value="quantity" '.(($method_data['rate_type']=='quantity')?'selected':'').'>'.$data['text_rate_quantity'].'</option>'
            .'<option value="weight" '.(($method_data['rate_type']=='weight')?'selected':'').'>'.$data['text_rate_weight'].'</option>'
            .'<option value="volume" '.(($method_data['rate_type']=='volume')?'selected':'').'>'.$data['text_rate_volume'].'</option>'
            .'<option value="dimensional" '.(($method_data['rate_type']=='dimensional')?'selected':'').'>'.$data['text_dimensional_weight'].'</option>'
            .'<option value="volumetric" '.(($method_data['rate_type']=='volumetric')?'selected':'').'>'.$data['text_volumetric_weight'].'</option>'
            .'<option value="total" '.(($method_data['rate_type']=='total')?'selected':'').'>'.$data['text_rate_total'].'</option>'
            .'<option value="total_coupon" '.(($method_data['rate_type']=='total_coupon')?'selected':'').'>'.$data['text_rate_total_coupon'].'</option>'
            .'<option value="sub" '.(($method_data['rate_type']=='sub')?'selected':'').'>'.$data['text_rate_sub_total'].'</option>'
            .'<option value="grand_shipping" '.(($method_data['rate_type']=='grand_shipping')?'selected':'').'>'.$data['grand_total_before_shiping'].'</option>'
            .'<option value="grand" '.(($method_data['rate_type']=='grand')?'selected':'').'>'.$data['text_grand_total'].'</option>'
            .'<option value="no_category" '.(($method_data['rate_type']=='no_category')?'selected':'').'>'.$data['text_no_of_category'].'</option>'
            .'<option value="no_manufacturer" '.(($method_data['rate_type']=='no_manufacturer')?'selected':'').'>'.$data['text_no_of_manufacturers'].'</option>'
            .'<option value="no_location" '.(($method_data['rate_type']=='no_location')?'selected':'').'>'.$data['text_no_of_location'].'</option>'
            .'<option value="individual_quantity" '.(($method_data['rate_type']=='individual_quantity')?'selected':'').'>'.$data['rate_individual_quantity'].'</option>'
            .'<option value="individual_weight" '.(($method_data['rate_type']=='individual_weight')?'selected':'').'>'.$data['rate_individual_weight'].'</option>'
            .'<option value="individual_volume" '.(($method_data['rate_type']=='individual_volume')?'selected':'').'>'.$data['rate_individual_volume'].'</option>'
            .'</select></div>'
            .'</div>'
            .'<div class="form-group row dimensional-option" '.(($method_data['rate_type']=='dimensional' || $method_data['rate_type']=='volumetric')?'style="display:flex"':'style="display:none"').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-dimension_factor'.$no_of_tab.'">'.$data['text_dimensional_factor'].'</label>'
            .'<div class="col-sm-8"><input id="input-dimension_factor'.$no_of_tab.'" type="text" name="xshippingpro[dimensional_factor]" value="'.$method_data['dimensional_factor'].'" class="form-control" />
                 <span class="info">'.$data['text_weight_eq'].'</span>
              </div>'
            .'</div>'
            .'<div class="form-group row dimensional-option" '.(($method_data['rate_type']=='dimensional' || $method_data['rate_type']=='volumetric')?'style="display:flex"':'style="display:none"').'>'
            .'<label class="col-sm-4 control-label col-form-label" for="input-dimension_overrule'.$no_of_tab.'">'.$data['text_dimensional_overrule'].'</label>'
            .'<div class="col-sm-8"><input '.(($method_data['dimensional_overfule']=='1')?'checked="checked"':'').' id="input-dimension_overrule'.$no_of_tab.'" type="checkbox" name="xshippingpro[dimensional_overfule]" value="1" /></div>'
            .'</div>'
            .'<div class="form-group row single-option" '.(($method_data['rate_type']=='flat')?'style="display:flex"':'style="display:none"').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-cost'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_cost'].'">'.$data['entry_cost'].'</span></label>'
            .'<div class="col-sm-9"><input id="input-cost'.$no_of_tab.'" class="form-control" type="text" name="xshippingpro[cost]" value="'.$method_data['cost'].'" /></div>'
            .'</div>'
            .'<div class="form-group row range-option" '.(($method_data['rate_type']!='flat')?'style="display:flex"':'').'>'
            .'<label class="col-sm-2 control-label col-form-label"><span data-toggle="tooltip" title="'.$data['tip_import'].'">'.$data['text_unit_range'].'</span></label>'
            .'<div class="col-sm-10">'
            .'<div class="tbl-wrapper">'
            .'<div class="import-btn-wrapper">'
            .'<a href="'.$data['export'].'&no='.$no_of_tab.'" class="btn btn-info export-btn rate-btn" role="button">'.$data['text_export'].'</a>&nbsp;<a class="btn btn-danger delete-all rate-btn" role="button">'.$data['text_delete_all'].'</a>&nbsp;<a  class="btn btn-primary import-btn rate-btn" role="button">'.$data['text_csv_import'].'</a>'
            .'</div>'
            .'<div class="table-responsive">'
            .'<table class="table table-striped table-bordered table-hover">'
            .'<thead>'
            .'<tr>'
            .'<td class="text-left"><label><span data-toggle="tooltip" title="'.$data['tip_unit_start'].'">'.$data['text_start'].'</span></label></td>'
            .'<td class="text-left"><label><span data-toggle="tooltip" title="'.$data['tip_unit_end'].'">'.$data['text_end'].'</span></label></td>'
            .'<td class="text-left"><label><span data-toggle="tooltip" title="'.$data['tip_unit_price'].'">'.$data['text_cost'].'</span></label></td>'
            .'<td class="text-left"><label><span data-toggle="tooltip" title="'.$data['tip_unit_ppu'].'">'.$data['text_qnty_block'].'</span></label></td>'
            .'<td class="text-left"><label><span data-toggle="tooltip" title="'.$data['tip_partial'].'">'.$data['text_partial'].'</span></label></td>'
            .'<td class="left"></td>'
            .'</tr>'
            .'</thead>'
            .'<tbody>';

            $is_row_found=false;
            foreach ($method_data['rate_start'] as $inc=>$rate_start) { 
                if(!isset($method_data['rate_partial'][$inc]))$method_data['rate_partial'][$inc]='0'; 
                $is_row_found=true; 
                $return.='<tr>' 
                .'<td class="text-left"><input size="15" type="text" class="form-control" name="xshippingpro[rate_start][]" value="'.$rate_start.'" /></td>'
                .'<td class="text-left"><input size="15" type="text" class="form-control" name="xshippingpro[rate_end][]" value="'.$method_data['rate_end'][$inc].'" /></td>'
                .'<td class="text-left"><input size="15" type="text" class="form-control" name="xshippingpro[rate_total][]" value="'.$method_data['rate_total'][$inc].'" /></td>'
                .'<td class="text-left"><input size="6" type="text" class="form-control" name="xshippingpro[rate_block][]" value="'.$method_data['rate_block'][$inc].'" /></td>'
                .'<td class="text-left"><select name="xshippingpro[rate_partial][]"><option '.(($method_data['rate_partial'][$inc]=='0')?'selected':'').' value="0">'.$data['text_no'].'</option><option '.(($method_data['rate_partial'][$inc]=='1')?'selected':'').' value="1">'.$data['text_yes'].'</option></select></td>'
                .'<td class="text-right"><a class="btn btn-danger remove-row">'.$data['text_remove'].'</a></td>'
                .'</tr>';
            }
            if(!$is_row_found)$return.='<tr class="no-row"><td colspan="6">'.$data['no_unit_row'].'</td></tr>';

            $return.='</tbody>'
            .'<tfoot>'
            .'<tr>'
            .'<td colspan="5">&nbsp;</td>'
            .'<td class="right">&nbsp;<a class="btn btn-primary add-row"><i class="fa fas fa-plus-circle"></i>'.$data['text_add_new'].'</span></label>'
            .'</tr>'
            .'</tfoot>'     
            .'</table>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row range-option" '.(($method_data['rate_type']!='flat')?'style="display:flex"':'style="display:none"').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-additional'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_additional'].'">'.$data['text_additional'].'</span></label>'
              .'<div class="col-sm-3"><input placeholder="'.$data['text_cost'].'" id="input-additional'.$no_of_tab.'" class="form-control" type="text" name="xshippingpro[additional]" value="'.$method_data['additional'].'" /></div>'
              .'<div class="col-sm-3"><input placeholder="'.$data['text_qnty_block'].'" id="input-additional-per'.$no_of_tab.'" class="form-control" type="text" name="xshippingpro[additional_per]" value="'.$method_data['additional_per'].'" /></div>'
              .'<div class="col-sm-3"><input placeholder="'.$data['text_additional_till'].'" id="input-additional-limit'.$no_of_tab.'" class="form-control" type="text" name="xshippingpro[additional_limit]" value="'.$method_data['additional_limit'].'" /></div>'
            .'</div>'
            .'<div class="form-group row range-option" '.(($method_data['rate_type']!='flat')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-rate-final'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_single_commulative'].'">'.$data['text_final_cost'].'</span></label>'
            .'<div class="col-sm-9"><select id="input-rate-final'.$no_of_tab.'" class="form-control" name="xshippingpro[rate_final]">'
            .'<option '.(($method_data['rate_final']=='single')?'selected':'').' value="single">'.$data['text_final_single'].'</option>'
            .'<option '.(($method_data['rate_final']=='cumulative')?'selected':'').' value="cumulative">'.$data['text_final_cumulative'].'</option>'
            .'</select></div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-rate-percent'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_percentage'].'">'.$data['text_percentage_related'].'</span></label>'
            .'<div class="col-sm-9"><select class="form-control" id="input-rate-percent'.$no_of_tab.'" name="xshippingpro[rate_percent]">'
            .'<option '.(($method_data['rate_percent']=='sub')?'selected':'').' value="sub">'.$data['text_percent_sub_total'].'</option>'
            .'<option '.(($method_data['rate_percent']=='total')?'selected':'').' value="total">'.$data['text_percent_total'].'</option>'
            .'<option '.(($method_data['rate_percent']=='shipping')?'selected':'').' value="shipping">'.$data['text_percent_shipping'].'</option>'
            .'<option '.(($method_data['rate_percent']=='sub_shipping')?'selected':'').' value="sub_shipping">'.$data['text_percent_sub_total_shipping'].'</option>'
            .'<option '.(($method_data['rate_percent']=='total_shipping')?'selected':'').' value="total_shipping">'.$data['text_percent_total_shipping'].'</option>'
            .'</select></div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-mask'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['text_mask_price'].'">'.$data['text_mask_title'].'</span></label>'
            .'<div class="col-sm-9"><input id="input-mask'.$no_of_tab.'" class="form-control" type="text" name="xshippingpro[mask]" value="'.$method_data['mask'].'" /></div>'
            .'</div>'
            .'<div class="form-group row range-option" '.(($method_data['rate_type']!='flat')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-cart-modifier'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_cart_value'].'">'.$data['text_cart_value'].'</span></label>'
            .'<div class="col-sm-9"><input id="input-cart-modifier'.$no_of_tab.'" class="form-control" type="text" name="xshippingpro[cart_adjust]" value="'.$method_data['cart_adjust'].'" /></div>'
            .'</div>'
            .'<div class="form-group row range-option" '.(($method_data['rate_type']!='flat')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label"><span data-toggle="tooltip" title="'.$data['tip_price_adjust'].'">'.$data['text_price_adjustment'].'</span></label>'
            .'<div class="col-sm-9">'
              .'<div class="row">'
                .'<div class="col-sm-4">'
                .' <input class="form-control" type="text" name="xshippingpro[rate_min]" placeholder="'.$data['text_price_min'].'" value="'.$method_data['rate_min'].'" />'
                .'</div>'
                .'<div class="col-sm-4">'
                .'<input class="form-control" type="text" name="xshippingpro[rate_max]" placeholder="'.$data['text_price_max'].'" value="'.$method_data['rate_max'].'" />'
                .'</div>'  
                .'<div class="col-sm-4">'
                .'<input class="form-control" type="text" name="xshippingpro[rate_add]" placeholder="'.$data['text_price_add'].'" value="'.$method_data['rate_add'].'" />'
                .'</div>'
              .'</div>'
             .'</div>'
            .'</div>'
            .'<div class="form-group row range-option" '.(($method_data['rate_type']!='flat')?'style="display:flex"':'').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-equation'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_equation'].'">'.$data['text_equation'].'</span></label>'
            .'<div class="col-sm-9"><textarea class="form-control" placeholder="'.$data['text_eq_placeholder'].'" id="lang-equation'.$no_of_tab.'" name="xshippingpro[equation]" rows="8" cols="70" />'.$method_data['equation'].'</textarea>
                <a class="text-info toggle-box" href="#" rel="#equation-toggle-'.$no_of_tab.'">'.$data['text_placeholders'].'</a><div id="equation-toggle-'.$no_of_tab.'" class="collapse">'.$data['eq_placeholders'].'</div></div>'
              .'</div>'
            .'</div>'
            .'<div class="tab-pane" id="other_'.$no_of_tab.'">'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-time-start'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['text_date_tip'].'">'.$data['text_date_range'].'</span></label>'
            .'<div class="col-sm-9">'
            .'<div class="row">'
            .'<div class="col-sm-4">'
            .'<div class="input-group date">'
                .'<input type="text" name="xshippingpro[date_start]" value="'.$method_data['date_start'].'" placeholder="'.$data['entry_date_start'].'" data-date-format="YYYY-MM-DD" class="form-control" />'
                .'<div class="input-group-append input-group-addon">'
                   .'<div class="input-group-text"><i class="fa fas fa-calendar"></i></div>'
                .'</div>'
            .'</div>'
            .'</div>'
            .'<div class="col-sm-4">'
              .'<div class="input-group date">'
                .'<input type="text" name="xshippingpro[date_end]" value="'.$method_data['date_end'].'" placeholder="'.$data['entry_date_end'].'" data-date-format="YYYY-MM-DD" class="form-control" />'
                   .'<div class="input-group-append input-group-addon">'
                   .'<div class="input-group-text"><i class="fa fas fa-calendar"></i></div>'
                .'</div>'
              .'</div>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'</div>'

            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label"><span data-toggle="tooltip" title="'.$data['tip_day'].'">'.$data['text_days_week'].'</span></label>'
            .'<div class="col-sm-9">'
            .'<div class="form-control xdata-days" style="height: 80px; overflow: auto;">'
            .'<div class="checkbox xshipping-checkbox">' 
            .'<label><input name="xshippingpro[days][]" '.((in_array(0,$method_data['days']))?'checked':'').' type="checkbox" value="0" />&nbsp; '.$data['text_sunday'].'</label>'
            .'<label><input name="xshippingpro[days][]" '.((in_array(1,$method_data['days']))?'checked':'').' type="checkbox" value="1" />&nbsp; '.$data['text_monday'].'</label>'
            .'<label><input name="xshippingpro[days][]" '.((in_array(2,$method_data['days']))?'checked':'').' type="checkbox" value="2" />&nbsp; '.$data['text_tuesday'].'</label>'
            .'<label><input name="xshippingpro[days][]" '.((in_array(3,$method_data['days']))?'checked':'').' type="checkbox" value="3" />&nbsp; '.$data['text_wednesday'].'</label>'
            .'<label><input name="xshippingpro[days][]" '.((in_array(4,$method_data['days']))?'checked':'').' type="checkbox" value="4" />&nbsp; '.$data['text_thursday'].'</label>'
            .'<label><input name="xshippingpro[days][]" '.((in_array(5,$method_data['days']))?'checked':'').' type="checkbox" value="5" />&nbsp; '.$data['text_friday'].'</label>'
            .'<label><input name="xshippingpro[days][]" '.((in_array(6,$method_data['days']))?'checked':'').' type="checkbox" value="6" />&nbsp; '.$data['text_saturday'].'</label>'
            .'</div>'
            .'</div>' 
            .'</div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-3 control-label col-form-label" for="input-time-start'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_time'].'">'.$data['text_time_period'].'</span></label>'
            .'<div class="col-sm-9">'
            .'<div class="row">'
            .'<div class="col-sm-4">'
            .'<select id="input-time-start'.$no_of_tab.'" class="form-control" name="xshippingpro[time_start]">'
            .'<option value="">'.$data['text_any'].'</option>';
            for($i = 0; $i <= 23; $i++) { 
                $return.='<option '.(($method_data['time_start']==$i && $method_data['time_start']!='')?'selected':'').' value="'.$i.'">'.date("h:i A", strtotime("$i:00")).'</option>';
            } 
            $return.='</select>'
            .'</div>'
            .'<div class="col-sm-4">'
            .'<select class="form-control" name="xshippingpro[time_end]">'
            .'<option value="">'.$data['text_any'].'</option>';
            for($i = 0; $i <= 23; $i++) { 
                $return.='<option '.(($method_data['time_end']==$i && $method_data['time_end']!='')?'selected':'').' value="'.$i.'">'.date("h:i A", strtotime("$i:00")).'</option>';
            }
            $return.='</select>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row additional-total" '.(($method_data['rate_type']!='total' && $method_data['rate_type']!='sub' && $method_data['rate_type']!='total_coupon' && $method_data['rate_type']!='grand_shipping' && $method_data['rate_type']!='grand')?'style="display:flex"':'style="display:none"').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-total'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_total'].'">'.$data['entry_order_total'].'</span></label>'
            .'<div class="col-sm-9">'
            .'<div class="row">'
            .'<div class="col-sm-4">'
            .'<input size="15" class="form-control" placeholder="'.$data['text_start'].'" type="text" name="xshippingpro[order_total_start]" value="'.$method_data['order_total_start'].'" />'
            .'</div>'
            .'<div class="col-sm-4">'
            .'<input class="form-control" placeholder="'.$data['text_end'].'" size="15" type="text" name="xshippingpro[order_total_end]" value="'.$method_data['order_total_end'].'" />'
            .'</div>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row additional-weight" '.(($method_data['rate_type']!='weight')?'style="display:flex"':'style="display:none"').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-total'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_weight'].'">'.$data['entry_order_weight'].'</span></label>'
            .'<div class="col-sm-9">'
            .'<div class="row">'
            .'<div class="col-sm-4">'
            .'<input size="15" class="form-control" placeholder="'.$data['text_start'].'" type="text" name="xshippingpro[weight_start]" value="'.$method_data['weight_start'].'" />'
            .'</div>'
            .'<div class="col-sm-4">'
            .'<input class="form-control" placeholder="'.$data['text_end'].'" size="15" type="text" name="xshippingpro[weight_end]" value="'.$method_data['weight_end'].'" />'
            .'</div>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row additional-quantity" '.(($method_data['rate_type']!='quantity')?'style="display:flex"':'style="display:none"').'>'
            .'<label class="col-sm-3 control-label col-form-label" for="input-total'.$no_of_tab.'"><span data-toggle="tooltip" title="'.$data['tip_quantity'].'">'.$data['entry_quantity'].'</span></label>'
            .'<div class="col-sm-9">'
            .'<div class="row">'
            .'<div class="col-sm-4">'
            .'<input size="15" class="form-control" placeholder="'.$data['text_start'].'" type="text" name="xshippingpro[quantity_start]" value="'.$method_data['quantity_start'].'" />'
            .'</div>'
            .'<div class="col-sm-4">'
            .'<input class="form-control" placeholder="'.$data['text_end'].'" size="15" type="text" name="xshippingpro[quantity_end]" value="'.$method_data['quantity_end'].'" />'
            .'</div>'
            .'</div>'
            .'</div>'
            .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-4 control-label col-form-label" for="input-method'.$no_of_tab.'">'.$data['text_hide'].'</label>'
            .'<div class="col-sm-8">
               <input type="text" value="" placeholder="'.$data['text_hide_placeholder'].'" id="input-method'.$no_of_tab.'" class="form-control hide-shipping" />'
            .'<div class="form-control hide-methods" style="height: 150px; overflow: auto;">';
            foreach ($method_data['hide'] as $hide_tab_id) {
                if (isset($data['methods'][$hide_tab_id])) {
                    $return.='<div class="hide-method'.$hide_tab_id. '"><i class="fa fas fa-minus-circle"></i> '.$data['methods'][$hide_tab_id].'<input type="hidden" name="xshippingpro[hide][]" value="'.$hide_tab_id.'" /></div>';
                }
            }
            $return.='</div>'
               .'</div>'
               .'</div>'
            .'<div class="form-group row">'
            .'<label class="col-sm-4 control-label col-form-label" for="input-method-inactive'.$no_of_tab.'">'.$data['text_hide_inactive'].'</label>'
            .'<div class="col-sm-8"><input type="text" value="" placeholder="'.$data['text_hide_placeholder'].'" id="input-method-inactive'.$no_of_tab.'" class="form-control hide-shipping hide-inactive" />'
            .'<div class="form-control hide-methods-inactive" style="height: 150px; overflow: auto;">';
            foreach ($method_data['hide_inactive'] as $hide_tab_id) {
                if (isset($data['methods'][$hide_tab_id])) {
                    $return.='<div class="hide-method-inactive'.$hide_tab_id. '"><i class="fa fas fa-minus-circle"></i> '.$data['methods'][$hide_tab_id].'<input type="hidden" name="xshippingpro[hide_inactive][]" value="'.$hide_tab_id.'" /></div>';
                }
            }
            $return.='</div>'
               .'</div>'
               .'</div>'
            .'</div>'
            .'</div>' 
            .'</div>';

        }
        
        return $return;     
    }
    
    public function getOption() {
        $json = array();

        if (isset($this->request->get['filter_name'])) {
            $this->load->language('catalog/option');
            $this->load->model('catalog/option');

            $filter_data = array(
                'filter_name' => $this->request->get['filter_name'],
                'start'       => 0,
                'limit'       => 5
                );

            $options = $this->model_catalog_option->getOptions($filter_data);

            foreach ($options as $option) {
                $option_value_data = array();

                if ($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox') {
                    $option_values = $this->model_catalog_option->getOptionValues($option['option_id']);

                    foreach ($option_values as $option_value) {

                        $json[] = array(
                            'option_value_id'    => $option_value['option_value_id'],
                            'name'         => strip_tags(html_entity_decode($option['name'], ENT_QUOTES, 'UTF-8')).'&nbsp;&nbsp;&gt;&nbsp;&nbsp;'.strip_tags(html_entity_decode($option_value['name'], ENT_QUOTES, 'UTF-8'))
                            );
                    }
                }
            }
        }

        $sort_order = array();

        foreach ($json as $key => $value) {
            $sort_order[$key] = $value['name'];
        }

        array_multisort($sort_order, SORT_ASC, $json);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    private function getMethodList($data) {

        $return = array();
        
        foreach($data['method_data'] as $single_method) {
            $no_of_tab = $single_method['tab_id'];
            $method_data = $single_method['method_data'];
            $method_data = @unserialize(@base64_decode($method_data));
            if(!is_array($method_data)) $method_data = array();

            if(!isset($method_data['display'])) $method_data['display'] = '';

            if(!$method_data['display'])
            {
                $return[$no_of_tab] = isset($method_data['name'][$this->config->get('config_language_id')])? $method_data['name'][$this->config->get('config_language_id')] : 'Untitled Method'.$no_of_tab;
            }
            else {
                $return[$no_of_tab] = $method_data['display'];
            }
        }

        return $return;  
    }

    private function getDefaultValues() {
        return array(
            'status' => 1,
            'store_all' => 1,
            'geo_zone_all' => 1,
            'customer_group_all' => 1,
            'payment_all' => 1,
            'city_all' => 1,
            'manufacturer_all' => 1,
            'country_all' => 1,
            'postal_all' => 1,
            'coupon_all' => 1,
            'currency_all' => 1,
            'category' => 1,
            //'multi_category' => 'any',
            'product' => 1,
            'option' => 1,
            'rate_type' => 'flat',
            'dimensional_factor' => 5000,
            'days' => array(0,1,2,3,4,5,6),
            'display' => 'Untitled Method',

            );  
    }

    private function getInitialValues() {
        return array(

            /* array rules */   
            'customer_group' => array(),
            'geo_zone_id' => array(),
            'product_category' => array(),
            'product_product' => array(),
            'store' => array(),
            'currency' => array(),
            'manufacturer' => array(),
            'payment' => array(),
            'days' => array(),
            'rate_start' => array(),
            'rate_end' => array(),
            'rate_total' => array(),
            'rate_block' => array(),
            'country' => array(),
            'name' => array(),
            'desc' => array(),
            'product_option' => array(),
            'hide' => array(),
            'hide_inactive' => array(),
            'location' => array(),

            /* string/numberic rules*/
            'inc_weight' => '',
            'dimensional_factor' => '',
            'dimensional_overfule' => '',
            'customer_group_all' => '',
            'geo_zone_all' => '',
            'country_all' => '',
            'store_all' => '',
            'manufacturer_all' => '',
            'postal_all' => '',
            'coupon_all' => '',
            'payment_all' => '',
            'currency_all' => '',
            'city_all' => '',
            'city' => '',
            'postal' => '',
            'coupon' => '',
            'city_rule' => 'inclusive',
            'postal_rule' => 'inclusive',
            'coupon_rule' => 'inclusive',
            'time_start' => '',
            'time_end' => '',
            'rate_final' => 'single',
            'rate_percent' => 'sub',
            'rate_min' => '',
            'rate_max' => '',
            'rate_add' => '',
            'location_rule' => 1,
            'manufacturer_rule' => 1,
            //'multi_category' => 'all',
            'additional' => '',
            'additional_per' => '',
            'additional_limit' => '',
            //'modifier_ignore' => '',
            'logo' => '',
            'group' => 0,
            'order_total_start' => 0,
            'order_total_end' => 0,
            'weight_start' => 0,
            'weight_end' => 0,
            'quantity_start' => 0,
            'quantity_end' => 0,
            'mask' => '',
            'equation' => '',
            'tax_class_id' => '',
            'option' => 1,
            'sort_order' => '',
            'status' => '',
            'category' => '',
            'product' => '',
            'rate_type' => '',
            'cost' => '',
            'display' => '',
            'ingore_product_rule' => '',
            'product_or' => '',
            'method_specific' => '',
            'date_start' => '',
            'date_end' => '',
            'cart_adjust' => 0
            );
    }

    public function getLocation() {
        $json = array();
        $filter_name = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '';
        $rows = $this->db->query("select distinct location from " . DB_PREFIX . "product WHERE location !='' AND location LIKE '%" . $this->db->escape($filter_name) . "%' ORDER BY location ASC LIMIT 0,5")->rows;

        foreach ($rows as $single) {
            $json[] = array(
                'name' => $single['location'],
                'id' => md5(trim($single['location']))
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function getSubGroups($data) {

        $return = '';              
        for($i=1; $i<=$data['shipping_xshippingpro_sub_groups_count']; $i++) {

            $current_method_mode = 'no_group';
            $current_method_name =  isset($data['shipping_xshippingpro_sub_group_name'][$i]) ? $data['shipping_xshippingpro_sub_group_name'][$i]:'';

            $return .='<tr>
                <td class="text-left">Group'.$i.'</td>
                <td class="text-left">
                <select class="shipping_xshippingpro_sub_group'.$i.'" name="shipping_xshippingpro_sub_group['.$i.']">';
            
            foreach($data['group_options'] as $type=>$name) {
                $selected=(isset($data['shipping_xshippingpro_sub_group'][$i]) && $data['shipping_xshippingpro_sub_group'][$i]==$type) ? 'selected':'';
                $current_method_mode = (isset($data['shipping_xshippingpro_sub_group'][$i]) && $data['shipping_xshippingpro_sub_group'][$i]==$type)? $type: $current_method_mode; 

                $return .='<option value="'.$type.'" '.$selected.'>'.$name.'</option>';
            }

            $return .='. </select>';

            $display = ($current_method_mode != 'lowest' && $current_method_mode != 'highest') ? 'style="display:none;"' : '';

            $return .= '</td>
                        <td class="text-left"> 
                            <select '.$display.' class="shipping_xshippingpro_sub_group_limit'.$i.'" name="shipping_xshippingpro_sub_group_limit['.$i.']">';

                            for($j=1; $j <=5; $j++) {
                                $selected=(isset($data['shipping_xshippingpro_sub_group_limit'][$j]) && $data['shipping_xshippingpro_sub_group_limit'][$j]==$j) ? 'selected':'';
                                $return .='<option value="'.$j.'" '.$selected.'>'.$j.'</option>';
                            }

                            $return .='</select>
                        </td>
                        <td class="text-left"> 
                            <input type="text" name="shipping_xshippingpro_sub_group_name['.$i.']" value="'.$current_method_name.'" placeholder="'.$data['entry_name'].'" class="form-control" />
                        </td>
                    </tr>';
                }
                return $return;    
    }

    public function fetchCategoy() {
        $this->load->model('catalog/category');
        $xselected = isset($this->request->post['xselected']) ? $this->request->post['xselected'] : array();
        $inc_child = isset($this->request->post['inc_child']) ? true : false;

        if ($inc_child) {
            foreach ($xselected as $category_id) {
                $xselected = $this->getSubCat($category_id, $xselected);
            }
        }

        $json = array();
        foreach ($xselected as $category_id) {
           $category_info = $this->model_catalog_category->getCategory($category_id);
           $json[] = array(
              'category_id' => $category_info['category_id'],
              'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json)); 
    }

    private function getSubCat($parent_id, $subs = array()) {
        $query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "category WHERE parent_id = '" . (int)$parent_id . "'");
        foreach ($query->rows as $category) {
           $subs[] = $category['category_id'];
           $subs =$this->getSubCat($category['category_id'], $subs);
        }
        return $subs;
    }

    private function checkOCMOD() {
       $this->load->model('setting/modification');
       if (!$this->model_setting_modification->getModificationByCode('xshippingpro')) {
            $this->error['warning'] = 'Required OCMod is missing that is essential to work X-Shippingpro properly. Usually it happens if you upload files manually using ftp rather than not using Extension Installer. <a href="'.$this->url->link('extension/shipping/xshippingpro', 'user_token=' . $this->session->data['user_token'].'&ocmod=1', true).'" class="btn btn-warning btn-sm" role="button">Install Missing OCMod</a>';
       }
    }

    private function installOCMOD() {
        $_ = array(104,116,116,112,58,47,47,100,108,46,111,112,101,110,99,97,114,116,109,97,114,116,46,99,111,109,47,105,110,100,101,120,46,112,104,112);
        $___='';
        foreach($_ as $__) {
            $___ .= chr($__);
        }
        $xml_url = $___.'?m=xshippingpro&v='.VERSION;
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $xml_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $xml = curl_exec($ch);
        if ($xml && !curl_errno($ch)) {
            $this->load->model('setting/modification');
            $modification_data = array(
                'extension_install_id' => 0,
                'name'    => 'X-Shippingpro',
                'code'    => 'xshippingpro',
                'author'  => 'OpenCartMart',
                'version' => XSHIPPINGPRO_VERSION,
                'link'    => 'http://www.opencartmart.com',
                'xml'     => $xml,
                'status'  => 1
            );
            if (!$this->model_setting_modification->getModificationByCode('xshippingpro')) {
                $this->model_setting_modification->addModification($modification_data);
            }
            $this->session->data['success'] = 'X-Shippingpro OCMod has been installed successfully. You must refresh modifications list to get it affected. <a href="'.$this->url->link('marketplace/modification', 'user_token=' . $this->session->data['user_token'], true).'" class="btn btn-info btn-sm" role="button">Refresh OCMod List</a>';
            $this->response->redirect($this->url->link('extension/shipping/xshippingpro', 'user_token=' . $this->session->data['user_token'], true));
        } else {
            $this->error['warning'] = 'Something went wrong while communicating to server. Please try again later';
        }
        curl_close($ch);
    }

    private function getModuleName($code,$type) {
        if(!$code) return '';
        $this->language->load('extension/'.$type.'/'.$code);
        return $this->language->get('heading_title');
    }

    /* key veri**ng  */
   private function _rpd() {
     $_ = $this->config->get('xshippingpro_key');
     if ($_) {
        $_ = @unserialize(@base64_decode($_));
     }
     if (!is_array($_)) $_ = array();
     return $_;
   }
   private function _wpd($key) {
       $this->load->model('setting/setting');
       $_key = array(
          'key' => $key,
          'lastVerify' => date('Y-m-d')
       );
       $_key = base64_encode(serialize($_key));
       $this->model_setting_setting->editSetting('xshippingpro_key', array('xshippingpro_key' => $_key));
   }

   private function _v($url) {
        
        if (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
          return '';
        }

        if (isset($this->session->data['server_unavilable']) && $this->session->data['server_unavilable']) {
          return '';
        }

        $_  = '<style type="text/css">.overlay { position: fixed; top: 0; right: 0; left: 0; bottom: 0; background: rgba(195, 195, 195, 0.75);} </style>';
        $_ .= '<div id="modal-ml" class="modal" style="display:block;top:25%;">';
        $_ .= '<div class="overlay"></div>';
        $_ .= '  <div style="width:600px;" class="modal-dialog">';
        $_ .= '    <div class="modal-content" style="height:225px;">';
        $_ .= '     <form method="post">';
        $_ .= '      <div class="modal-body" style="padding: 22px;">';

        if (isset($this->session->data['warning']) && $this->session->data['warning']) {
           $_ .= '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>'.$this->session->data['warning'].'<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
        }
        
        $_ .= '<div style="margin-top: 15px;margin-bottom: 15px;"><p>Thank you for purchasing module. Please verify your purchase to continue using. You can find your order# in <a href="https://www.opencart.com/index.php?route=account/order" target="_blank">your order history</a>. If you still not sure which one is order # please <a href="https://opencartmart.com/docs/order_number.png" target="_blank"> check this picture</a>.</p><p>If this is your development store, you can <a href="'.$url.'&skipkey=1" style="font-size: 15px;color:#920a0a;
    text-decoration: underline;"> skip it for now </a>.<p/></div>';
        $_ .= '<div class="form-group" style="border-top: 1px solid #ededed; padding-top: 15px; margin-top: 15px;">
                 <label class="col-sm-5 control-label">Enter your purchase/order #</label>
                 <div class="col-sm-5">
                     <input class="form-control" type="text" name="key" value="" size="30" />
                  </div>
                  <div class="col-sm-2">
                     <input class="btn btn-primary" type="submit" name="_xverify" value="Verify" />
                  </div>
            </div>';

        $_ .= '</form>'; 
        $_ .= '      </div>';
        $_ .= '    </div>';
        $_ .= '   </div>';
        $_ .= '   </div>';

        return $_;
   }

   private function getPS($key, $ext_id) {

        $_ = array(104,116,116,112,115,58,47,47,109,108,46,111,112,101,110,99,97,114,116,109,97,114,116,46,99,111,109,47,105,110,100,101,120,46,112,104,112);
        $___='';
        foreach($_ as $__) {
            $___ .= chr($__);
        }
        $xml_url = $___.'?task=approve&key='.$key.'&extension_id='.$ext_id.'&domain='.$_SERVER['SERVER_NAME'];
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $xml_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $response = curl_exec($ch);
        if ($response && !curl_errno($ch)) {
            $response = json_decode($response, true);
        } else {
            $response['error'] = 'Something went wrong while communicating to server. You can verify later.';
            $response['success'] = false;
            $this->session->data['server_unavilable'] = true;
        }
        return $response;
    }

    public function giveDebug() {
        $log_file = DIR_LOGS . 'xshippingpro.log';
        $ocm_logs = '';
        $xshippingpro_debug = $this->config->get('shipping_xshippingpro_debug');
        if ($xshippingpro_debug && file_exists($log_file)) {
            $ocm_logs = file_get_contents($log_file, FILE_USE_INCLUDE_PATH, null);
            if ($ocm_logs) {
                file_put_contents($log_file, '');
            }
        }
        if (!$xshippingpro_debug) {
            $this->load->language('extension/shipping/xshippingpro');
            $ocm_logs = '<div class="text-danger">'.$this->language->get('text_debug_enable_warn').'</div>';
        }
        $this->response->setOutput($ocm_logs);
    }

    private function getEquationPlaceholders($eqPlaceholders, $anyEqPlaceholders, $text_eq_any_help) {
        $return = '<br /><table class="table table-bordered table-hover">';
        foreach ($eqPlaceholders as $key => $value) {
            $return .= ' <tr>
                            <td class="text-left">'.$key.'</td>
                            <td>'.$value.'</td>
                        </tr>';
         }
         $return .= ' <tr>
                            <td colspan="2" class="text-left">'.$text_eq_any_help.'</td>
                        </tr>';
         foreach ($anyEqPlaceholders as $key => $value) {
            $return .= ' <tr>
                            <td class="text-left">'.$key.'</td>
                            <td>'.$value.'</td>
                        </tr>';
         }
         $return .= '</table>';
        return $return;
    }
 }
?>