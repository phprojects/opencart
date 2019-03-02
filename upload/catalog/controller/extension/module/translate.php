<?php
/**
 * Database Language Editor
 * SQL-based Language Editor for Opencart
 * 
 * 
 **/
class ControllerExtensionModuleTranslate extends Controller {

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

}