<?php
  $arrRole = $this->config->item('USER_ROLE');
?>
<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>
    Theme Customer List
    <small>Manage</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li><a href="#">Shopifytheme</a></li>
    <li class="active">Customer List</li>
  </ol>
</section>

<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box">
        <div class="box-header">
          <h3 class="box-title">Customer List</h3>
        </div><!-- /.box-header -->
        <div class="col-md-12 column"  style = "border-bottom:solid 1px #ddd; margin-bottom:4px; padding-bottom: 5px;" >
            <a id="modal-666931" href="#modal-container-666931" role="button" class="btn btn-default btn-sm" data-toggle="modal">
                <i class="glyphicon glyphicon-plus"></i>&nbsp; Add new Customer
            </a>&nbsp;
        </div>
        <div class="box-body">
          <table id="example2" class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th class = "text-center" >S. NO.</th>
                    <th class = "text-center" >Email</th>
                    <th class = "text-center" >License Code</th>
                    <th class = "text-center" >Is Active</th>
                    <th class = "text-center" >Created</th>
                    <th class = "text-center" >Action</th>
                </tr>
            </thead>
            <tbody>
            <?php $sno = 1; ?>
                 <?php foreach ($query->result() as $row): ?>

                <tr class="tbl_view text-center" >

                    <td>
                        <?php echo $sno;?>
                    </td>
                    <td>
                        <a href="#" name="email" class="email" data-type="text" data-pk="<?= $row->id?>" data-url="<?php echo base_url( $this->config->item('index_page') . '/product/updateShopifytheme/email' ) ?>" data-title="Enter new Email"><?=$row->email ?></a>
                    </td>
                    <td>
                        <input disabled style="width: 100%; color: green;" value="<?PHP echo $row->license; ?>" />
                    </td>
                    <td>
                        <a href="#" class="is_active" data-name="<?=$row->email ?>" data-type="select" data-pk="<?= $row->id?>" data-url="<?php echo base_url( $this->config->item('index_page') . '/product/updateShopifytheme/is_active' ) ?>" data-title="Select status"><?php echo $row->is_active == '1' ? 'Active' : 'Inactive' ?></a>
                    </td>

                    <td>
                        <?php
                            $created = strtotime($row->d_o_c);
                            echo date('F jS Y', $created );
                        ?>
                    </td>

                    <td>
                        <div class="btn-group">
                            <button  class="btn btn-default btn-sm btn_delete"  type="submit" title="Delete" data-email="<?PHP echo $row->email; ?>" del_id = '<?PHP echo $row->id; ?>' >
                            <i class="glyphicon glyphicon-remove"></i></button>
                         </div>
                    </td>
                </tr>
               <?php $sno = $sno+1;  endforeach; ?>
            </tbody>
          </table>
        </div><!-- /.box-body -->
      </div><!-- /.box -->
    </div><!-- /.col -->
  </div><!-- /.row -->
</section><!-- /.content -->

<form method="POST" id='delfunnels' action="<?php echo base_url( $this->config->item('index_page') . '/product/delShopifytheme' ) ?>" >
    <input type="hidden" id = 'del_id' name="del_id" value=""/>
</form>

<div class="modal fade" id="modal-container-666931" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h4 class="modal-title" id="myModalLabel">Create new entry</h4>
            </div>

            <div class="modal-body">
                <div id='retAddTransaction'></div>

                <form class="form-horizontal cus-form" id="Add_transaction" method="POST" action="<?php echo base_url( $this->config->item('index_page') . '/product/createShopifytheme' ) ?>"  data-parsley-validate>

                    <table class="table table-bordered">
                    <tr>
                        <td colspan="2">
                            <label>Customer Email</label>
                            <input type="text" name="email" id='email' class="form-control input-group-sm" required/>
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <div>
                            <label>Is Active</label>
                            </div>
                            <div class="tran-type">
                                <label>Yes</label>
                                    <input type="radio" value="1" name="is_active" style="display: inline">

                                <label>No</label>
                                <input type="radio" checked="checked" value="0" name="is_active" style="display: inline">


                            </div>
                        </td>
                    </tr>
                    </table>
                    <div style="padding-left: 10px; padding-bottom: 10px; margin-top: -8px;">
                        <button type="submit" name="submit" class="btn btn-success">Submit</button>
                        <button id="cancle" name="cancle" class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Cancel</button>
                    </div>

                </form>
            </div>

        </div>

    </div>

</div>



<a class="confirmLink" href="#"></a>
<div id="dialog" title="Confirmation Required" style="display:none;">
  Are you sure want to delete?
</div>

<script>
$("#modal-container-666931").on('hidden.bs.modal', function(e){window.location.reload();});

$(document).ready(function (){
    // Editable
    //$('.email').editable();
    $('.is_active').editable({
         source: [
            {value: 1, text: 'Active'},
            {value: 0, text: 'Inactive'}

        ]
    });

    // ********** Delete Action ********** //
    $(".btn_delete").on('click', function (e){
;       //e.preventDefault();
        console.log('dele');
        $('#del_id').val( $(this).attr( 'del_id' ) );
        $('#del_email').val( $(this).attr( 'data-email' ) );
        $('.confirmLink').trigger('click'); return false;
    });

    $("#dialog").dialog({
        autoOpen: false,
        modal: true
    });


    $(".confirmLink").click(function(e) {
        e.preventDefault();
        var targetUrl = $(this).attr("href");

        $("#dialog").dialog({
            buttons : {
            "Confirm" : function() {
                $(this).dialog("close");
                $("#delfunnels").submit();
            },
            "Cancel" : function() {
                $(this).dialog("close");
                return false;
                }
            }
        });

        $("#dialog").dialog("open");
    });

    // ********************************* //

    $( "#Add_transaction" ).submit(function( event ) {
       var url = $(this).attr('action');
            $.ajax({
            url: url,
            data: $("#Add_transaction").serialize(),
            type: $(this).attr('method')
          }).done(function(data) {
              $('#retAddTransaction').html(data);
//                  window.location.reload();
            $('#Add_transaction')[0].reset();
          });
        event.preventDefault();
    });

});
</script>
<script>
  /*$(document).ready(function(){
    var email = 'diana4545@outlook.com';
    $.get("http://localhost:9080/javascript-license/product/getThemelicense?email=" + email, function(data, status){
        console.log(data);
        if(data == '02d33cd939a286fafbd2f3243892d666')
          alert(data);
    });
  });*/

    /*.ajax({
      type: "POST",
      url: "http://localhost:9080/javascript-license/product/getThemelicense",
      data: 'email=' + 'diana4545@outlook.com',
      dataType: "json", // Set the data type so jQuery can parse it for you
      success: function (data) {
        console.log(data);
      }
  });*/
</script>
