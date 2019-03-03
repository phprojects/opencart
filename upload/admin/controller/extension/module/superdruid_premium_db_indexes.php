<?php
class ControllerExtensionModuleSuperDruidPremiumDBIndexes extends Controller {

public function install() {
	$this->load->model('extension/module/premium_db_indexes');
	$this->model_extension_module_premium_db_indexes->install();
}

public function uninstall() {
	$this->load->model('extension/module/premium_db_indexes');
	$this->model_extension_module_premium_db_indexes->uninstall();
}

	
	public function index() {
		$this->load->language('extension/module/superdruid_premium_db_indexes');
		$this->load->model('extension/module/premium_db_indexes');
		

		$this->document->setTitle($this->language->get('heading_title'));
		
		$data['heading_title'] = $this->language->get('heading_title');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/superdruid_premium_db_indexes', 'user_token=' . $this->session->data['user_token'], true),
		);


		
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['cancel'] = $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['error_warning'] = '';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/superdruid_premium_db_indexes', $data));
		
	}
	


}
?>
