<?php
chdir('../');
require_once('config.php');
require_once('include/db.php');
require_once('include/helper.php');
$helper = new Helper($db);

/* Adresa URL a actiunii de actualizare */
$scrapper_path = SITE_URL.'actions/sync.php';

/* Incarca vanzatorii */
$sellers = $db->getAllRecords('seller', $fields='*');

$helper::log("INIT CRON SYNC");
foreach ($sellers as $seller) {
    if (intval($seller['database_version']) >= intval($seller['scrapper_version'])) {
        continue;
    }

    /* Verifica daca adresa URL reprezinta o adresa URL valida */
    if (filter_var($seller['feed_url'], FILTER_VALIDATE_URL) === false) {
        continue;
    }

    $helper::log("Se incearca sincronizarea vanzatorului #".$seller['id']);

    /* Initializeaza apelul CURL */
    $ch = curl_init($scrapper_path.'?edit_id='.$seller['id'].'&is_human=0');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $c = curl_exec($ch);
    $curl_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    /* Proceseaza raspunsul */
    /* Daca raspunsul este valid si are codul 200 | 302 */
    if ($curl_code == "200" || $curl_code == "302") {
        $result = json_decode($c, true);
        if ($result['success'] == 1) {
            $sql_get = $db->get('seller', array('id'=>$seller['id']));
            $sql_update	=	$db->update('seller', array('database_version'=>$sql_get[0]['scrapper_version']), array('id'=>$seller['id']));
            $helper::log("Sincronizare cu success pentru vanzatorul #".$seller['id']);
        } else {
            $helper::log("Sincronizare a esuat pentru vanzatorul #".$seller['id']);
        }
        $helper::log("Rezultat: ".$result['message']);
    } else {
        $helper::log("Sincronizare a esuat pentru vanzatorul #".$seller['id']);
        $helper::log("Cod eroare ".$curl_code);
    }
    curl_close($ch);
}
