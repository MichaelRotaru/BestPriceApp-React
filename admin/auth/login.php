<?php
chdir("../");
require_once('config.php');
/* Initializeaza sesiunea */
session_start();

/* Verifica daca utilizatorul este autentificat si in caz de adevar este redirectionat catre pagina de home */
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location:".SITE_URL."app.php");
    exit;
}

/* Incarca dependintele */
require_once('include/db.php');
require_once('include/notice.php');

/* Defineste variabilele si le initializeaza cu valori goale */
$username = $password = "";
$username_err = $password_err = "";

/* Proceseaza formularul */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /* Valideaza numele de utilozator */
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }

    /* Valideaza parola */
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty($username_err) && empty($password_err)) {
        $data	=	array('username'=>$_POST["username"]);
        $checkUser = $db->get('users', $data);

        if ($checkUser) {
            if (password_verify($password, $checkUser[0]['password'])) {
                /* Parola este corecta. Incepe sesiunea */
                session_start();

                /* Stocheaza datele in sesiune */
                $_SESSION["loggedin"] = true;
                $_SESSION["username"] = $username;

                /* Redirectioneaza catre pagina de home */
                header("location:".SITE_URL."app.php");
            } else {
                $password_err = "Parola introdusa nu este valida.";
            }
        } else {
            $username_err = "Nu a fost gasit nici un cont asociat numelui de utilizator.";
        }
    }
}
require_once('components/header.php');
?>
  <body>
    <header>
      <div class="container mt-2">
        <?php
          $notice->display();
        ?>
      </div>
    </header>
    <div class="container auth-cointainer login">
      <div class="card">
        <div class="card-body">
          <div class="wrapper">
            <h2>Autentificare</h2>
            <p>Va rugam introduceti datele de logare.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
              <form action="app.php" method="post">
                <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                  <label>Nume utilizator</label>
                  <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                  <span class="help-block"><?php echo $username_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                  <label>Parola</label>
                  <input type="password" name="password" class="form-control">
                  <span class="help-block"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group">
                  <input type="submit" class="btn btn-primary" value="Autentifica-te!">
                </div>
              </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
