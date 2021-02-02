<?php
require_once('config.php');

/* Verificare de securitate a clasei */
if (! class_exists('Helper')) :
    class Helper
    {
        public $db;
        public function __construct($db)
        {
            $this->db = $db;
        }

        /**
         * Metoda de instalare a aplicatiei
         *
         * @return void
         */
        public function install()
        {
            if (!$this->db->query("DESCRIBE `seller`")) {
                $this->db->query("CREATE TABLE `seller` (
                `id` INT(11) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
                `name` VARCHAR(255) NOT NULL,
                `home_url` VARCHAR(255) NOT NULL,
                `feed_url` VARCHAR(255),
                `logo` VARCHAR(255),
                `database_version` INT(11) UNSIGNED DEFAULT 0,
                `scrapper_version` INT(11) UNSIGNED DEFAULT 0,
                `last_update` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
              )");
            }

            if (!$this->db->query("DESCRIBE `product_description`")) {
                $this->db->query("CREATE TABLE `product_description` (
                `sku` VARCHAR(255) NOT NULL PRIMARY KEY,
                `title` VARCHAR(255) NOT NULL,
                `thumb` VARCHAR(255),
                `short_desc` TEXT,
                `created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
              )");
            }

            if ($this->db->query("DESCRIBE `product_description`")) {
                  $this->db->query("ALTER TABLE `product_description` ADD FULLTEXT (`sku`, `title`, `short_desc`) ");
            }

            if (!$this->db->query("DESCRIBE `product_seller`")) {
                  $this->db->query("CREATE TABLE `product_seller` (
                  `product_sku` VARCHAR(255) NOT NULL ,
                  `seller_id` INT(11) UNSIGNED NOT NULL,
                  `price` DECIMAL(15,4) NOT NULL,
                  `url` VARCHAR(255) NOT NULL,
                  CONSTRAINT `fk_seller_id`
                    FOREIGN KEY (`seller_id`)
                  REFERENCES `seller`(`id`)
                   ON DELETE CASCADE
              )");
            }

            if (!$this->db->query("DESCRIBE `users`")) {
                  $this->db->query("CREATE TABLE users (
                      id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
                      username VARCHAR(50) NOT NULL UNIQUE,
                      password VARCHAR(255) NOT NULL,
                      created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                  )");
            }
            if ($this->db->query("DESCRIBE `users`")) {
                $default_pass = password_hash(DEFAULT_USER_PASSWORD, PASSWORD_DEFAULT);
                $this->db->query("INSERT INTO `users`(`username`, `password`) VALUES ('".DEFAULT_USER_NAME."','".$default_pass."')");
            }

            $this->makeDir("logs");
            $this->makeDir("uploads");
        }

        /**
         * Metoda de dezinstalare a aplicatiei
         *
         * @return void
         */
        public function uninstall()
        {
            $this->db->query("DROP TABLE `product_description`");
            $this->db->query("DROP TABLE `product_seller`");
            $this->db->query("DROP TABLE `seller`");
            $this->db->query("DROP TABLE `users`");

            $this->delDir("logs");
            $this->delDir("uploads");
        }

        /**
         * Salveaza local un fisier incarcat printr-un formular de upload
         *
         * @param string $file identificatorul fisierului
         * @param string $dest_folder adresa destinatie a directorului
         * @return string numele fisierului
         */
        public function saveFile($file, $dest_folder)
        {
            if ($file["error"] > 0) {
                return false;
            }
            $tmpFileName = $file['tmp_name'];
            $fileName = $file['name'];
            $server_path = $_SERVER['DOCUMENT_ROOT'].'/'.UPLOADS_FOLDER.$dest_folder.$fileName;
            $server_url = $fileName;
            move_uploaded_file($tmpFileName, $server_path);
            return $server_url;
        }

        /**
         * Creeaza un director
         *
         * @param  string $path adresa locala a directorului
         * @return bool TRUE|FALSE - valoarea de success a operatiei
         */
        public function makeDir($path)
        {
            $ret = mkdir($path); // use @mkdir if you want to suppress warnings/errors
            return $ret === true || is_dir($path);
        }

        /**
         * Sterge un fisier
         *
         * @param  string $dir adresa locala a fisierului
         * @return bool TRUE|FALSE - valoarea de success a operatiei
         */
        public function delFile($path)
        {
            return unlink($_SERVER['DOCUMENT_ROOT'].'/'.$path);
        }

        /**
         * Sterge un director
         *
         * @param  string $dir adresa locala a directorului
         * @return bool TRUE|FALSE - valoarea de success a operatiei
         */
        public function delDir($dir)
        {
            if (!file_exists($dir)) {
                return true;
            }

            if (!is_dir($dir)) {
                return unlink($dir);
            }

            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') {
                    continue;
                }

                if (!$this->delDir($dir . DIRECTORY_SEPARATOR . $item)) {
                    return false;
                }
            }
            return rmdir($dir);
        }

        /**
         * Verifica validitatea unui fisier
         *
         * @param  string $file adresa locala a fisierului
         * @return bool TRUE|FALSE
         */
        public function fileExist($file)
        {
            if (isset($file) && $file["size"] && !$file["error"]) {
                return true;
            }
            return false;
        }

        /**
         * Verifica daca un fisier este imagine de tipul JPEG, GIF sau PNG
         *
         * @param  string $file adresa locala a fisierului
         * @return bool TRUE|FALSE
         */
        public function fileIsImage($file)
        {
            $type = $file['type'];
            if ($type == 'image/jpeg' || $type == 'image/gif' || $type == 'image/png') {
                return true;
            }
            return false;
        }

        /**
         * Verifica daca un fisier este de tipul CSV
         *
         * @param  string $file adresa locala a fisierului
         * @return bool TRUE|FALSE
         */
        public function fileIsCSV($file)
        {
            $type = $file['type'];
            if ($type == 'text/csv') {
                return true;
            }
            return false;
        }

        /**
         * Metoda statica
         * Verifica daca o adresa URL reprezinta o imagine
         *
         * @param  string $url adresa URL a imaginii
         * @return bool TRUE|FALSE
         */
        public static function urlIsImage($url)
        {
            try {
                if (is_array(getimagesize($url))) {
                    return true;
                }
            } catch (Exception $e) {
                return false;
            }
            return false;
        }

        /**
         * Proceseaza si salveaza o imagine aflata la o adresa URL
         *
         * @param  string $url adresa imaginii
         * @param  string $dest adresa locala unde se doreste salvarea imaginii ce contine la coada numele fisierului + extensia
         * @return string numele fisierului
         */
        public static function saveImageFromURL($url, $dest)
        {
            $filename = basename($url);
            $img = file_get_contents($url);
            $im = imagecreatefromstring($img);
            $width_orig = imagesx($im);
            $height_orig = imagesy($im);
            $width = IMAGE_MAX_WIDTH;
            $height = IMAGE_MAX_HEIGHT;

            $ratio_orig = $width_orig/$height_orig;
            if ($width/$height > $ratio_orig) {
                $width = $height*$ratio_orig;
            } else {
                $height = $width/$ratio_orig;
            }

            $thumb = imagecreatetruecolor($width, $height);
            imagecopyresized($thumb, $im, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
            imagejpeg($thumb, $dest.$filename); //save image as jpg
            imagedestroy($thumb);
            imagedestroy($im);

            return $filename;
        }

        /**
         * Scrie un mesaj in fisierul log si creeaza directorul/fisierul de log daca acestea nu exista
         *
         * @param  string $log_msg continutul mesajului
         * @return void
         */
        public static function log($log_msg){
            $log_filename = LOGS_FOLDER_NAME;
            if (!file_exists($log_filename))
            {
                /* creeaza directorul in caz ca nu exista */
                mkdir($log_filename, 0777, true);
            }
            $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.log';
            file_put_contents($log_file_data, $date = date('H:i:s', time()).' - '.$log_msg . "\n", FILE_APPEND);
        }
    }
endif;
