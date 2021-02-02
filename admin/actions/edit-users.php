<?php
chdir('../');
require_once('include/application_top.php');
require_once('parsers/csv.php');

/* Verifica daca este o cerere de afisare */
if(isset($_REQUEST['edit_id']) && $_REQUEST['edit_id']!=""){
		/* Selecteaza datele vanzatorului */
		$row	=	$db->getAllRecords('seller','*',' && id="'.$_REQUEST['edit_id'].'"');
		$val = $row[0];
}
/* Verifica daca o cerere de modificare a datelor de vanzator */
if(isset($_REQUEST['submit_update']) && $_REQUEST['submit_update']!=""){
	extract($_REQUEST);
	$data	=	array(
		 'name'=>$s_name,
		 'home_url'=>$s_home_url,
	);
	/* Valideaza datele introduse */
	if($s_name==""){
		$notice_messages[] = 102;
	}elseif($s_home_url==""){
		$notice_messages[] = 103;
	}elseif ($helper->fileExist($_FILES["s_logo"])) {
		if($helper->fileIsImage($_FILES["s_logo"])){
			try{
				$helper->delFile(UPLOADS_FOLDER.$edit_id."/".$val['logo']);
			}catch(Exception $e){
				$notice_messages[] = 101;
			}
			$file = $_FILES["s_logo"];
			$server_url = $helper->saveFile($file,$edit_id.'/');
			$data['logo'] = $server_url;
		}else{
			$notice_messages[] = 104;
		}
	}else{
		$notice_messages[] = 1;
	}
	/* Actualizeaza datele in baza de date */
	$update	=	$db->update('seller',$data,array('id'=>$edit_id));
	$notice->setMessage($notice_messages,$_SERVER['REQUEST_URI']);
}

/* Verifica daca o cerere de actualizare de feed */
if(isset($_REQUEST['submit_feed']) && $_REQUEST['submit_feed']!="" ){
	extract($_REQUEST);

	/* Verifica daca adresa reprezinta un fisier si fisierul este de tip CSV */
	if ($helper->fileExist($_FILES["s_csv"]) && $helper->fileIsCSV($_FILES["s_csv"])){
		 $file = $_FILES["s_csv"];
		 /* Proceseaza fisierul */
		 $parser = new ParserCSV($db,$file['tmp_name'],$edit_id);
		 $response = $parser->doParse();
     $response_message = $response["status"].
                         '<ul><li>'.sizeof($response["errors"]).' Erori'.'</li><li>'.
                         $response["linesProcessed"].' Linii procesate'.'</li><li>'.
                         $response["prodsCreated"].' Produse create'.'</li><li>'.
                         $response["newLinks"].' Noi legaturi'.'</li></ul>';

		 $notice_messages[] = 1;
		 $notice_messages[] = $response_message;
	}else{
		 $notice_messages[] = 105;
	}
	/* Seteaza mesajul de notificare */
	$notice->setMessage($notice_messages,$_SERVER['REQUEST_URI']);
}

/* Verifica daca o cerere de schimbare a adresei de actualizare */
if(isset($_REQUEST['submit_scrapper']) && $_REQUEST['submit_scrapper']!="" ){
	 extract($_REQUEST);
	 if($s_feed_url==""){
		 $notice_messages[] = 101;/* feed invalid */
	 }else{
		 $data	=	array(
             'feed_url'=>$s_feed_url,
           );
		 /* Actualizeaza in baza de date */
     $update = $db->update('seller',$data,array('id' => $edit_id));
		 $notice_messages[] = 1;
	 }
	 $notice->setMessage($notice_messages,$_SERVER['REQUEST_URI']);
}
/* Incarca componenta HTML header */
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
				<div class="card-header"><strong>Editeaza Vanzator</strong> <a href="<?= SITE_URL ?>app.php" class="float-right btn btn-dark btn-sm"><i class="fa fa-fw fa-globe"></i> Inapoi la Panou</a></div>
				<div class="card-body">
					<div class="row">
						<div class="col-sm-6">
							<h5>Modifica detaliile vanzatorului</h5>
							<form method="post" enctype="multipart/form-data">
								<div class="form-group">
									<label>Nume Vanzator <span class="text-danger">*</span></label>
									<input type="text" name="s_name" id="s_name" class="form-control" value="<?php echo $val['name']; ?>" placeholder="Introdu numele" required>
								</div>
								<div class="form-group">
									<label>URL Vanzator<span class="text-danger">*</span></label>
									<input type="text" name="s_home_url" id="s_home_url" class="form-control" value="<?php echo $val['home_url']; ?>" placeholder="Introdu adresa radacina" required>
								</div>
								<div class="form-group">
									<label>Logo<span class="text-danger">*</span></label>
									<img class='seller_logo' name="s_current_logo" id="s_current_logo" src='<?= SITE_URL.UPLOADS_FOLDER_NAME.$val['id'].'/'.$val['logo'] ?>' />
									<input type="file" name="s_logo" id="s_logo" />
								</div>
								<div class="form-group">
									<input type="hidden" name="edit_id" id="edit_id" value="<?php echo $_REQUEST['edit_id']?>">
									<button type="submit" name="submit_update" value="submit_update" id="submit_update" class="btn btn-primary"><i class="fa fa-fw fa-edit"></i> Modifica</button>
								</div>
							</form>
						</div>
						<div class="col-sm-6">
							<form method="post" enctype="multipart/form-data">
								<h5>Management feeduri</h5>
								<div class="form-group">
									<label>Feed de produse<span class="text-danger">*</span></label>
									<input type="file" name="s_csv" id="s_csv" />
								</div>

								<div class="form-group">
									<input type="hidden" name="edit_id" id="edit_id" value="<?php echo $_REQUEST['edit_id']?>">
									<button type="submit" name="submit_feed" value="submit_feed" id="submit_feed" class="btn btn-primary"><i class="fa fa-fw fa-upload"></i> Incarca</button>
								</div>
							</form>
							<?php
								$syncUrl = SITE_URL."actions/sync.php?edit_id=".$_REQUEST['edit_id'];
								$cleanUrl = SITE_URL."actions/clean-seller.php?edit_id=".$_REQUEST['edit_id'];
							?>
							<form method="post" enctype="multipart/form-data">
								<h5>Sincronizare automata</h5>
								<div class="form-group">
									<label>Scrapper URL<span class="text-danger">*</span></label>
									<input type="text" name="s_feed_url" id="s_feed_url" class="form-control" value="<?php echo $val['feed_url']; ?>" placeholder="Introdu adresa URL a feedului" required>
								</div>
								<div class="form-group">
									<input type="hidden" name="edit_id" id="edit_id" value="<?= $_REQUEST['edit_id']?>">
									<button type="submit" name="submit_scrapper" value="submit_scrapper" id="submit_scrappe" class="btn btn-primary"><i class="fas fa-fw fa-edit"></i> Modifica</button>
								</div>
							</form>
							<form method="post" enctype="multipart/form-data">
								<h5>Actualizare manuala</h5>
								<div class="form-group">
									<a class="btn btn-primary" href="<?= $syncUrl ?>"><i class="fas fa-fw fa-sync-alt"></i> Actualizeaza</a>
								</div>
								<h5>Curatare</h5>
								<div class="form-group">
									<a class="btn btn-danger" href="<?= $cleanUrl ?>"><i class="fas fa-fw fa-trash"></i> Sterge toate produsele</a>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>
