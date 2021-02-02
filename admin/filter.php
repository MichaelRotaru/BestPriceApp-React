<?php
/**
 * API de cautare a unui set de cuvinte in baza de date
 * @param GET string s - lista de cuvinte sub forma de sir de caractere separate prin spatiu
 * @return JSON_OBJECT - lista de rezultate
 */
header('Access-Control-Allow-Origin: *');

/* Incarca dependintele */
require_once('config.php');
require_once('include/db.php');


$query_string = strtolower($_GET['s']);
/* Separa cuvintele din cerere */
$keywords = explode(' ', $query_string);

/* Lista de greutati a campurilor in care se doreste cautarea cuvintelor cheie */
$fields = array(
  "sku" => 2,
  "title" => 1.5,
  "short_desc" => 1,
);

/* Incarca rezultatul SQL */
$products = searchSQL($fields, $keywords);

/* Proceseaza rezultatul sql */
foreach ($products as &$prod) {
    $product_seller_query = array(
      "product_sku" => $prod['sku']
    );
    $product_sellers = $db->get('product_seller', $product_seller_query);
    $sellers = array();
    foreach ($product_sellers as &$ps) {
        $seller_query = array(
          "id" => $ps['seller_id']
        );
        $seller = $db->get('seller', $seller_query)[0];
        $seller = array_merge($seller, $ps);
        $sellers[] = $seller;
    }
    $prod["sellers"] = $sellers;
}

/* Returneaza lista de produse + sugestiile de vanzatori */
echo json_encode($products);

/**
 * Initializeaza o cautarea Full-text a listei de cuvinte date in tabelul 'product_description'
 *
 * @param array $fields - lista de greutati
 * @param array $words - lista de cuvinte pentru care se doreste cautarea
 * @return array lista de rezultate
 */
function searchSQL($fields, $words)
{
    global $db;

    $match = '';
    $cond = '';
    $weight_formula = '';
    $f=1;
    $w=1;
    $params = array();
    /* Compune stringul SQL */
    foreach ($words as $word) {
        foreach ($fields as $name => $weight) {
            $match .= ", MATCH(".$name.") AGAINST (:f".$f.") AS rel".$name.$w;
            $params['f'.$f] = $word;
            if ($f > 1) {
                $weight_formula.="+";
            }
            $weight_formula.= "rel".$name.$w."*".$weight;
            $f++;
        }
        if ($w > 1) {
            $cond.=' OR ';
        }
        $cond.= "MATCH(".implode(',', array_keys($fields)).") AGAINST (:w".$w.")";
        $params['w'.$w] = $word;
        $w++;
    }
    $stmt = $db->getPdo()->prepare("SELECT * ".$match." FROM product_description WHERE ".$cond." ORDER BY ".$weight_formula." DESC LIMIT 100");

    try {
        $stmt->execute($params);

        $res = $stmt->fetchAll();
        if (! $res || count($res) != 1) {
            return $res;
        }
        return $res;
    } catch (\PDOException $e) {
        throw new \RuntimeException("[".$e->getCode()."] : ". $e->getMessage());
    }
}
