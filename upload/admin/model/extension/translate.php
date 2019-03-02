<?php
/**
 * Database Language Editor
 * SQL-based Language Editor for Opencart
 * 
 * 
 **/
class ModelExtensionTranslate extends Model {

	public function import($interface, $directory, $filename) {
		$count = 0;
		$interfaces = $this->getInterfaces();
		$file = $interfaces[$interface]['langdir'].$directory.'/'.$filename.'.php';
		if(is_file($file)) {
			$_ = array();
			require($file);
			foreach($_ as $k => $v) {
				$data = array(
					'interface'	=> $interface,
					'directory'	=> $directory,
					'filename'	=> $filename,
					'key'		=> $k,
					'value'		=> $v
				);
				if(!$translation = $this->getTranslationByData($data)) {
					$id = $this->insert($data);
					$count++;
				}
				else {
					$id = $translation['translation_id'];
				}
				/** **/
				$this->updateOriginal($id, $data['value']);
			}
		}
		return $count;
	}

	public function insert($data) {
		$this->db->query("INSERT INTO ".DB_PREFIX."translate
							SET `interface` = '".$this->db->escape($data['interface'])."',
							`directory` = '".$this->db->escape($data['directory'])."',
							`filename` = '".$this->db->escape($data['filename'])."',
							`key` = '".$this->db->escape($data['key'])."',
							`value` = '".$this->db->escape($data['value'])."'");
		return $this->db->getLastId();
	}

	public function update($translation_id, $value) {
		$this->db->query("UPDATE ".DB_PREFIX."translate
							SET `value` = '".$this->db->escape($value)."'
							WHERE `translation_id` = '".(int)$translation_id."'");
	}

	public function delete($translation_id) {
		$this->db->query("DELETE FROM ".DB_PREFIX."translate
							WHERE `translation_id` = '".(int)$translation_id."'");
	}

	public function getTranslation($translation_id) {
		$query = $this->db->query("SELECT *, (`value` = `original`) AS `status` FROM ".DB_PREFIX."translate
									WHERE `translation_id` = '".(int)$translation_id."'");
		return $query->row;
	}

	public function getTranslationByData($data) {
		$query = $this->db->query("SELECT *, (`value` = `original`) AS `status` FROM ".DB_PREFIX."translate
									WHERE `interface` = '".$this->db->escape($data['interface'])."'
									AND `directory` = '".$this->db->escape($data['directory'])."'
									AND `filename` = '".$this->db->escape($data['filename'])."'
									AND `key` = '".$this->db->escape($data['key'])."'");
		return $query->row;
		
	}

	public function getTranslations($data = array()) {
		$sql = "SELECT *, (`value` = `original`) AS `status` FROM " . DB_PREFIX . "translate";

		$implode = array();
		
		if (!empty($data['filter_interface'])) {
			$implode[] = "`interface` = '" . $this->db->escape(utf8_strtolower($data['filter_interface'])) . "'";
		}
		
		if (!empty($data['filter_directory'])) {
			$implode[] = "`directory` = '" . $this->db->escape(utf8_strtolower($data['filter_directory'])) . "'";
		}
		
		if (!empty($data['filter_filename'])) {
			$implode[] = "`filename` LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_filename'])) . "%'";
		}
		
		if (!empty($data['filter_key'])) {
			$implode[] = "`key` LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_key'])) . "%'";
		}
		
		if (!empty($data['filter_value'])) {
			$implode[] = "`value` LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_value'])) . "%'";
		}
		
		if (isset($data['filter_status'])) {
			if($data['filter_status'] == 'equals') $implode[] = "`value` = `original`";
			elseif($data['filter_status'] == 'differs') $implode[] = "`value` <> `original`";
			elseif($data['filter_status'] == 'new') $implode[] = "`original` IS NULL";
		}
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
		
		$sort_data = array(
			'interface',
			'directory',
			'filename',
			'key',
			'value',
			'status'			
		);
			
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `".$data['sort']."`";	
		} else {
			$sql .= " ORDER BY `key`";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}		
		
		$query = $this->db->query($sql);
		
		return $query->rows;	
	}

	public function getTotalTranslations($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "translate";
		
		$implode = array();
		
		if (!empty($data['filter_interface'])) {
			$implode[] = "`interface` = '" . $this->db->escape(utf8_strtolower($data['filter_interface'])) . "'";
		}
		
		if (!empty($data['filter_directory'])) {
			$implode[] = "`directory` = '" . $this->db->escape(utf8_strtolower($data['filter_directory'])) . "'";
		}
		
		if (!empty($data['filter_filename'])) {
			$implode[] = "`filename` LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_filename'])) . "%'";
		}
		
		if (!empty($data['filter_key'])) {
			$implode[] = "`key` LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_key'])) . "%'";
		}
		
		if (!empty($data['filter_value'])) {
			$implode[] = "`value` LIKE '%" . $this->db->escape(utf8_strtolower($data['filter_value'])) . "%'";
		}
		
		if (isset($data['filter_status'])) {
			if($data['filter_status'] == 'equals') $implode[] = "`value` = `original`";
			elseif($data['filter_status'] == 'differs') $implode[] = "`value` <> `original`";
			elseif($data['filter_status'] == 'new') $implode[] = "`original` IS NULL";
		}
		
		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}
				
		$query = $this->db->query($sql);
				
		return $query->row['total'];
	}

	public function getInterfaces() {
		$interfaces = array();
		$interfaces[basename(DIR_APPLICATION)] = array('name' => basename(DIR_APPLICATION), 'langdir' => DIR_LANGUAGE);
		$interfaces[basename(DIR_CATALOG)] = array('name' => basename(DIR_CATALOG), 'langdir' => DIR_CATALOG.'language/');
		return $interfaces;
	}

	public function getLanguages() {
		$languages = array();
		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."language");
		foreach($query->rows as $language) {
			$languages[$language['code']] = array(
				'directory' => $language['code'],
				'filename' => $language['code'],
				'name' => $language['name'],
				'image' => 'language/'.$language['code'].'/'.$language['code'].'.png');
		}
		return $languages;
	}

	public function checkTable() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `".DB_PREFIX."translate` (
							`translation_id` int(11) NOT NULL AUTO_INCREMENT,
							`interface` varchar(32) NOT NULL,
							`directory` varchar(32) NOT NULL,
							`filename` varchar(128) NOT NULL,
							`key` varchar(128) NOT NULL,
							`value` text NOT NULL,
							`original` blob,
							PRIMARY KEY (`translation_id`),
							KEY `key` (`key`),
							KEY `path` (`interface`,`directory`,`filename`),
							FULLTEXT KEY `value` (`value`)
						) ENGINE=MyISAM  DEFAULT CHARSET=utf8");
		return true;
	}

	public function resetOriginal() {
		$this->db->query("UPDATE ".DB_PREFIX."translate SET `original` = NULL");
	}

	public function updateOriginal($translation_id, $original) {
		$this->db->query("UPDATE ".DB_PREFIX."translate
							SET `original` = '".$this->db->escape($original)."'
							WHERE `translation_id` = '".(int)$translation_id."'");
	}

	public function deleteTranslatemode($interface, $filename, $key) {
		$this->db->query("DELETE FROM ".DB_PREFIX."translate
							WHERE `interface` = '".$this->db->escape($interface)."'
							AND `filename` = '".$this->db->escape($filename)."'
							AND `key` = '".$this->db->escape($key)."'");
	}

	public function getTranslationsTranslatemode($data) {
		if($data['filter_filename'] == 'main') {
			$filenames = "SELECT `code` AS `filename` FROM ".DB_PREFIX."language";
			$languages = $this->getLanguages();
			$filter_filename1 = $languages[$data['filter_lang1']]['filename'];
			$filter_filename2 = $languages[$data['filter_lang2']]['filename'];
		}
		else {
			$filenames = "'".$this->db->escape(utf8_strtolower($data['filter_filename']))."'";
			$filter_filename1 = $filter_filename2 = $data['filter_filename'];
		}
		$sql = "
			SELECT
				'".utf8_strtolower($data['filter_interface'])."' `interface`,
				'".utf8_strtolower($data['filter_filename'])."' `filename`,
				t0.`key` `key`,
				t1.`value` `value`,
				t1.`original` `original`,
				t2.`value` `value2`,
				t2.`original` `original2`
			FROM (
				SELECT DISTINCT `key` FROM ".DB_PREFIX."translate
					WHERE `interface` = '".$this->db->escape(utf8_strtolower($data['filter_interface']))."'
					AND `filename` IN (".$filenames.")
			) t0
			LEFT JOIN (
				SELECT `key`, `value`, `original` FROM ".DB_PREFIX."translate
					WHERE `interface` = '".$this->db->escape(utf8_strtolower($data['filter_interface']))."'
					AND `filename` = '".$this->db->escape(utf8_strtolower($filter_filename1))."'
					AND `directory` = '".$this->db->escape(utf8_strtolower($data['filter_lang1']))."'
			) t1
			ON t0.`key` = t1.`key`
			LEFT JOIN (
				SELECT `key`, `value`, `original` FROM ".DB_PREFIX."translate
					WHERE `interface` = '".$this->db->escape(utf8_strtolower($data['filter_interface']))."'
					AND `filename` = '".$this->db->escape(utf8_strtolower($filter_filename2))."'
					AND `directory` = '".$this->db->escape(utf8_strtolower($data['filter_lang2']))."'
			) t2
			ON t0.`key` = t2.`key`
		";
		
		$sort_data = array(
			'key',
			'value',
			'value2'
		);
			
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY `".$data['sort']."`";	
		} else {
			$sql .= " ORDER BY `key`";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			//$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}		
		
		$query = $this->db->query($sql);
		
		return $query->rows;	
	}

	public function getTotalTranslationsTranslatemode($data) {
		if($data['filter_filename'] == 'main') {
			$filenames = "SELECT `code` AS `filename` FROM ".DB_PREFIX."language";
		}
		else $filenames = "'".$this->db->escape(utf8_strtolower($data['filter_filename']))."'";
		$sql = "SELECT COUNT(DISTINCT `key`) AS total FROM ".DB_PREFIX."translate
				WHERE `interface` = '".$this->db->escape(utf8_strtolower($data['filter_interface']))."'
				AND `filename` IN (".$filenames.")
		";
		
		$query = $this->db->query($sql);
		
		return $query->row['total'];	
	}

	public function getFilenames($interface) {
		$query = $this->db->query("SELECT DISTINCT `filename` FROM ".DB_PREFIX."translate
									WHERE `interface` = '" . $this->db->escape($interface) . "'
									AND `filename` LIKE '%/%'
									ORDER BY `filename`");
		$result = array();
		foreach($query->rows as $row) {
			$result[] = $row['filename'];
		}
		return $result;
	}

}