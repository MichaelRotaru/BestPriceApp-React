<?php
  require_once('include/application_top.php');
  require_once('parsers/csv.php');

  /* Verifica tipul cererii */
  if(isset($_REQUEST['submit']) && $_REQUEST['submit']!=""){
  	extract($_REQUEST);
    /* Valideaza datele din formular */
  	if($s_name==""){
      $notice_messages[] = 102;//nume invalid
  	}elseif($s_home_url==""){
      $notice_messages[] = 103;//url invalid
  	}elseif (!$helper->fileExist($_FILES["s_logo"]) || ($helper->fileExist($_FILES["s_logo"]) && !$helper->fileIsImage($_FILES["s_logo"]))) {
      $notice_messages[] = 104;//logo invalid
  	}elseif ($helper->fileExist($_FILES["s_csv"]) && !$helper->fileIsCSV($_FILES["s_csv"])) {
      $notice_messages[] = 105;//feed invalid
  	}else{
      /* Introdu inregistrarea in baza de date */
      $data	=	array(
              'name'=>$s_name,
              'home_url'=>$s_home_url,
            );
      $insert	=	$db->insert('seller',$data);
      $sellerId	=	$db->lastInsertId('seller');
      /* Creeaza sistemul de directoare uploads */
      $helper->makeDir("uploads/".$sellerId);
      $helper->makeDir("uploads/".$sellerId.'/images');


      if($insert){
        /* Proceseaza imaginea logo */
        $file = $_FILES["s_logo"];
        $server_url = $helper->saveFile($file,$sellerId.'/');
        $update =	$db->update('seller', array('logo'=>$server_url), array('id'=>$sellerId));

        /* Proceseaza fisierul feed */
        if ($helper->fileExist($_FILES["s_csv"]) && $helper->fileIsCSV($_FILES["s_logo"])) {
           $file = $_FILES["s_csv"];
           $parser = new ParserCSV($db,$file['tmp_name'],$sellerId);
        }
        $notice_messages[] = 1;
      }else{
        $notice_messages[] = 101;
      }
  	}
    /* Seteaza mesajele de success / eroare */
    $notice->setMessage($notice_messages);
  }

  require_once('components/header.php');
?>

  <body>
    <header>
      <div class="container mt-2">
        <?php
          $notice->display();
        ?>
      </div>
    </header>
    <div class="container">
      <div class="card">
        <div class="card-header">
          <strong>Adauga Vanzator</strong>
          <a href="<?= SITE_URL ?>auth/logout.php" class="float-right btn btn-dark btn-sm ml-2">Deconectare</a>
          <a href="<?= SITE_URL ?>auth/register.php" class="float-right btn btn-dark btn-sm">Creeaza cont administrator</a>
        </div>
        <div class="card-body">
          <div class="col-sm-6">
            <form method="post" enctype="multipart/form-data">
              <div class="form-group">
                <label>Nume <span class="text-danger">*</span></label>
                <input type="text" name="s_name" id="s_name" class="form-control" placeholder="Enter seller name" value="<?= isset( $_REQUEST['s_name'])?$_REQUEST['s_name']:'' ?>">
                <!-- required -->
              </div>

              <div class="form-group">
                <label>URL <span class="text-danger">*</span></label>
                <input type="text" name="s_home_url" id="s_home_url" class="form-control" placeholder="Enter seller url" value="<?= isset( $_REQUEST['s_home_url'])?$_REQUEST['s_home_url']:'' ?>">
              </div>

              <div class="form-group">
                <label>Logo<span class="text-danger">*</span></label>
                <input type="file" name="s_logo" id="s_logo" />
              </div>

              <div class="form-group">
                <label>Feed de produse</label>
                <input type="file" name="s_csv" id="s_csv" />
              </div>
              <div class="form-group">
                <button type="submit" name="submit" value="submit" id="submit" class="btn btn-primary"><i class="fa fa-fw fa-plus-circle"></i>Adauga Vanzator</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <?php
      $condition	=	'';
      if(isset($_REQUEST['username']) && $_REQUEST['username']!=""){
        $condition	.=	' && username LIKE "%'.$_REQUEST['username'].'%" ';
      }
      $condition	=	'';

      $sellerData	=	$db->getAllRecords('seller','*',$condition,'ORDER BY id DESC');
      ?>
    <div class="container mt-3 mb-3">
      <div>
        <table class="table table-striped table-bordered">
          <thead>
            <tr class="bg-primary text-white">
              <th>#</th>
              <th>Logo</th>
              <th>Nume vanzator</th>
              <th>URL vanzator</th>
              <th class="text-center">Ultima actualizare</th>
              <th class="text-center">Actiune</th>
            </tr>
          </thead>
          <tbody>
            <?php
  					if(count($sellerData)>0){
  						$s	=	'';
  						foreach($sellerData as $val){
  							$s++;
  					?>
            <tr>
              <td><?= $s ?></td>
              <td><?php if($val['logo']){echo "<img class='seller_logo' src='".UPLOADS_FOLDER_NAME.$val['id'].'/'.$val['logo']."'/>";}else{echo " ";}?></td>
              <td><?= $val['name'] ?></td>
              <td><?= $val['home_url'] ?></td>
              <td align="center"><?= $val['last_update'] ?></td>
              <td align="center">
                <a href="actions/edit-users.php?edit_id=<?php echo $val['id'];?>" class="text-primary"><i class="fa fa-fw fa-edit"></i> Modifica</a> |
                <a href="actions/delete.php?del_id=<?php echo $val['id'];?>" class="text-danger" onClick="return confirm('Esti sigur?');"><i class="fa fa-fw fa-trash"></i> Sterge</a>
              </td>
            </tr>
            <?php
  						}
  					}else{
  					?>
            <tr>
              <td colspan="6" align="center">Nu a fost gasit niciun rezultat!</td>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </body>
</html>
