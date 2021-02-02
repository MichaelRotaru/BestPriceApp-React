<?php
/* Verificare de securitate a clasei */
if (! class_exists('db')) :
    class db
    {
        /**
         * obiect de conectare PDO
         * @var \PDO
         */
        protected $pdo;

        public function __construct(\PDO $pdo)
        {
            $this->pdo = $pdo;
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }

        /**
         * Returneaza obiectul PDO
         */
        public function getPdo()
        {
            return $this->pdo;
        }

        public function query($sql)
        {
            try {
                $query = $this->pdo->query($sql);
                return true;
            } catch (PDOException $e) {
                return false;
            }
        }

        /**
         * Returneaza id-ul ultimei inregistrari din sectiune
         *
         * @param  string $param Numele secventei obiect pentru care se doreste returnarea ID-ului
         * @return string ID-ul ultimei inregistrari din baza de date
         */
        public function lastInsertId($param = null)
        {
            return $this->pdo->lastInsertId($param);
        }

        /**
         * Manager pentru operatiile dinamice CRUD
         *
         * Create:  insertTableName($arrData)
         * Retrieve: getTableNameByFieldName($value)
         * Update: updateTableNameByFieldName($value, $arrUpdate)
         * Delete: deleteTableNameByFieldName($value)
         *
         * @param  string     $function
         * @param  array      $arrParams
         * @return array|bool
         */
        public function __call($function, array $params = array())
        {
            if (! preg_match('/^(get|update|insert|delete)(.*)$/', $function, $matches)) {
                throw new \BadMethodCallException($function.' is an invalid method Call');
            }

            if ('insert' == $matches[1]) {
                if (! is_array($params[0]) || count($params[0]) < 1) {
                    throw new \InvalidArgumentException('insert values must be an array');
                }
                return $this->insert($this->camelCaseToUnderscore($matches[2]), $params[0]);
            }

            list($tableName, $fieldName) = explode('By', $matches[2], 2);
            if (! isset($tableName, $fieldName)) {
                throw new \BadMethodCallException($function.' is an invalid method Call');
            }

            if ('update' == $matches[1]) {
                if (! is_array($params[1]) || count($params[1]) < 1) {
                    throw new \InvalidArgumentException('update fields must be an array');
                }
                return $this->update(
                    $this->camelCaseToUnderscore($tableName),
                    $params[1],
                    array($this->camelCaseToUnderscore($fieldName) => $params[0])
                );
            }

            return $this->{$matches[1]}(
                $this->camelCaseToUnderscore($tableName),
                array($this->camelCaseToUnderscore($fieldName) => $params[0])
            );
        }

        /**
         * Metoda de executare a operatiei SELECT in baza de date
         *
         * @param  string     $tableName numele tabelului
         * @param  array      $where (cheia este numele campului)
         * @return array|bool (lista asociativa pentru inregistrarile singulare, lista multidimensionala pentru inregistrarile multiple)
         */
        public function get($tableName, $whereAnd  =   array(), $whereOr   =   array(), $whereLike =   array())
        {
            $cond   =   '';
            $s=1;
            $params =   array();
            foreach ($whereAnd as $key => $val) {
                if (!strlen($cond)) {
                    $cond   .=  $key." = :a".$s;
                } else {
                    $cond   .=  " And ".$key." = :a".$s;
                }
                $params['a'.$s] = $val;
                $s++;
            }
            foreach ($whereOr as $key => $val) {
                if (!strlen($cond)) {
                    $cond   .=  $key." = :a".$s;
                } else {
                    $cond   .=  " OR ".$key." = :a".$s;
                }
                $params['a'.$s] = $val;
                $s++;
            }
            foreach ($whereLike as $key => $val) {
                if (!strlen($cond)) {
                    $cond   .=  $key." LIKE CONCAT('%', :a".$s.", '%')";
                } else {
                    $cond   .=  " OR ".$key." LIKE CONCAT('%', :a".$s.", '%')";
                }
                $params['a'.$s] = $val;
                $s++;
            }
            $stmt = $this->pdo->prepare("SELECT  $tableName.* FROM $tableName WHERE ".$cond);
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

        public function getAllRecords($tableName, $fields='*', $cond='', $orderBy='', $limit='')
        {
            $stmt = $this->pdo->prepare("SELECT $fields FROM $tableName WHERE 1 ".$cond." ".$orderBy." ".$limit);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows;
        }

        public function getQueryCount($tableName, $field, $cond='')
        {
            $stmt = $this->pdo->prepare("SELECT count($field) as total FROM $tableName WHERE 1 ".$cond);
            try {
                $stmt->execute();
                $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (! $res || count($res) != 1) {
                    return $res;
                }
                return $res;
            } catch (\PDOException $e) {
                throw new \RuntimeException("[".$e->getCode()."] : ". $e->getMessage());
            }
        }

        /**
         * Metoda Update
         *
         * @param  string $tableName
         * @param  array  $set       (lista asociativa unde cheia este numele campului)
         * @param  array  $where     (lista asociativa unde cheia este numele campului)
         * @return int    numarul de linii afectate
         */
        public function update($tableName, array $set, array $where)
        {
            $arrSet = array_map(
                function ($value) {
                    return $value . '=:' . $value;
                },
                array_keys($set)
             );

            $stmt = $this->pdo->prepare(
                "UPDATE $tableName SET ". implode(',', $arrSet).' WHERE '. key($where). '=:'. key($where) . 'Field'
             );

            foreach ($set as $field => $value) {
                $stmt->bindValue(':'.$field, $value);
            }
            $stmt->bindValue(':'.key($where) . 'Field', current($where));
            try {
                $stmt->execute();

                return $stmt->rowCount();
            } catch (\PDOException $e) {
                throw new \RuntimeException("[".$e->getCode()."] : ". $e->getMessage());
            }
        }

        /**
         * Metoda Delete
         *
         * @param  string $tableName
         * @param  array  $where     (lista asociativa unde cheia este numele campului)
         * @return int    numarul liniilor afectate
         */
        public function delete($tableName, array $where)
        {
            $stmt = $this->pdo->prepare("DELETE FROM $tableName WHERE ".key($where) . ' = ?');
            try {
                $stmt->execute(array(current($where)));

                return $stmt->rowCount();
            } catch (\PDOException $e) {
                throw new \RuntimeException("[".$e->getCode()."] : ". $e->getMessage());
            }
        }


        /**
         * Metoda Insert
         *
         * @param  string $tableName
         * @param  array  $arrData   (datele ce trebuie introduse, lista asociativa unde cheia este numele campului)
         * @return int    numarul liniilor afectate
         */
        public function insert($tableName, array $data)
        {
            $stmt = $this->pdo->prepare(
                "INSERT INTO $tableName (".implode(',', array_keys($data)).")
                VALUES (".implode(',', array_fill(0, count($data), '?')).")"
            );
            try {
                $stmt->execute(array_values($data));
                return $stmt->rowCount();
            } catch (\PDOException $e) {
                throw new \RuntimeException("[".$e->getCode()."] : ". $e->getMessage());
            }
        }

        /**
         * Modifica tipul unui sir de caractere la caractere mici
         *
         * @param  string $string camelCase string
         * @return string underscore_space string
         */
        protected function camelCaseToUnderscore($string)
        {
            return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
        }
    }
endif;

/* Initializeaza obiectul db */
$dsn	= 	"mysql:dbname=".DB_DATABASE.";host=".DB_HOSTNAME."";
$pdo	=	"";
try {
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$db =	new db($pdo);
