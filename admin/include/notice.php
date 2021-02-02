<?php
/* Verificare de securitate a clasei */
if (! class_exists('NoticeEngine')) :
    /**
     * Clasa de manageriaza sitemul de afisare a erorilor in aplicatie
     */
    class NoticeEngine
    {
        /* Constante */
        /* Coduri mesaje de tipul notice/success */
        public $MESSAGES = array(
          "1"=>"Operatiune finalizata cu succcess!",
          "2"=>"Vanzator sters cu succes!",
          "3"=>"Vanzator adaugat cu succes!",
          "4"=>"Produse adaugate cu succes!",
          "5"=>"Vanzator editat cu succes!",
          "6"=>'Contul a fost creeat cu success!'
        );

        /* Coduri mesaje de tipul eroare */
        public $ERRORS = array(
          "101"=>"A aparut o eroare, va rugam incercati din nou!",
          "102"=>"Numele vanzatorului este invalid!",
          "103"=>"Adresa URL a vanzatorului este invalida!",
          "104"=>"Logo invalid!",
          "105"=>"Feed invalid!",
          "106"=>"Fisier invalid!",
        );

        public $codes = [];
        /* Numele variabilei COOKIE ce stocheaza mesajele */
        private $cookieName = 'shl_notices';

        public function __construct()
        {
            /* Incarca valoarea din COOKIE */
            $this->loadCookieErrors();
        }
        /**
         * Afiseaza mesajele in format HTML
         */
        public function display()
        {
            foreach($this->codes as $code){
              if(isset($code)){
                if(isset($this->ERRORS[$code])){
                  echo '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> '.$this->ERRORS[$code].'</div>';
                }elseif(isset($this->MESSAGES[$code])){
                  echo '<div class="alert alert-success"><i class="fa fa-thumbs-up"></i> '.$this->MESSAGES[$code].'</div>';
                }else{
                  echo '<div class="alert alert-success">'.$code.'</div>';
                }
              }
            }
        }

        /**
         * Popuneaza variabila COOKIE cu un set de valori date
         *
         * @param array $codes - lista de erori pentru care se doreste stocarea
         */
        private function setCookieErrors($codes)
        {
            setcookie($this->cookieName, json_encode($codes), time()+3600, '/');
        }

        /**
         * Incarca valoarea din variabila COOKIE si curata variabila COOKIE
         */
        private function loadCookieErrors()
        {

            if(isset($_COOKIE[$this->cookieName])){
              $this->codes = json_decode($_COOKIE[$this->cookieName], true);
              /* Sterge variabila COOKIE */
              setcookie($this->cookieName, '', time()-3600, '/');
            }
        }

        public function setMessage($codes,$page = ''){
          if(!is_array($codes)){
            $codes = [$codes];
          }
          $this->setCookieErrors($codes);
          $this->redirect($page);
        }

        public function redirect($page = '')
        {
            if($page == ''){
              $page = $_SERVER['PHP_SELF'];
            }
            header('location:'.$page);
            exit;
        }
    }
endif;

/* Initializeaza obiectul notice */
$notice = new NoticeEngine();
$notice_messages = [];
