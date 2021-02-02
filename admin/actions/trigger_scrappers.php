<?php
chdir('../');
require_once('config.php');
require_once('include/db.php');
require_once('include/helper.php');
$helper = new Helper($db);

/* Adresa locala a subprogramului scrapper */
$scrapper_path = 'scrapper.php';

/* Incarca vanzatorii */
$sellers = $db->getAllRecords('seller', $fields='*');

$helper::log("INIT SCRAPPER UPDATE");
foreach ($sellers as $seller) {
    /* Verifica daca adresa URL reprezinta o adresa URL valida */
    if (filter_var($seller['feed_url'], FILTER_VALIDATE_URL) === false) {
        continue;
    }

    $helper::log("Se incearca accesarea scrapperului #".$seller['id']);

    /* Initializeaza apelul CURL */
    $ch = curl_init($seller['feed_url'].$scrapper_path);
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
            $sql_update	=	$db->update('seller', array('scrapper_version'=>intval($sql_get[0]['scrapper_version'])+1), array('id'=>$seller['id']));
            $helper::log("Scrapper actualizat cu success pentru vanzatorul #".$seller['id']);
        } else {
            $helper::log("Scrapper actualizat cu success pentru vanzatorul #".$seller['id']);
        }
    } else {
        $helper::log("Scrapper-ul a esuat pentru vanzatorul #".$seller['id']);
        $helper::log("Cod eroare ".$curl_code);
    }
    curl_close($ch);
}
