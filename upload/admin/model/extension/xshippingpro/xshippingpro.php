<?php
class ModelExtensionXshippingproXshippingpro extends Model
{
  
   public function addData($data) {
   
        $row_exist = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xshippingpro` WHERE tab_id = '" . (int)$data['tab_id'] . "'")->row;
        
        if ($row_exist) {
            $sql="UPDATE `" . DB_PREFIX . "xshippingpro` SET method_data= '" . $this->db->escape($data['method_data']) . "', sort_order = '".$data['sort_order']."'";
            $sql.="WHERE tab_id = '" . (int)$data['tab_id'] . "'";
        } else {
            $sql="INSERT INTO `" .DB_PREFIX . "xshippingpro` SET method_data= '" . $this->db->escape($data['method_data']) . "', sort_order = '".$data['sort_order']."'";
            $sql.= ", `tab_id` = '".(int)$data['tab_id']."'";
        }
        
        $this->db->query($sql);
        return true;
    }
    
    public function getData() {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "xshippingpro` order by `sort_order` asc")->rows;
    }
    
    public function getDataByTabId($tab_id) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "xshippingpro` WHERE tab_id = '" . (int)$tab_id . "'")->row;
    }
    
     public function deleteData($tab_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "xshippingpro` WHERE tab_id = '" . (int)$tab_id . "'");
        return true;
    }

    private function addEvents($deleteFirst = false) {

        $this->load->model('setting/event');

        if ($deleteFirst) {
            $this->model_setting_event->deleteEventByCode('xshippingpro');
        }

        $this->model_setting_event->addEvent('xshippingpro', 'catalog/view/mail/order_add/before', 'extension/shipping/xshippingpro/onOrderEmail');

        $this->model_setting_event->addEvent('xshippingpro', 'catalog/view/*/shipping_method*/before', 'extension/shipping/xshippingpro/onShippingMethod');
    }
    
    public function install() {

        $this->addEvents();
            
        $sql = "
            CREATE TABLE IF NOT EXISTS `".DB_PREFIX."xshippingpro` (
              `id` int(8) NOT NULL AUTO_INCREMENT,
              `method_data` longtext NULL,
              `tab_id` int(8) NULL,
              `sort_order` int(8) NULL,
               PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
        ";
        $query = $this->db->query($sql);

        $this->safeColumnAdd('xshippingpro', array (
           array('column'=>'sort_order','extra'=>'int(8) NULL')
        ));
    }
    
    public function uninstall(){
    
       $this->load->model('setting/event');
       $this->model_setting_event->deleteEventByCode('xshippingpro');
       $query = $this->db->query("DROP TABLE IF EXISTS `".DB_PREFIX."xshippingpro`");
         
    }
    
    public function isDBBUPdateAvail() {

      $events = array();
      $events[] = 'catalog/view/mail/order_add/before';
      $events[] = 'catalog/view/*/shipping_method*/before';

      $rows = $this->db->query("SELECT DISTINCT * FROM `" . DB_PREFIX . "event` WHERE `code` = 'xshippingpro'")->rows;
      $existing_events = array();
      foreach ($rows as $key => $value) {
        $existing_events[] = $value['trigger'];
      }

      $need_update = false;

      if (count($events) != count($existing_events)) {
          $need_update = true;
      } else {
          foreach ($events as $event) {
             if (!in_array($event, $existing_events)) {
                 $need_update = true;
             }
          }
      }

      /* if need to update, then delete current ones and update again*/
      if ($need_update) {
          $this->addEvents(true);
      }

      
       $tables=array('xshippingpro');   
        foreach($tables as $table){
          if(!$this->db->query("SELECT * FROM information_schema.tables WHERE table_schema = '".DB_DATABASE."' AND table_name = '".DB_PREFIX.$table."' LIMIT 1")->row){
             return true;
          }
        }

        $columns=array('sort_order');
          foreach($columns as $column){
             if(!$this->db->query("SELECT * FROM information_schema.columns WHERE table_schema = '".DB_DATABASE."' AND table_name = '".DB_PREFIX."xshippingpro' and column_name='".$column."' LIMIT 1")->row){
                   return true;
              }
          }

        return false;

     }

     private function safeColumnAdd($table,$columns) {
       if(!is_array($columns))$columns=array();
       if($table) {
          foreach($columns as $columnInfo) {
             $column=$columnInfo['column'];
             $extra=$columnInfo['extra'];
             if(!$this->db->query("SELECT * FROM information_schema.columns WHERE table_schema = '".DB_DATABASE."' AND table_name = '".DB_PREFIX.$table."' and column_name='".$column."' LIMIT 1")->row){
               $query = $this->db->query("ALTER TABLE `".DB_PREFIX.$table."` ADD `".$column."` ".$extra); 
             }
          }
       }
    }
  
}

?>