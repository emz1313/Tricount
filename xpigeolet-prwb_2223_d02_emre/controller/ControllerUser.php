<?php

require_once 'model/User.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';

class ControllerUser extends Controller
{

    public function index(): void
    {

        $this->settings();
    }

    // affichage des settings 
    public function settings(): void
    {

        $user = $this->get_user_or_redirect();


        (new View("settings"))->show(array(
            "user" => $user,
        ));
    }

    public function edit_profile()
    {
        $user = $this->get_user_or_redirect();

        $email = $user->getEmail();
        $name = $user->getFullname();
        $iban = $user->getIban();
        $errors = [];

        if ($user->getId() == $_GET['param1'] && isset($_GET['param1']) && isset($_GET['param1']) != '') {
            if (
                isset($_POST["name"]) && isset($_POST["email"])
                && isset($_POST["iban"])
            ) {

                $email = trim($_POST['email']);
                $name = Tools::sanitize($_POST['name']);
                $iban = trim($_POST['iban']);

                $errors = $user->validate_email($email);
                $errors = array_merge($errors, $user->validate_iban($iban));
                $errors = array_merge($errors, $user->validate_fullname($name));

                if (count($errors) == 0) {
                    $user->setEmail($email);
                    $user->setFullname($name);
                    $user->setIban($iban);
                    $user->update();
                    $this->redirect("user", "settings");
                }
            }
        } else {
            $this->redirect("user", "settings");
        }


        (new View("edit_profile"))->show(array(
            "user" => $user,
            "email" => $email,
            "name" => $name,
            "iban" => $iban,
            "errors" => $errors
        ));
    }

    public function edit_password()
    {
        $user = $this->get_user_or_redirect();

        $newPassword = '';
        $newPassword_confirm = '';
        $errors = [];
        if (isset($_POST["newPass"]) || isset($_POST["newPass_Confirm"])) {

            $newPassword = $_POST["newPass"];
            $newPassword_confirm = $_POST["newPass_Confirm"];

            $errors = array_merge($errors, User::validate_passwords($newPassword, $newPassword_confirm));


            if (count($errors) == 0) {
                $user->update_password($newPassword);
                $this->redirect("user", "settings");
            }
        }
        (new View("edit_password"))->show(array("user" => $user, "errors" => $errors));
    }
}
