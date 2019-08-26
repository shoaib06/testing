<?php
    require_once('header.php');
?>

    <div class="container" style="z-index:1;background-color:#fff;">
        <div class="row" >
            <div class="col-md-4">
                <?php
                    require_once('form.php');
                ?>
            </div>
            <div class="col-md-8">
                <div class="stickyBox" style="position:sticky; top:10%;">
                    <div class="row justify-content-center" style="border:1px solid grey;">
                        <div class="preview" id="preview" style="height:600px;">                    
                            <img style="max-width:100%;max-height:100%;" data-toggle="modal" data-target="#imageModal" src="" alt="preview" id="previewIMG"/>
                        </div>
                    </div>
                    <div class="row mt-5 justify-content-center">
                        <button id="btnPrint" class="btn-dark">Print</button>
                        <button class="btn-success" data-toggle="modal" data-target="#templateModal">Choose Template</button>
                    </div>

                </div>                
            </div>            
        </div>


    <div class="container" style="position:fixed;top:100px;z-index:-1;">
        <div id="template" style="background-color:#fff;">
        </div>
    </div>




    </div>

    
<!-- Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Preview</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <img id="modalIMG" width="100%" src="" />
      </div>
    </div>
  </div>
</div>




<?php
    require_once('footer.php');
?>