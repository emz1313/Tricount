<?php

require_once 'framework/Model.php';

class User extends Model
{

    private $id;
    private $mail;
    private $hashed_password;
    private $full_name;
    private $role;
    private $iban;

    public function __construct(
        $mail,
        $hashed_password,
        $full_name,
        $iban,
        $role = "user",
        $id = NULL
    ) {


        $this->mail = $mail;
        $this->hashed_password = $hashed_password;
        $this->full_name = $full_name;
        $this->iban = $iban;
        $this->role = $role;
        $this->id = $id;
    }

    public static function check_pass_before_change($user, $oldPass, $newPass, $newPass_confirm)
    {
        $errors = [];
        if (self::check_password($oldPass, $user->hashed_password)) {
            $errors = User::validate_passwords($newPass, $newPass_confirm);
        } else {
            $errors[] = "Wrong password. Please try again.";
        }

        return $errors;
    }
    public function update_password($password)
    {
        $hashed_password = Tools::my_hash($password);
        self::execute(
            "UPDATE users SET hashed_password=:hashed_password WHERE mail=:mail",
            array("hashed_password" => $hashed_password, "mail" => $this->mail)
        );
    }


    // fonction qui sauve ou maj un utilisateur 
    public function add(): User
    {

        self::execute(
            "INSERT INTO users(mail,hashed_password,full_name,iban)
                             VALUES(:mail,:hashed_password,:full_name,:iban)",

            [
                "mail" => $this->mail,
                "hashed_password" => $this->hashed_password,
                "iban" => $this->iban,
                "full_name" => $this->full_name
            ]
        );
        $this->id = self::lastInsertId();
        return $this;
    }

    public function update(): User
    {
        if (!is_null($this->getId())) {
            self::execute(
                "UPDATE users SET 
                                mail = :mail,
                                hashed_password=:hashed_password,
                                full_name=:full_name,
                                iban=:iban,
                                role=:role
                                WHERE id=:id",
                array(
                    "mail" => $this->mail,
                    "hashed_password" => $this->hashed_password,
                    "full_name" => $this->full_name,
                    "iban" => $this->iban,
                    "role" => $this->role,
                    "id" => $this->id
                )
            );
        }
        return $this;
    }





    public static function get_user_by_mail($email)
    {
        $query = self::execute(
            "SELECT * FROM Users WHERE Mail = :email",
            array("email" => $email)
        );

        $data = $query->fetch();

        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new User(
                $data['mail'],
                $data['hashed_password'],
                $data['full_name'],
                $data['iban'],
                $data['role'],
                $data['id']
            );
        }
    }

    public static function validate_login(string $email, string $password): array
    {
        $errors = [];
        $email = User::get_user_by_mail($email);
        if ($email) {
            if (!self::check_password($password, $email->hashed_password)) {
                $errors[] = "Wrong password. Please try again.";
            }
        } else {
            $errors[] = "Can't find a member with the email '$email'. Please sign up.";
        }
        return $errors;
    }


    public function validate_email($email)
    {
        $errors = array();
        if (!(isset($email) && is_string($email) && strlen($email) > 0)) {
            $errors[] = "An email address is required.";
        }
        if (!(filter_var($email, FILTER_VALIDATE_EMAIL))) {
            $errors[] = "Please enter a valid email address";
        }
        $user = self::get_user_by_mail($email);
        if ($user) {
            if (!is_null($this->id) && $this->id == $user->getId()) {
                return $errors;
            }
            $errors[] = "A user with the following email, already exists";
        }
        return $errors;
    }

    private static function validate_password(string $password): array
    {
        $errors = [];
        if (strlen($password) < 8 || strlen($password) > 16) {
            $errors[] = "Password length must be between 8 and 16.";
        }
        if (!((preg_match("/[A-Z]/", $password)) && preg_match("/\d/", $password) && preg_match("/['\";:,.\/?!\\-]/", $password))) {
            $errors[] = "Password must contain one uppercase letter, one number and one punctuation mark.";
        }
        return $errors;
    }

    public static function validate_passwords(string $password, string $password_confirm): array
    {
        $errors = User::validate_password($password);
        if ($password != $password_confirm) {
            $errors[] = "You have to enter twice the same password.";
        }
        return $errors;
    }

    private static function check_password(string $clear_password, string $hash): bool
    {
        return $hash === Tools::my_hash($clear_password);
    }

    public static function validate_unicity(string $email): array
    {
        $errors = [];
        $member = self::get_user_by_mail($email);
        if ($member) {
            $errors[] = "This user already exists.";
        }
        return $errors;
    }

    public function validate_fullname($full_name)
    {
        $errors = array();
        if (!(isset($full_name) && is_string($full_name) && strlen($full_name) > 0)) {
            $errors[] = "Please enter your full name";
        }
        if (!(isset($full_name) && is_string($full_name) && strlen($full_name) >= 3)) {
            $errors[] = "Your full name must be at least 3 characters";
        }
        return $errors;
    }
    public function validate_iban($iban)
    {
        if (!empty($iban) && !preg_match('/^[0-9]{14}$/', $iban)) {
            return array('L\'IBAN doit soit être vide ou contenir 14 chiffres uniquement
                exemple : 12345678912345');
        }
        return array();
    }


    public function isSub($userID, $tricountID)
    {
        $query = self::execute(
            "SELECT user FROM subscriptions 
                                WHERE user = :userID 
                                AND tricount = :tricountID",
            array(
                "userID" => $userID,
                "tricountID" => $tricountID
            )
        );

        return $query->rowCount() > 0;
    }




    public static function get_user_by_id($id)
    {
        $query = self::execute(
            "SELECT * FROM users WHERE id = :id",
            array("id" => $id)
        );
        $data = $query->fetch();
        if ($query->rowCount() == 0) {
            return null;
        } else {
            return new User(
                $data['mail'],
                $data['hashed_password'],
                $data['full_name'],
                $data['iban'],
                $data['role'],
                $data['id']
            );
        }
    }
    public function get_all_users()
    {
        $query = self::execute(
            "SELECT * FROM users WHERE id != :id",
            array("id" => $this->id)
        );
        $data = $query->fetchAll();
        $users = [];
        foreach ($data as $row) {
            $users[] = new User(
                $row['mail'],
                $row['hashed_password'],
                $row['full_name'],
                $row['iban'],
                $row['role'],
                $row['id']
            );
        }
        return $users;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFullname(): string
    {
        return $this->full_name;
    }

    public function getEmail()
    {
        return $this->mail;
    }
    public function getTricounts()
    {
        return Tricount::get_all_tricounts_by_userID($this);
    }
    public function getIban()
    {
        return $this->iban;
    }


    public function setPassword($password)
    {
        $this->hashed_password = $password;
    }
    public function setEmail($email)
    {
        $this->mail = $email;
    }
    public function setFullname($name)
    {
        $this->full_name = $name;
    }
    public function setIban($iban)
    {
        $this->iban = $iban;
    }
}
