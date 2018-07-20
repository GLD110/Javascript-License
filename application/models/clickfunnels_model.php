<?php
class Clickfunnels_model extends Master_model
{
    protected $_tablename = 'clickfunnels';
    function __construct() {
        parent::__construct();
    }

    /**
    * Get the list of account
    *
    * @param mixed $team_id
    */
    public function getList()
    {
        $sql = 'SELECT * FROM ' . $this->_tablename;

        $query = $this->db->query($sql);

        return $query;
    }

    function createClickfunnels(){
        $email = $this->input->post('email');
        $is_active = $this->input->post('is_active');
        $created = date("Y/m/d");
        $scripts = '';
        if($is_active){
          $scripts = '<script src="' . $this->config->item('base_url') . 'uploads/clickfunnels/'  . base64_encode($email) . '.js"></script>';
          $fn = $this->config->item('app_path') . 'asset/js/clickfunnels.js';
          $newfn = $this->config->item('app_path') . 'uploads/clickfunnels/' . base64_encode($email) . '.js'; 

          if(copy($fn,$newfn))
            echo 'The file was copied successfully';
          else
            echo 'An error occurred during copying the file';
        }

        $data = array(
            'email'=>$email,
            'is_active'=>$is_active,
            'd_o_c'=>$created,
            'scripts'=> $scripts
        );

        $this->db->insert( $this->_tablename, $data);
        if($this->db->affected_rows()>0){
            return true;
        }
        else{
            return FALSE;
        }
    }
}
?>
