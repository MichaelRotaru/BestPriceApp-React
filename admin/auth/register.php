<?php
chdir("../");
require_once('include/application_top.php');

/* Defineste variabilele si le initializeaza cu valori goale */
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

/* Proceseaza formularul */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    /* Valideaza numele de utilizator */
    if (empty(trim($_POST["username"]))) {
        $username_err = "Va rugam introduceti un nume de utilizator.";
    } else {
        $data	=	array('username'=>$_POST["username"]);
        $checkUser = $db->get('users', $data);

        if ($checkUser) {
            $username_err = "Numele de utilizator exista deja.";
        } else {
            $username = trim($_POST["username"]);
        }
    }

    /* Valideaza parola */
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Parola trebuie sa contina cel putin 6 caractere.";
    } else {
        $password = trim($_POST["password"]);
    }

    /* Valideaza confirmarea parolei */
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Va rugam confirmati parola.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Parolele nu se potrivesc.";
        }
    }

    /* Verifica datele formularului inaite de a le introduce in baza de date */
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {
        $data	=	array('username'=>$username,'password'=>password_hash($password, PASSWORD_DEFAULT));
        $insert = $db->insert('users', $data);

        if ($insert) {
            $notice->setMessage([6], SITE_URL.'auth/login.php');
        } else {
            $notice->setMessage([101], $_SERVER['REQUEST_URI']);
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
    <div class="container">
      <div class="card">
        <div class="card-header"><strong>Creeaza cont administrator</strong> <a href="<?= SITE_URL ?>app.php" class="float-right btn btn-dark btn-sm"><i class="fa fa-fw fa-globe"></i> Inapoi la Panou</a></div>
        <div class="card-body">
          <div class="col-sm-6">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
              <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Nume utilizator</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
              </div>
              <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Parola</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block"><?php echo $password_err; ?></span>
              </div>
              <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirmare Parola</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
              </div>
              <div class="form-group">
                <p>*Toate campurile sunt obligatorii.</p>
                <input type="submit" class="btn btn-primary" value="Adauga cont">
              </div>
            </form>
          </div>
        </div>
      </div>
  </body>
</html>
