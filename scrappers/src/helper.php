<?php
/* Elimina caracterele nedorite dintr-un sir de caractere
 * @param string $string - sirul de caractere ce se doreste modificat
 * @param string $deep - flag pentru o filtrare stricta
 * @return string  sirul de caractere rezultat
 */
function escape($string,$deep = false) {
    $string = trim($string);
    $string = str_replace(";",",",$string);
    if($deep){
      $string = preg_replace("/[^A-Za-z0-9_\-\ \.]/", '', $string);
    }
    if(strlen($string)<4 || $string == "&nbsp,"){
      $string = '';
    }
    return cleanString($string);
}

/* Converteste un sir de caractere codificate UTF-8 la formatul normal
 * @param string $text - sirul de caractere ce se doreste modificat
 * @return string  sirul de caractere rezultat
 */
function cleanString($text) {
    $utf8 = array(
        '/[áàâãªä]/u'   =>   'a',
        '/[ÁÀÂÃÄ]/u'    =>   'A',
        '/[ÍÌÎÏ]/u'     =>   'I',
        '/[íìîï]/u'     =>   'i',
        '/[éèêë]/u'     =>   'e',
        '/[ÉÈÊË]/u'     =>   'E',
        '/[óòôõºö]/u'   =>   'o',
        '/[ÓÒÔÕÖ]/u'    =>   'O',
        '/[úùûü]/u'     =>   'u',
        '/[ÚÙÛÜ]/u'     =>   'U',
        '/ç/'           =>   'c',
        '/Ç/'           =>   'C',
        '/ñ/'           =>   'n',
        '/Ñ/'           =>   'N',
        '/–/'           =>   '-', // cratima UTF-8 la cratima "normala"
        '/[’‘‹›‚]/u'    =>   ' ', // Gilimea simpla
        '/[“”«»„]/u'    =>   ' ', // Ghilimele duble
        '/ /'           =>   ' ', // Spatiu (equiv. to 0x160)
    );
    return preg_replace(array_keys($utf8), array_values($utf8), $text);
}

/* Forteaza contextul - Necesar apelurilor executate prin CRON */
$context = stream_context_create(
    array(
        "http" => array(
            "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
        )
    )
);
