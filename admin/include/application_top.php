<?php
require_once('config.php');
require_once('helper.php');

/* Initializeaza sesiunea */
session_start();

/* Verifica daca utilizatorul este autentificat si in caz ca nu redirectioneaza catre pagina de autentificare */
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location:".SITE_URL."auth/login.php");
    exit;
}

/* Initializeaza dependintele necesare aplicatiei */
require_once('include/db.php');
require_once('include/notice.php');

$helper = new Helper($db);
