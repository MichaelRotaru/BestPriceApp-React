<?php
  chdir('../');
  require_once('config.php');

  require_once('include/db.php');
  require_once('include/helper.php');

  $helper = new Helper($db);
  $helper->uninstall();
  $helper->install();

  echo "Instalarea s-a efectuat cu success.";
  echo "<br/>";
  echo("Va rugam stergeti fisierul install.php.");
