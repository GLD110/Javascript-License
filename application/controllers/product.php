<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Product extends MY_Controller {

  public function __construct() {
    parent::__construct();
    $this->load->model( 'Product_model' );
    $this->load->model( 'Clickfunnels_model' );
    $this->load->model( 'Shopifytheme_model' );

    // Define the search values
    $this->_searchConf  = array(
      'name' => '',
      'sku' => '',
      'shop' => $this->_default_store,
      'page_size' => $this->config->item('PAGE_SIZE'),
      'sort_field' => 'product_id',
      'sort_direction' => 'DESC',
    );
    $this->_searchSession = 'product_app_page';
  }

  public function index(){
    $this->is_logged_in();

    $this->manage();
  }

  public function manage( $page =  0 ){
    // Check the login
    $this->is_logged_in();

    // Init the search value
    $this->initSearchValue();

    // Get data
    $this->Product_model->rewriteParam($this->_searchVal['shop']);
    $arrCondition =  array(
      'name' => $this->_searchVal['name'],
      'sku' => $this->_searchVal['sku'],
      'sort' => $this->_searchVal['sort_field'] . ' ' . $this->_searchVal['sort_direction'],
      'page_number' => $page,
      'page_size' => $this->_searchVal['page_size'],
    );
    $data['query'] =  $this->Product_model->getList( $arrCondition );
    $data['total_count'] = $this->Product_model->getTotalCount();
    $data['page'] = $page;

    // Store List
    $arr = array();
    foreach( $this->_arrStoreList as $shop => $row ) $arr[ $shop ] = $shop;
    $data['arrStoreList'] = $arr;

    // Define the rendering data
    $data = $data + $this->setRenderData();

    // Load Pagenation
    $this->load->library('pagination');

    $this->load->view('view_header');
    $this->load->view('view_product', $data );
    $this->load->view('view_footer');
  }

  public function update( $type, $pk )
  {
    // Check the login
    $this->is_logged_in();

    $data = array();

    switch( $type )
    {
        case 'type' : $data['type'] = $this->input->post('value'); break;
        case 'title' : $data['title'] = $this->input->post('value'); break;
        case 'p_code' : $data['p_code'] = $this->input->post('value'); break;
        case 'img_resource' : $data['img_resource'] = $this->input->post('value'); break;
        case 'sku' : $data['sku'] = $this->input->post('value'); break;
        case 'item_per_square' : $data['item_per_square'] = str_replace( ',', '.', $this->input->post('value') ); break;
    }
    if($type == 'img_resource')
    {
      $this->Product_model->update_resources($pk, $data);
    }
    if($type == 'p_code')
    {
      $this->Product_model->update_pcodes($pk, $data);
    }
    else{
      $this->Product_model->update( $pk, $data );
    }
  }

  public function sync( $shop = '', $page = 1 )
  {
    $this->load->model( 'Process_model' );
    $this->load->model( 'Collection_model' );

    // Set the store information
    $this->Product_model->rewriteParam( $shop );

    $this->load->model( 'Shopify_model' );
    $this->Shopify_model->setStore( $shop, $this->_arrStoreList[$shop]->app_id, $this->_arrStoreList[$shop]->app_secret );

    // // Get the lastest day of collection
    // $last_day = $this->Collection_model->getLastUpdateDate();
    // $last_day = str_replace(' ', 'T', $last_day);
    //
    // $param = 'limit=250';
    // if( $last_day != '' ) $param .= '&updated_at_min=' . $last_day;
    // //$action = 'custom_collections.json?' . $param;
    //
    // $action = 'smart_collections.json';
    //
    // // Retrive Data from Shop
    // $collectionInfo = $this->Shopify_model->accessAPI( $action );
    //
    // // Store to database
    // if( isset($collectionInfo->smart_collections) && is_array($collectionInfo->smart_collections) )
    // {
    //   foreach( $collectionInfo->smart_collections as $collection )
    //   {
    //     /*$collection_productIDs = $this->Shopify_model->accessAPI( 'products.json?fields=id&collection_id=' . $collection->id );
    //     $c_pIDs = '';
    //     foreach($collection_productIDs->products as $c_p)
    //     {
    //       $c_pIDs = $c_pIDs . ',' . $c_p->id;
    //     }
    //     $collection->product_ids = $c_pIDs;*/
    //     $this->Collection_model->addCollection( $collection );
    //   }
    // }

    // Get the lastest day
    $last_day = $this->Product_model->getLastUpdateDate();

    // Retrive Data from Shop
    $count = 0;

    // Make the action with update date or page
    $action = 'products.json?';
    if( $last_day != '' && $last_day != $this->config->item('CONST_EMPTY_DATE') && $page == 1 )
    {
      $action .= 'limit=250&updated_at_min=' . urlencode( $last_day );
    }
    else
    {
      $action .= 'limit=20&page=' . $page;
    }

    // Retrive Data from Shop
    $productInfo = $this->Shopify_model->accessAPI( $action );

    // Store to database
    if( isset($productInfo->products) && is_array($productInfo->products) )
    {
      foreach( $productInfo->products as $product )
      {
        $this->Process_model->product_create( $product, $this->_arrStoreList[$shop] );
      }
    }

    // Get the count of product
    if( $last_day != '' && $last_day != $this->config->item('CONST_EMPTY_DATE') && $page == 1 )
    {
      $count = 0;
    }
    else
    {
      if( isset( $productInfo->products )) $count = count( $productInfo->products );
      $page ++;
    }

    if( $count == 0 )
      echo 'success';
    else
      echo $page . '_' . $count;
  }

  function clickfunnels(){
      // Check the login
      $this->is_logged_in();

      if($this->session->userdata('role') == 'admin'){
          $data['query'] =  $this->Clickfunnels_model->getList();
          $data['arrStoreList'] =  $this->_arrStoreList;

          $this->load->view('view_header');
          $this->load->view('view_clickfunnels', $data);
          $this->load->view('view_footer');
      }
  }

  function delClickfunnels(){
      if($this->session->userdata('role') == 'admin'){
          $id = $this->input->get_post('del_id');
          $email = $this->input->get_post('del_email');

          $fn = $this->config->item('app_path') . 'uploads/clickfunnels/' . base64_encode($email) . '.js';
          if(unlink($fn)){
            echo sprintf("The file %s deleted successfully",$fn);
          }else{
            echo sprintf("An error occurred deleting the file %s",$fn);
          }

          $returnDelete = $this->Clickfunnels_model->delete( $id );
          if( $returnDelete === true ){
              $this->session->set_flashdata('falsh', '<p class="alert alert-success">One item deleted successfully</p>');
          }
          else{
              $this->session->set_flashdata('falsh', '<p class="alert alert-danger">Sorry! deleted unsuccessfully : ' . $returnDelete . '</p>');
          }
      }
      else{
          $this->session->set_flashdata('falsh', '<p class="alert alert-danger">Sorry! You have no rights to deltete</p>');
      }
      redirect('product/clickfunnels');
      exit;
  }

  function createClickfunnels(){
     if($this->session->userdata('role') == 'admin'){
      $this->form_validation->set_rules('email', 'Email', 'callback_email_check');
      //$this->form_validation->set_rules('password', 'Password', 'required|matches[cpassword]');

      if ($this->form_validation->run() == FALSE){
          echo validation_errors('<div class="alert alert-danger">', '</div>');
          exit;
      }
      else{
            if($this->Clickfunnels_model->createClickfunnels()){
                echo '<div class="alert alert-success">This Customer added successfully</div>';
                //redirect('product/clickfunnels');
                exit;
            }
            else{
                echo '<div class="alert alert-danger">Sorry ! something went wrong </div>';
                exit;
            }
          }
     }
     else{
         echo '<div class="alert alert-danger">Invalid Email</div>';
         exit;
     }
  }

  function updateClickfunnels( $key ){
    if($this->session->userdata('role') == 'admin'){
      $val = $this->input->post('value');
      $email = $this->input->post('name');
      $scripts = '';
      if( $val ){
        $scripts = '<script src="' . $this->config->item('base_url') . 'uploads/clickfunnels/'  . base64_encode($email) . '.js"></script>';
        $fn = $this->config->item('app_path') . 'asset/js/clickfunnels.js';
        $newfn = $this->config->item('app_path') . 'uploads/clickfunnels/' . base64_encode($email) . '.js';

        if(copy($fn,$newfn))
          echo 'The file was copied successfully';
        else
          echo 'An error occurred during copying the file';
      }
      else
      {
        $fn = $this->config->item('app_path') . 'uploads/clickfunnels/' . base64_encode($email) . '.js';
        if(unlink($fn)){
        echo sprintf("The file %s deleted successfully",$fn);
        }else{
        echo sprintf("An error occurred deleting the file %s",$fn);
        }
      }
      $data = array(
        $key => $val, 'scripts' => $scripts
      );

      $this->Clickfunnels_model->update( $this->input->post('pk'), $data );
    }
  }

  public function email_check($str){
      $query =  $this->db->get_where('clickfunnels', array('email'=>$str));

      if (count($query->result())>0)
      {
          $this->form_validation->set_message('email_check', 'The %s already exists');
          return FALSE;
      }
      else
      {
          return TRUE;
      }
  }

  function shopifytheme(){
      // Check the login
      $this->is_logged_in();

      if($this->session->userdata('role') == 'admin'){
          $data['query'] =  $this->Shopifytheme_model->getList();
          $data['arrStoreList'] =  $this->_arrStoreList;

          $this->load->view('view_header');
          $this->load->view('view_shopifytheme', $data);
          $this->load->view('view_footer');
      }
  }

  function delShopifytheme(){
      if($this->session->userdata('role') == 'admin'){
          $id = $this->input->get_post('del_id');

          $returnDelete = $this->Shopifytheme_model->delete( $id );
          if( $returnDelete === true ){
              $this->session->set_flashdata('falsh', '<p class="alert alert-success">One item deleted successfully</p>');
          }
          else{
              $this->session->set_flashdata('falsh', '<p class="alert alert-danger">Sorry! deleted unsuccessfully : ' . $returnDelete . '</p>');
          }
      }
      else{
          $this->session->set_flashdata('falsh', '<p class="alert alert-danger">Sorry! You have no rights to deltete</p>');
      }
      redirect('product/shopifytheme');
      exit;
  }

  function createShopifytheme(){
     if($this->session->userdata('role') == 'admin'){
      $this->form_validation->set_rules('email', 'Email', 'callback_shopifyemail_check');
      //$this->form_validation->set_rules('password', 'Password', 'required|matches[cpassword]');

      if ($this->form_validation->run() == FALSE){
          echo validation_errors('<div class="alert alert-danger">', '</div>');
          exit;
      }
      else{
            if($this->Shopifytheme_model->createShopifytheme()){
                echo '<div class="alert alert-success">This Customer added successfully</div>';
                //redirect('product/clickfunnels');
                exit;
            }
            else{
                echo '<div class="alert alert-danger">Sorry ! something went wrong </div>';
                exit;
            }
          }
     }
     else{
         echo '<div class="alert alert-danger">Invalid Email</div>';
         exit;
     }
  }

  function updateShopifytheme( $key ){
    if($this->session->userdata('role') == 'admin'){
      $val = $this->input->post('value');

      $data = array(
        $key => $val
      );

      $this->Shopifytheme_model->update( $this->input->post('pk'), $data );
    }
  }

  public function shopifyemail_check($str){
      $query =  $this->db->get_where('shopifytheme', array('email'=>$str));

      if (count($query->result())>0)
      {
          $this->form_validation->set_message('email_check', 'The %s already exists');
          return FALSE;
      }
      else
      {
          return TRUE;
      }
  }

  public function getThemelicense(){

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST");
    header('Content-Type: application/json');

    //var_dump($_GET[ "mail" ]);exit;

    if( isset( $_GET["email" ] ) ){

      $email = trim(preg_replace('/\s\s+/', ' ', $_GET[ "email" ]));
      //$license = trim(preg_replace('/\s\s+/', ' ', $_POST[ "license" ]));
      $query =  $this->db->get_where('shopifytheme', array('email'=>$email));

      if (count($query->result())>0)
      {
          $result_code = $query->result()[0]->license;
          $is_active = $query->result()[0]->is_active;

          if($is_active){
            echo json_encode(array( '0' => $result_code));
          }
          else{
            echo json_encode(array( '0' => false));
          }
      }
      else
      {
          echo json_encode(array( '0' => false));
      }
    }
    else{
        echo json_encode(array( '0' => false));
    }
  }
}
