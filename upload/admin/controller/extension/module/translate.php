<?php
/**
 * Database Language Editor
 * SQL-based Language Editor for Opencart
 * 
 * 
 **/
class ControllerExtensionModuleTranslate extends Controller {
	private $error = array();
	
	public function install() {

		$this->load->model('extension/translate');
		$this->model_extension_translate->checkTable();

		$this->load->model('setting/event');
		$this->model_setting_event->addEvent('udi_translate_catalog', 'catalog/language/*/after', 'extension/module/translate/translate', 1, -1);
		$this->model_setting_event->addEvent('udi_translate_admin', 'admin/language/*/after', 'extension/module/translate/translate', 1, -1);

		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('module_translate', array('module_translate_status'	=> 1));

	}

	public function uninstall() {

		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('udi_translate_catalog');
		$this->model_setting_event->deleteEventByCode('udi_translate_admin');

		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('module_translate');

	}

	public function translate(&$route, &$key) {

		$interface = basename(dirname(DIR_LANGUAGE));
		$directory = !empty($this->session->data['language']) ? $this->session->data['language'] : $this->config->get('config_language');
  		$results = $this->db->query("SELECT `key`, `value` FROM ".DB_PREFIX."translate
									WHERE `interface`='".$this->db->escape($interface)."'
									AND `directory`='".$this->db->escape($directory)."'
									AND `filename`='".$this->db->escape($route)."'");
		foreach($results->rows as $result) {
			if (!$key) {
				$this->language->set($result['key'], $result['value']);
			} else {
				$this->language->get($key)->set($result['key'], $result['value']);
			}
		}

	}

	public function index() {
		$this->load->language('extension/module/translate');
		 
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('extension/translate');
		
		//$this->model_extension_translate->checkTable();
		
    	$this->getList();
	}

	public function insert() {
		$this->load->language('extension/module/translate');

		$this->document->setTitle($this->language->get('heading_title').' - '.$this->language->get('text_insert'));
		
		$this->load->model('extension/translate');
		
		//$this->model_extension_translate->checkTable();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_translate->insert($this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->burl();
			
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->response->redirect($this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('extension/module/translate');

		$this->document->setTitle($this->language->get('heading_title').' - '.$this->language->get('text_edit'));
		
		$this->load->model('extension/translate');
		
		//$this->model_extension_translate->checkTable();

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_translate->update($this->request->get['translation_id'], $this->request->post['value']);
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->burl();
					
			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
					
			$this->response->redirect($this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('extension/module/translate');
		 
		$this->load->model('extension/translate');
		
		//$this->model_extension_translate->checkTable();
		
    	if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $translation_id) {
				$this->model_extension_translate->delete($translation_id);
			}
			
			$this->session->data['success'] = $this->language->get('text_delete_success');

			$url = $this->burl();

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
			
			$this->response->redirect($this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . $url, true));
    	}
    
    	$this->getList();
	}

