<?php
chdir('../');
require_once('include/application_top.php');

/* Verifica daca parametrul edit_id este specificat */
if (isset($_REQUEST['edit_id']) && $_REQUEST['edit_id']!="") {
    extract($_REQUEST);
    /* Sterge produsele vanzatorului din baza de date */
    $query = "DELETE p, ps FROM product_seller ps LEFT JOIN product_description p ON ps.product_sku = p.sku WHERE ps.seller_id = ".$edit_id."";
    $result = $db->query($query);
    $helper->delDir(UPLOADS_FOLDER_NAME.$edit_id.'/images');
    $helper->makeDir(UPLOADS_FOLDER_NAME.$edit_id.'/images');
    /* Seteaza notificarea */
    $notice->setMessage(1, SITE_URL."actions/edit-users.php?edit_id=".$edit_id);
}
