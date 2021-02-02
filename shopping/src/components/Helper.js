/**
 * Functie globala
 * Convertirea unui string la tipul Number
 *
 * @param string number: numarul ce se doreste a fi convertit
 * @param int n: numarul de zecimale
 * @param int x: lungimea seciunii
 * @return Number: rezultatul conversiei
 */
function format(number, n, x) {
    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
    return Number(number).toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
};

/**
 * Functie globala
 * Convertirea unui string la tipul Number adaugarea tipului de moneda si convertirea la loc in string
 *
 * @param string number: numarul ce se doreste a fi convertit
 * @return Number: rezultatul conversiei
 */
export function formatCurrency(number){
  return format(number,2)+" RON";
}

/**
 * Functie globala
 * Selectarea unui parametru dintr-un string de forma URL GET
 *
 * @param string str: sirul de caractere in care se face cautarea
 * @param string name: numale parametrului
 * @return object: numele parametrului insotita de valoarea sa
 */
export function getParameterByName(str, name){
  name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
  var regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
  var results = regex.exec(str);
  return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
}