	public function import() {
		$this->load->language('extension/module/translate');
		 
		$this->load->model('extension/translate');
		
		//$this->model_extension_translate->checkTable();
		
		if(isset($this->request->post['import']) && $this->validateImport()) {
			$this->model_extension_translate->resetOriginal();
			$count = 0;
			foreach($this->model_extension_translate->getInterfaces() as $interface) {
				foreach($this->model_extension_translate->getLanguages() as $language) {
					$count += $this->model_extension_translate->import($interface['name'], $language['directory'], $language['filename']);
					foreach(glob($interface['langdir'].$language['directory'].'/*', GLOB_ONLYDIR) as $dir) {
						foreach(glob($dir.'/*.php') as $file) {
							$filename = basename($dir).'/'.basename($file, '.php');
							$count += $this->model_extension_translate->import($interface['name'], $language['directory'], $filename);
						}
						foreach(glob($dir.'/*', GLOB_ONLYDIR) as $rdir) {
							foreach(glob($rdir.'/*.php') as $file) {
								$filename = basename($dir).'/'.basename($rdir).'/'.basename($file, '.php');
								$count += $this->model_extension_translate->import($interface['name'], $language['directory'], $filename);
							}
						}
					}
				}
			}
			$this->session->data['success'] = sprintf($this->language->get('text_import_success'), $count);
			$this->response->redirect($this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'], true));
		}
    	$this->getList();
	}

	private function getList() {
		if (isset($this->request->get['filter_interface']) && array_key_exists($this->request->get['filter_interface'], $this->model_extension_translate->getInterfaces())) {
			$filter_interface = $this->request->get['filter_interface'];
		} else {
			$filter_interface = null;
		}

		if (isset($this->request->get['filter_directory']) && array_key_exists($this->request->get['filter_directory'], $this->model_extension_translate->getLanguages())) {
			$filter_directory = $this->request->get['filter_directory'];
		} else {
			$filter_directory = null;
		}
		
		if (isset($this->request->get['filter_filename'])) {
			$filter_filename = $this->request->get['filter_filename'];
		} else {
			$filter_filename = null;
		}

		if (isset($this->request->get['filter_key'])) {
			$filter_key = $this->request->get['filter_key'];
		} else {
			$filter_key = null;
		}

		if (isset($this->request->get['filter_value'])) {
			$filter_value = $this->request->get['filter_value'];
		} else {
			$filter_value = null;
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = null;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'key'; 
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
						
		$url = $this->burl();

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_extension'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
			'separator' => ' :: '
		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . $url, true),
      		'separator' => ' :: '
   		);
		
		$data['translatemode'] = $this->url->link('extension/module/translate/translatemode', 'user_token=' . $this->session->data['user_token'], true);
		$data['import'] = $this->url->link('extension/module/translate/import', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['insert'] = $this->url->link('extension/module/translate/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/translate/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['translations'] = array();

		$fdata = array(
			'filter_interface'              => $filter_interface,
			'filter_directory'             => $filter_directory,
			'filter_filename' => $filter_filename,
			'filter_key' => $filter_key,
			'filter_value' => $filter_value,
			'filter_status' => $filter_status, 
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                    => $this->config->get('config_limit_admin')
		);
		
		$translations_total = $this->model_extension_translate->getTotalTranslations($fdata);
		$translations = $this->model_extension_translate->getTranslations($fdata);
 
    	foreach ($translations as $result) {
			$action = array();
		
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('extension/module/translate/edit', 'user_token=' . $this->session->data['user_token'] . '&translation_id=' . $result['translation_id'] . $url, true)
			);
			if($result['status']) $status = $this->language->get('text_exists');
			elseif($result['status'] === NULL) $status = $this->language->get('text_not_exists');
			else $status = $this->language->get('text_exists_and_differs');
			
			$data['translations'][] = array(
				'translation_id'    => $result['translation_id'],
				'interface'           => $result['interface'],
				'directory'          => isset($result['directory']) ? $result['directory'] : null,
				'filename' => $result['filename'],
				'filename_link'          => $this->url->link('extension/module/translate/translatemode', 'user_token=' . $this->session->data['user_token'] . '&filter_interface=' . $result['interface']. '&filter_filename=' . $result['filename'], true),
				'key'         => $result['key'],
				'value'         => htmlspecialchars($result['value']),
				'selected'       => isset($this->request->post['selected']) && in_array($result['translation_id'], $this->request->post['selected']),
				'action'         => $action,
				'status'		=> $status
			);
		}	
					
		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_all'] = $this->language->get('text_all');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_confirm'] = $this->language->get('text_confirm');

		$data['column_interface'] = $this->language->get('column_interface');
		$data['column_directory'] = $this->language->get('column_directory');
		$data['column_filename'] = $this->language->get('column_filename');
		$data['column_key'] = $this->language->get('column_key');
		$data['column_value'] = $this->language->get('column_value');
		$data['column_status'] = $this->language->get('column_status');
		$data['column_action'] = $this->language->get('column_action');		
		
		$data['button_translatemode'] = $this->language->get('button_translatemode');
		$data['button_import'] = $this->language->get('button_import');
		$data['button_insert'] = $this->language->get('button_insert');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_filter'] = $this->language->get('button_filter');
		$data['button_reset'] = $this->language->get('button_reset');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		$url = $this->burl();

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['sort_interface'] = $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . '&sort=interface' . $url, true);
		$data['sort_directory'] = $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . '&sort=directory' . $url, true);
		$data['sort_filename'] = $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . '&sort=filename' . $url, true);
		$data['sort_key'] = $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . '&sort=key' . $url, true);
		$data['sort_value'] = $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . '&sort=value' . $url, true);
		$data['sort_status'] = $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);
		
		$url = $this->burl();

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $translations_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
			
		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($translations_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($translations_total - $this->config->get('config_limit_admin'))) ? $translations_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $translations_total, ceil($translations_total / $this->config->get('config_limit_admin')));

		$data['filter_interface'] = $filter_interface;
		$data['filter_directory'] = $filter_directory;
		$data['filter_filename'] = $filter_filename;
		$data['filter_key'] = $filter_key;
		$data['filter_value'] = $filter_value;
		$data['filter_status'] = $filter_status;
		
		$data['interfaces'] = array_keys($this->model_extension_translate->getInterfaces());
		$data['directories'] = array_keys($this->model_extension_translate->getLanguages());
		$data['statuses'] = array(
			'new' => $this->language->get('text_not_exists'),
			'differs' => $this->language->get('text_exists_and_differs'),
			'equals' => $this->language->get('text_exists')
		);
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/translate_list', $data));
	}

	private function getForm() {
    	$data['heading_title'] = $this->language->get('heading_title');
 		if(isset($this->request->get['translation_id'])) {
 			$data['edit'] = true;
 			$data['heading_title'] .= ' - '.$this->language->get('text_edit');
 		}
 		else {
 			$data['edit'] = false;
 			$data['heading_title'] .= ' - '.$this->language->get('text_insert');
		}
 
    	$data['entry_interface'] = $this->language->get('entry_interface');
    	$data['entry_directory'] = $this->language->get('entry_directory');
    	$data['entry_filename'] = $this->language->get('entry_filename');
    	$data['entry_key'] = $this->language->get('entry_key');
    	$data['entry_value'] = $this->language->get('entry_value');
    	$data['text_select'] = $this->language->get('text_select');
 
		$data['button_save'] = $this->language->get('button_save');
    	$data['button_cancel'] = $this->language->get('button_cancel');

		$data['user_token'] = $this->session->data['user_token'];

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['interface'])) {
			$data['error_interface'] = $this->error['interface'];
		} else {
			$data['error_interface'] = '';
		}

 		if (isset($this->error['directory'])) {
			$data['error_directory'] = $this->error['directory'];
		} else {
			$data['error_directory'] = '';
		}
		
 		if (isset($this->error['filename'])) {
			$data['error_filename'] = $this->error['filename'];
		} else {
			$data['error_filename'] = '';
		}
		
 		if (isset($this->error['key'])) {
			$data['error_key'] = $this->error['key'];
		} else {
			$data['error_key'] = '';
		}
		
		$url = $this->burl();
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
						
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_extension'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
			'separator' => ' :: '
		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . $url, true),
      		'separator' => ' :: '
   		);

		if (!isset($this->request->get['translation_id'])) {
			$data['action'] = $this->url->link('extension/module/translate/insert', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/module/translate/edit', 'user_token=' . $this->session->data['user_token'] . '&translation_id=' . $this->request->get['translation_id'] . $url, true);
		}
		  
    	$data['cancel'] = $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . $url, true);

    	if (isset($this->request->get['translation_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$translation_info = $this->model_extension_translate->getTranslation($this->request->get['translation_id']);
    	}
			
    	if (isset($this->request->post['interface'])) {
      		$data['post_interface'] = $this->request->post['interface'];
		} elseif (isset($translation_info)) { 
			$data['post_interface'] = $translation_info['interface'];
		} else {
      		$data['post_interface'] = '';
    	}

    	if (isset($this->request->post['directory'])) {
      		$data['post_directory'] = $this->request->post['directory'];
    	} elseif (isset($translation_info)) { 
			$data['post_directory'] = $translation_info['directory'];
		} else {
      		$data['post_directory'] = '';
    	}

    	if (isset($this->request->post['filename'])) {
      		$data['post_filename'] = $this->request->post['filename'];
    	} elseif (isset($translation_info)) { 
			$data['post_filename'] = $translation_info['filename'];
		} else {
      		$data['post_filename'] = '';
    	}

    	if (isset($this->request->post['key'])) {
      		$data['post_key'] = $this->request->post['key'];
    	} elseif (isset($translation_info)) { 
			$data['post_key'] = $translation_info['key'];
		} else {
      		$data['post_key'] = '';
    	}

    	if (isset($this->request->post['value'])) {
      		$data['post_value'] = $this->request->post['value'];
    	} elseif (isset($translation_info)) { 
			$data['post_value'] = htmlspecialchars($translation_info['value']);
		} else {
      		$data['post_value'] = '';
    	}
		
		$data['interfaces'] = array_keys($this->model_extension_translate->getInterfaces());
		$data['directories'] = array_keys($this->model_extension_translate->getLanguages());
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/translate_form', $data));
	}

	private function validateForm() {
    	if (!$this->user->hasPermission('modify', 'extension/module/translate')) {
      		$this->error['warning'] = $this->language->get('error_permission');
			return false;
    	}

		if(!array_key_exists($this->request->post['interface'], $this->model_extension_translate->getInterfaces())) {
			$this->error['interface'] = $this->language->get('error_interface');
		}

		$languages = $this->model_extension_translate->getLanguages();
		if(!array_key_exists($this->request->post['directory'], $languages)) {
			$this->error['directory'] = $this->language->get('error_directory');
			$expr = '';
			foreach($languages as $language) {
				$expr .= '|^'.$language['filename'].'$';
			}
		}
		else $expr = '|^'.$languages[$this->request->post['directory']]['filename'].'$';
		
		if(!preg_match("/^[a-z0-9_]{1,63}\/[a-z0-9_\/]{1,63}$$expr/", $this->request->post['filename'])) {
			$this->error['filename'] = $this->language->get('error_filename');
		}
		
		if(!preg_match("/^[a-z0-9_]{1,127}$/", $this->request->post['key'])) {
			$this->error['key'] = $this->language->get('error_key');
		}
		
		$this->request->post['value'] = htmlspecialchars_decode($this->request->post['value']);
		
		if($translation_info = $this->model_extension_translate->getTranslationByData($this->request->post)) {
			if(!isset($this->request->get['translation_id'])) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
			elseif($this->request->get['translation_id'] != $translation_info['translation_id']) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		}
		
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}    

	private function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'extension/module/translate')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}	
	  	 
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
	}

	private function validateImport() {
    	if (!$this->user->hasPermission('modify', 'extension/module/translate')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}	
	  	 
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}  
	}

	private function burl() {
		$burl = '';

		if (isset($this->request->get['filter_interface'])) {
			$burl .= '&filter_interface=' . $this->request->get['filter_interface'];
		}
		
		if (isset($this->request->get['filter_directory'])) {
			$burl .= '&filter_directory=' . $this->request->get['filter_directory'];
		}
		
		if (isset($this->request->get['filter_filename'])) {
			$burl .= '&filter_filename=' . $this->request->get['filter_filename'];
		}
			
		if (isset($this->request->get['filter_key'])) {
			$burl .= '&filter_key=' . $this->request->get['filter_key'];
		}
			
		if (isset($this->request->get['filter_value'])) {
			$burl .= '&filter_value=' . $this->request->get['filter_value'];
		}

		if (isset($this->request->get['filter_status'])) {
			$burl .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_lang1'])) {
			$burl .= '&filter_lang1=' . $this->request->get['filter_lang1'];
		}

		if (isset($this->request->get['filter_lang2'])) {
			$burl .= '&filter_lang2=' . $this->request->get['filter_lang2'];
		}

		return $burl;
	}

	public function translatemode() {
		$this->load->language('extension/module/translate');
		 
		$this->document->setTitle($this->language->get('heading_title').' - '.$this->language->get('text_translatemode'));
		
		$this->load->model('extension/translate');
		
		//$this->model_extension_translate->checkTable();
		
    	$this->getListTranslatemode();
	}

	public function update() {
		$this->load->language('extension/module/translate');

		$this->document->setTitle($this->language->get('heading_title').' - '.$this->language->get('text_translatemode').' - '.$this->language->get('text_update'));
		
		$this->load->model('extension/translate');
		
		//$this->model_extension_translate->checkTable();

		$url = $this->burl();
				
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
				
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateUpdateForm()) {
			$data = array(
				'interface'	=> $this->request->post['interface'],
				'key'		=> $this->request->post['key']);

			foreach($this->model_extension_translate->getLanguages() as $language) {
				$data['directory'] = $language['directory'];
				$data['filename'] = $this->request->post['filename'] == 'main' ? $language['filename'] : $this->request->post['filename'];

				$translation = $this->model_extension_translate->getTranslationByData($data);

				if(isset($this->request->post['values'][$language['directory']])) {
					$data['value'] = $this->request->post['values'][$language['directory']];
					if($translation) $this->model_extension_translate->update($translation['translation_id'], $data['value']);
					else $this->model_extension_translate->insert($data);
				}
				elseif($translation) $this->model_extension_translate->delete($translation['translation_id']);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module/translate/translatemode', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		if(!isset($this->request->get['interface']) || !isset($this->request->get['filename']) || !isset($this->request->get['key'])) {
			$this->response->redirect($this->url->link('extension/module/translate/translatemode', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getUpdateForm();
	}

	private function getListTranslatemode() {
		
		$interfaces = array_keys($this->model_extension_translate->getInterfaces());

		if (isset($this->request->get['filter_interface']) && in_array($this->request->get['filter_interface'], $interfaces)) {
			$filter_interface = $this->request->get['filter_interface'];
		} else {
			$filter_interface = $interfaces[0];
		}

		$filenames = (array)$this->model_extension_translate->getFilenames($filter_interface);

		if (isset($this->request->get['filter_filename']) && in_array($this->request->get['filter_filename'], $filenames)) {
			$filter_filename = $this->request->get['filter_filename'];
		} else {
			$filter_filename = 'main';
		}

		$directories = array_keys($this->model_extension_translate->getLanguages());

		if (isset($this->request->get['filter_lang1']) && in_array($this->request->get['filter_lang1'], $directories)) {
			$filter_lang1 = $this->request->get['filter_lang1'];
		} else {
			$filter_lang1 = $directories[0];
		}

		if (isset($this->request->get['filter_lang2']) && in_array($this->request->get['filter_lang2'], $directories)) {
			$filter_lang2 = $this->request->get['filter_lang2'];
		} else {
			$filter_lang2 = isset($directories[1]) ? $directories[1] : $directories[0];
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'key'; 
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
						
		$url = $this->burl();

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
		
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_extension'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
			'separator' => ' :: '
		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => ' :: '
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_translatemode'),
			'href'      => $this->url->link('extension/module/translate/translatemode', 'user_token=' . $this->session->data['user_token'] . $url, true),
      		'separator' => ' :: '
   		);
		
		$data['editmode'] = $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'], true);

		$data['translations'] = array();

		$fdata = array(
			'filter_interface'              => $filter_interface, 
			'filter_filename' => $filter_filename, 
			'filter_lang1' => $filter_lang1, 
			'filter_lang2' => $filter_lang2, 
			'sort'                     => $sort,
			'order'                    => $order,
		);
		
		$translations_total = $this->model_extension_translate->getTotalTranslationsTranslatemode($fdata);
		
		$fdata['start'] = ($page - 1) * $translations_total;
		$fdata['limit'] = $translations_total;
		
		$translations = $this->model_extension_translate->getTranslationsTranslatemode($fdata);
 
    	foreach ($translations as $result) {
			$action = array();
		
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('extension/module/translate/update', 'user_token=' . $this->session->data['user_token'] . '&interface=' . $result['interface'] . '&filename=' . $result['filename'] . '&key=' . $result['key'] . $url, true)
			);
			
			$data['translations'][] = array(
				'interface'           => $result['interface'],
				'filename' => $result['filename'],
				'key'         => $result['key'],
				'value'         => $result['value'] === null ? $result['value'] : htmlspecialchars($result['value']),
				'value2'         => $result['value2'] === null ? $result['value2'] : htmlspecialchars($result['value2']),
				'action'         => $action,
			);
		}	
					
		$data['heading_title'] = $this->language->get('heading_title').' - '.$this->language->get('text_translatemode');

		$data['text_all'] = $this->language->get('text_all');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['text_main'] = $this->language->get('text_main');

		$data['column_interface'] = $this->language->get('column_interface');
		$data['column_directory'] = $this->language->get('column_directory');
		$data['column_filename'] = $this->language->get('column_filename');
		$data['column_key'] = $this->language->get('column_key');
		$data['column_value'] = $this->language->get('column_value');
		$data['column_value2'] = $this->language->get('column_value2');
		$data['column_action'] = $this->language->get('column_action');		
		
		$data['button_editmode'] = $this->language->get('button_editmode');
		$data['button_filter'] = $this->language->get('button_filter');/** **/

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		$url = $this->burl();

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		$data['sort_key'] = $this->url->link('extension/module/translate/translatemode', 'user_token=' . $this->session->data['user_token'] . '&sort=key' . $url, true);
		$data['sort_value'] = $this->url->link('extension/module/translate/translatemode', 'user_token=' . $this->session->data['user_token'] . '&sort=value' . $url, true);
		$data['sort_value2'] = $this->url->link('extension/module/translate/translatemode', 'user_token=' . $this->session->data['user_token'] . '&sort=value2' . $url, true);
		
		$url = $this->burl();

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $translations_total;
		$pagination->page = $page;
		$pagination->limit = 200;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
			
		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($translations_total) ? (($page - 1) * $pagination->limit) + 1 : 0, ((($page - 1) * $pagination->limit) > ($translations_total - $pagination->limit)) ? $translations_total : ((($page - 1) * $pagination->limit) + $pagination->limit), $translations_total, ceil($translations_total / $pagination->limit));

		$data['filter_interface'] = $filter_interface;
		$data['filter_filename'] = $filter_filename;
		$data['filter_lang1'] = $filter_lang1;
		$data['filter_lang2'] = $filter_lang2;
		
		$data['interfaces'] = $interfaces;
		$data['directories'] = $directories;
		$data['filenames'] = $filenames;
		
		$data['sort'] = $sort;
		$data['order'] = $order;
		
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/translate_translatemode', $data));
	}

	private function getUpdateForm() {
    	$data['heading_title'] = $this->language->get('heading_title').' - '.$this->language->get('text_translatemode').' - '.$this->language->get('text_update');

    	$data['entry_interface'] = $this->language->get('entry_interface');
    	$data['entry_filename'] = $this->language->get('entry_filename');
    	$data['entry_key'] = $this->language->get('entry_key');
    	$data['text_deleted'] = $this->language->get('text_deleted');

		$data['button_save'] = $this->language->get('button_save');
    	$data['button_cancel'] = $this->language->get('button_cancel');

		$data['user_token'] = $this->session->data['user_token'];

 		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

 		if (isset($this->error['interface'])) {
			$data['error_interface'] = $this->error['interface'];
		} else {
			$data['error_interface'] = '';
		}

 		if (isset($this->error['filename'])) {
			$data['error_filename'] = $this->error['filename'];
		} else {
			$data['error_filename'] = '';
		}
		
 		if (isset($this->error['key'])) {
			$data['error_key'] = $this->error['key'];
		} else {
			$data['error_key'] = '';
		}
		
		$url = $this->burl();
		
		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
						
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
  		$data['breadcrumbs'] = array();

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => false
   		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_extension'),
			'href'      => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
			'separator' => ' :: '
		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/module/translate', 'user_token=' . $this->session->data['user_token'], true),
      		'separator' => ' :: '
   		);
		
   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_translatemode'),
			'href'      => $this->url->link('extension/module/translate/translatemode', 'user_token=' . $this->session->data['user_token'] . $url, true),
      		'separator' => ' :: '
   		);
		
		$data['action'] = $this->url->link('extension/module/translate/update', 'user_token=' . $this->session->data['user_token'] . '&interface=' . $this->request->get['interface'] . '&filename=' . $this->request->get['filename'] . '&key=' . $this->request->get['key'] . $url, true);

    	$data['cancel'] = $this->url->link('extension/module/translate/translatemode', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['post_interface'] = isset($this->request->post['interface']) ? $this->request->post['interface'] : $this->request->get['interface'];
		$data['post_filename'] = isset($this->request->post['filename']) ? $this->request->post['filename'] : $this->request->get['filename'];
		$data['post_key'] = isset($this->request->post['key']) ? $this->request->post['key'] : $this->request->get['key'];

		$data['values'] = array();

		foreach($this->model_extension_translate->getLanguages() as $language) {
			$ldata = array(
				'interface'	=> $data['post_interface'],
				'directory'	=> $language['directory'],
				'filename'	=> $data['post_filename'] == 'main' ? $language['filename'] : $data['post_filename'],
				'key'		=> $data['post_key']);
			$translation_info = $this->model_extension_translate->getTranslationByData($ldata);
			$translation = array(
				'directory'			=> $language['directory'],
				'language_name'		=> $language['name'],
				'language_image'	=> $language['image']);
			if(isset($this->request->post['values'][$language['directory']])) {
				$translation['value'] = $this->request->post['values'][$language['directory']];
				$translation['deleted'] = false;
			}
			else {
				$translation['value'] = $translation_info ? htmlspecialchars($translation_info['value']) : '';
				$translation['deleted'] = $translation_info ? false : true;
			}
			$data['values'][$language['directory']] = $translation;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/translate_update_form', $data));
	}

	private function validateUpdateForm() {
    	if (!$this->user->hasPermission('modify', 'extension/module/translate')) {
      		$this->error['warning'] = $this->language->get('error_permission');
			return false;
    	}

		if(!array_key_exists($this->request->post['interface'], $this->model_extension_translate->getInterfaces())) {
			$this->error['interface'] = $this->language->get('error_interface');
		}
		elseif($this->request->post['filename'] != 'main' && !in_array($this->request->post['filename'], $this->model_extension_translate->getFilenames($this->request->post['interface']))) {
			$this->error['filename'] = $this->language->get('error_filename');
		}

		if(!preg_match("/^[a-z0-9_]{1,127}$/", $this->request->post['key'])) {
			$this->error['key'] = $this->language->get('error_key');
		}
		
		if(isset($this->request->post['values'])) {
			foreach($this->request->post['values'] as &$value) {
				$value = htmlspecialchars_decode($value);
			}
			unset($value);
		}
		
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}
		
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}    

}