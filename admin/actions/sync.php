<?php
chdir('../');

require_once('config.php');
require_once('include/helper.php');
require_once('include/db.php');
require_once('include/notice.php');
require_once('parsers/csv.php');

$helper = new Helper($db);

/* Adresa locala a rezultatului scrapper */
$result_path = 'results.csv';
/* Verifica daca cererea a fost aruncata automat sau de catre un utilizator */
$is_human = 1;
if (isset($_REQUEST['human']) and $_REQUEST['human']=="0") {
    $is_human = 0;
}
if (isset($_REQUEST['edit_id']) and $_REQUEST['edit_id']!="") {
    extract($_REQUEST);
    $seller = $db->get('seller', array('id' => $edit_id));

    /* In cazul in care fisierul este valid initiaza parsarea */
    if ($seller[0]['feed_url']) {
        $parser = new ParserCSV($db, $seller[0]['feed_url'].$result_path, $edit_id);
        $response = $parser->doParse();
        if ($response["linesProcessed"]<1) {
            $response_message = "Feed invalid";
            echo $response["status"];
            return false;
        }
        $response_message = $response["status"].
                          '<ul><li>'.sizeof($response["errors"]).' Erori'.'</li><li>'.
                          $response["linesProcessed"].' Linii procesate'.'</li><li>'.
                          $response["prodsCreated"].' Produse create'.'</li><li>'.
                          $response["newLinks"].' Noi legaturi'.'</li></ul>';
        if ($is_human) {
            $notice->setMessage($response_message, SITE_URL."actions/edit-users.php?edit_id=".$edit_id);
        }
        echo json_encode(array("success"=>1,"message"=>$response_message));
        return true;
    } else {
        if ($is_human) {
            $notice->setMessage(101, SITE_URL."actions/edit-users.php?edit_id=".$edit_id);
        }
        $response_message = "Feed invalid";
        echo json_encode(array("success"=>0,"message"=>$response_message));
        return false;
    }
} else {
    if ($is_human) {
        $notice->setMessage(101, SITE_URL."actions/edit-users.php?edit_id=".$edit_id);
    }
    $response_message = "Feed invalid";
    echo json_encode(array("success"=>0,"message"=>$response_message));
    return false;
}
