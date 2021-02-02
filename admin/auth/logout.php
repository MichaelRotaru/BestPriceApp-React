<?php
/* Porneste sesiunea */
session_start();

/* Curata sesiunea */
$_SESSION = array();

/* Distruge sesiunea */
session_destroy();

/* Redirectioneaza utilizatorul catre pagina de autentificare */
header("location: login.php");
exit;
