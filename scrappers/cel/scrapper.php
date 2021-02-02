<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once '../src/domparser/simple_html_dom.php';
require_once '../src/helper.php';

$message = 1;

$products = array();
/* Adresa radacina a categoriei de unde se incepe incarcarea produselor */
// $root_url = "https://www.cel.ro/gel-antibacterian/";
// $root_url = "https://www.cel.ro/ceasuri-de-dama/";
$root_url = "https://www.cel.ro/laptop-laptopuri/";
$next_url = $root_url;

writeResult(array(array("sku","title","url","short_desc","price","thumb")),"w");

/* Se incearca accesarea tuturor paginilor din categoria ROOT */
$temp = 0;
do{
  try {
      $next_url = checkPage($root_url.$next_url);
  } catch(Exception $e) {
      $message = "Eroare 404";
  }
  /* Pauza pentru a nu supraincarca serverul */
  sleep(1/* secunde */);
  $temp++;
  gc_collect_cycles();
// }while($next_url && $temp<1);
}while($next_url);

/**
 * Proceseaza si incarca o pagina de produse in variabila globala $products
 * @param $url adresa URL a paginii
 * @return - adresa url a paginii urmatoare | 0 in caz ca nu exista
 */
function checkPage($url){
  /*Campuri necesare: sku;title;price;thumb;url;short_desc*/
  global $context,$root_url;
  $products = array();
  $i=0;
  $dom = file_get_html($url, false, $context);
  if(!empty($dom)){
    foreach($dom->find(".productlisting .product_data") as $divClass){
      $sku = $divClass;
      $products[$i]['sku'] = escape($sku->pid_prod,true);

      $title = $divClass->find(".product_link span")[0];
      $products[$i]['title'] = escape($title->plaintext,true);

      $link = $divClass->find(".product_link")[0];
      $products[$i]['url'] = $root_url.escape($link->href,true);

      $s_desc = $divClass->find(".caract_scurte")[0];
      $products[$i]['short_desc'] = escape($s_desc->plaintext);

      $new_price = $divClass->find(".pret_n")[0];
      $products[$i]['price'] = floatval(str_replace('.','',escape($new_price->plaintext)));

      $image = $divClass->find(".productListing-poza a img")[0];
      $products[$i]['thumb'] = escape($image->content);

      $i++;
    }
  }

  $pagination_wp = $dom->find(".listingWrapper .pageresults")[0];
  $next_page = $pagination_wp->find("a.last");

  writeResult($products);
  // if($next_page){
  //   return $next_page[0]->href;
  // }

  return 0;/* urmatoarea pagina*/
}

function writeResult($content_lines,$mode = "a"){
  /* Scrie fisierul rezultat */
  $output = fopen("results.csv",$mode) or die("Can't open php://output");
  foreach($content_lines as $line) {
      fputcsv($output, $line, ';');
  }
  fclose($output) or die("Can't close php://output");
}

/* Returneaza raspunsul */
if($message == 1){
  echo json_encode(array("success"=>1,"message"=>"Success"));
}else{
  echo json_encode(array("success"=>0,"message"=>$message));
}
