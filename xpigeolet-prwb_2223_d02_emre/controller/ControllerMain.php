<?php

require_once 'model/User.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

class ControllerMain extends Controller
{
    public function index(): void
    {
        if ($this->user_logged()) {
            $this->redirect("tricount", "index");
        } else {
            $email = '';
            $password = '';
            $errors = [];

            if (isset($_POST['email']) && isset($_POST['password'])) {
                $email = $_POST['email'];
                $password = $_POST['password'];

                $errors = User::validate_login($email, $password);

                if (empty($errors)) {
                    $this->log_user(User::get_user_by_mail($email));
                }
            }
            (new View("index"))->show(["email" => $email, "password" => $password, "errors" => $errors]);
        }
    }

    public function login(): void
    {
        if ($this->user_logged()) {
            $this->redirect("tricount", "index");
        } else {
            $email = '';
            $password = '';
            $errors = [];

            if (isset($_POST['email']) && isset($_POST['password'])) {
                $email = $_POST['email'];
                $password = $_POST['password'];

                $errors = User::validate_login($email, $password);

                if (empty($errors)) {
                    $this->log_user(User::get_user_by_mail($email));
                }
            }
            (new View("index"))->show(["email" => $email, "password" => $password, "errors" => $errors]);
        }
    }

    public function signup(): void
    {
        $mail = '';
        $full_name = '';
        $iban = '';
        $password = '';
        $passwordConfirm = '';
        $errors = [];

        if (
            isset($_POST['mail']) &&
            isset($_POST['full_name']) &&
            isset($_POST['iban']) &&
            isset($_POST['password']) &&
            isset($_POST['password_confirm'])
        ) {

            $mail = trim($_POST['mail']);
            $full_name = trim($_POST['full_name']);
            $iban = trim($_POST['iban']);
            $password = $_POST['password'];
            $passwordConfirm = $_POST['password_confirm'];
            $hashed_password = Tools::my_hash($password);


            $user = new User(
                $mail,
                $hashed_password,
                $full_name,
                $iban,
                $role = null
            );

            $errors = $user->validate_email($mail);
            $errors = array_merge($errors, $user->validate_iban($iban));
            $errors = array_merge($errors, User::validate_passwords($password, $passwordConfirm));
            $errors = array_merge($errors, $user->validate_fullName($full_name));

            if (count($errors) == 0) {
                $user->add(); //sauve l'utilisateur

                $this->log_user($user);
            }
        }
        (new View("signup"))->show(array(
            "mail" => $mail,
            "full_name" => $full_name,
            "iban" => $iban,
            "password" => $password,
            "password_confirm" => $passwordConfirm,
            "errors" => $errors
        ));
    }
}
