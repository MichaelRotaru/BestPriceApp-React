<?php
require_once('include/helper.php');

if (! class_exists('ParserCSV')) :
    /**
     * Clasa de procesare a unui fisier CSV dupa un format prestabilit si introducere a datelor in baza de date
     */
    class ParserCSV
    {
        public function __construct($db, $fileName, $sellerId)
        {
            $this->db = $db;
            $this->records = array();
            $this->status = '';
            $this->separator = ';';
            $this->filename = $fileName;
            $this->sellerId = $sellerId;
        }

        /**
         * Initiaza procesarea
         */
        public function doParse()
        {
            $name = $this->filename;
            $sellerId = $this->sellerId;
            $errors = array();
            /* Verifica daca identificatorul reprezinta fisier */
            if (($handle = fopen($name, 'r')) !== false) {
                /* Necesar pentru fisierele de dimensiuni mari */
                set_time_limit(0);
                $row = 0;
                $newProds = 0;
                $newLinks = 0;

                $header = array();

                $seller = array(
                  'last_update' => date("Y-m-d H:i:s")
                );
                $insertSellerProd	=	$this->db->update('seller', $seller, array("id"=>$sellerId));

                /* Proceseaza fisierul linie cu linie */
                while (($data = fgetcsv($handle, 1000, $this->separator)) !== false) {
                    $col_count = count($data);
                    /* Verifica daca cap de tabel */
                    if ($row == 0) {
                        /* Incarca capul de tabel */
                        foreach ($data as &$value) {
                            $header[] = $value;
                        }
                    } else {
                        $record = array();
                        foreach ($data as $key => $value) {
                            $record[$header[$key]] = $value;
                        }

                        $check_sku = array('sku' => $record['sku']);
                        /* Verifica existenta produsului in tabelul 'product_description' */
                        if (!sizeof($this->db->get('product_description', $check_sku))) {
                            /* Proceseaza si salveaza imaginea inregistrarii */
                            if (Helper::urlIsImage($record['thumb'])) {
                                $thumb_name = Helper::saveImageFromURL($record['thumb'], $_SERVER['DOCUMENT_ROOT'].'/'.UPLOADS_FOLDER.$sellerId."/images/");
                            } else {
                                $errors[] = "Invalid image! - ".$record['thumb'];
                            }

                            /* Actualizeaza datele in baza de date */
                            $product_description = array(
                                'sku' => $record['sku'],
                                'title' => $record['title'],
                                'thumb' => $sellerId.'/images/'.$thumb_name,
                                'short_desc' => $record['short_desc']
                            );
                            $insertProd	=	$this->db->insert('product_description', $product_description);
                            $newProds++;
                        }

                        /* Verifica existenta produsului in tabelul 'product_seller' */
                        $check_seller = array('product_sku' => $record['sku'],'seller_id' => $sellerId);
                        if (!sizeof($this->db->get('product_seller', $check_seller))) {
                            /* Actualizeaza datele in baza de date */
                            $product_seller = array(
                                'product_sku' => $record['sku'],
                                'seller_id' => $sellerId,
                                'url' => $record['url'],
                                'price' => $record['price']
                            );
                            $insertSellerProd	=	$this->db->insert('product_seller', $product_seller);
                            $newLinks++;
                        }
                    }
                    $row++;
                }
                fclose($handle);
            }
            $this->status = "Status";
            $this->errors = $errors;
            $this->linesProcessed = $row-1;/*Capul de table*/
            $this->newLinks = $newLinks;
            $this->prodsCreated = $newProds;

            return array("status"=>$this->status, "errors"=>$this->errors, "linesProcessed"=>$this->linesProcessed, "newLinks"=>$this->newLinks, "prodsCreated"=>$this->prodsCreated);
        }
    }
endif;
