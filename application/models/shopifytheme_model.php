<?php
class Shopifytheme_model extends Master_model
{
    protected $_tablename = 'shopifytheme';
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

    function createShopifytheme(){
        $email = $this->input->post('email');
        $is_active = $this->input->post('is_active');
        $created = date("Y/m/d");
        $license = md5($email);

        $data = array(
            'email'=>$email,
            'is_active'=>$is_active,
            'd_o_c'=>$created,
            'license'=> $license
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
