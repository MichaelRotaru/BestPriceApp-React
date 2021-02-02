<?php
chdir('../');
require_once('include/application_top.php');

/* Verifica daca parametrul del_id este specificat */
if (isset($_REQUEST['del_id']) && $_REQUEST['del_id']!="") {
    /* Sterge vanzatorul din baza de date */
    $db->delete('seller', array('id'=>$_REQUEST['del_id']));
    $helper->delDir("uploads/".$_REQUEST['del_id']);
    /* Seteaza notificarea */
    $notice->setMessage(1, SITE_URL."app.php");
}
