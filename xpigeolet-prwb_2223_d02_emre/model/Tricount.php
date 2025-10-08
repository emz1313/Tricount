<?php

require_once 'framework/Model.php';
require_once "User.php";

class Tricount extends Model
{

    private $id;
    private $title;
    private $description;
    private $created_at;
    private $creator;

    public function __construct($title, $description, $created_at, $creator, $id = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->created_at = $created_at;
        $this->creator = $creator;
    }


    public function add(): Tricount
    {

        // Use a prepared statement to insert data into the tricounts table
        self::execute(
            "INSERT INTO tricounts (title, description, created_at, creator)
                         VALUES (:title, :description, NOW(), :creator)",
            array(
                "title" => $this->title,
                "description" => $this->description,
                "creator" => $this->creator
            )
        );

        $this->id = $this->lastInsertId();

        return $this;
    }

    public function update()
    {
        // Prepare the SQL statement
        self::execute(
            "UPDATE tricounts 
                        SET title = :title,
                        description = :description
                        WHERE id = :tricount_id",
            array(
                "tricount_id" => $this->getId(),
                "title" => $this->getTitle(),
                "description" => $this->getDescription()
            )
        );

        return $this;
    }

    public function delete($tricount)
    {
        // delete all repartition template item
        $repartition_template = repartition_template::get_all_repartition_template_of_the_tricount($tricount->getId());

        if ($repartition_template) {
            foreach ($repartition_template as $repartition) {
                var_dump($repartition->getId());
                Repartition_template_item::delete_repartition_template_item($repartition);
            }
        }

        Repartition_template::delete_repartition_template($tricount->getId());

        // delete all operations
        $operations = Operation::get_operation_by_tricount($tricount->getId());
        foreach ($operations as $operationToDelete) {
            Operation::delete_operation($operationToDelete);
        }

        // delete all subs
        $subs = Tricount::get_all_sub_to_a_tricount($tricount->getId());
        foreach ($subs as $sub) {
            $subToDelete = Subscription::get_subscriptions_by_user_and_tricount($tricount, $sub);
            $subToDelete->delete();
        }

        // delete the tricount
        self::execute("DELETE FROM tricounts where id = :id", array("id" => $tricount->getId()));
        return true;
    }


    public static function get_tricount_by_id($id)
    {
        $query = self::execute("SELECT * FROM tricounts WHERE id=:id", array("id" => $id));
        $data = $query->fetch();
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new Tricount(
                $data["title"],
                $data["description"],
                date_create($data["created_at"]),
                User::get_user_by_id($data['creator']),
                $data["id"]
            );
        }
    }


    public static function get_all_tricounts_by_userID($userID)
    {

        $query = self::execute("SELECT title, description, created_at, creator, id from tricounts where creator =:userID", array("userID" => $userID));
        $tri = $query->fetchAll();
        $listeTricounts = [];
        foreach ($tri as $data) {
            $listeTricounts[] =
                new Tricount(
                    $data['title'],
                    $data['description'],
                    $data['created_at'],
                    User::get_user_by_id($data['creator']),
                    $data['id']
                );
        }

        return $listeTricounts;
    }




    public static function get_all_sub_tricount_by_user($userID)
    {
        $query = self::execute("SELECT * FROM `tricounts` INNER JOIN subscriptions ON tricounts.id = subscriptions.tricount WHERE subscriptions.user = :userID ", array("userID" => $userID));
        $tri = $query->fetchAll();
        $listeTricounts = [];
        foreach ($tri as $data) {
            $listeTricounts[] = new Tricount(
                $data['title'],
                $data['description'],
                $data['created_at'],
                User::get_user_by_id($data['creator']),
                $data['id']
            );
        }
        return $listeTricounts;
    }

    public static function get_all_sub_to_a_tricount($tricount)
    {
        $query = self::execute("SELECT * FROM subscriptions WHERE subscriptions.tricount = :tricount", array("tricount" => $tricount));
        $data = $query->fetchAll();
        $users = [];
        foreach ($data as $row) {
            $users[] = User::get_user_by_id($row['user']);
        }
        return $users;
    }

    public static function get_all_sub_json($tricountId)
    {
        $query = self::execute("SELECT * FROM subscriptions WHERE subscriptions.tricount = :tricount", array("tricount" => $tricountId));
        $data = $query ? $query->fetchAll() : [];
        $users = [];
        foreach ($data as $row) {
            $user = User::get_user_by_id($row['user']);
            $users[] = array(
                'id' => $user->getId(),
                'fullname' => $user->getFullname()
            );
        }
        return json_encode($users);
    }


    public static function addSubscriber($user_id, $tricount_id)
    {
        $user = User::get_user_by_id($user_id);
        $tricount = Tricount::get_tricount_by_id($tricount_id);
        if (!$user) {
            throw new Exception("User does not exist");
        }
        if (!$tricount) {
            throw new Exception("Tricount does not exist");
        }
        $query = self::execute("SELECT * FROM subscribers WHERE user = :user_id AND tricount = :tricount_id", array(':user_id' => $user_id, ':tricount_id' => $tricount_id));
        $data = $query->fetch();
        if ($query->rowCount() > 0) {
            throw new Exception("User is already a subscriber to this Tricount");
        }
        $query = self::execute("INSERT INTO subscribers (user, tricount) VALUES (:user_id, :tricount_id)", array(':user_id' => $user_id, ':tricount_id' => $tricount_id));
    }

    public static function get_all_amount_initiated($user, $tricount)
    {
        $query = self::execute(
            "SELECT SUM(amount)
                                FROM operations
                                WHERE initiator = :userID
                                AND tricount = :tricountID",
            array(
                "userID" => $user->getId(),
                "tricountID" => $tricount->getId()
            )
        );
        $data = $query->fetch();
        return $data[0];
    }

    public static function isThereAnInitiatedOperationByUser($user, $tricount)
    {
        $query = self::execute(
            "SELECT initiator FROM operations
            JOIN tricounts 
            ON operations.tricount = tricounts.id
            WHERE tricounts.id = :tricountID 
            AND operations.initiator = :userID",
            array(
                "userID" => $user->getId(),
                "tricountID" => $tricount->getId()
            )
        );

        if ($query->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function get_all_tricounts_by_user($userID)
    {
        $query = self::execute("SELECT * FROM `tricounts` WHERE `creator` = :userID", array("userID" => $userID));
        $tricounts = array();
        while ($data = $query->fetch()) {
            $tricounts[] = new Tricount(
                $data['title'],
                $data['description'],
                $data['created_at'],
                User::get_user_by_id($data['creator']),
                $data['id']
            );
        }
        return $tricounts;
    }
    public static function get_tricount_title_by_user($title, $user_id)
    {
        $user = User::get_user_by_id($user_id);
        $query = self::execute(
            "SELECT COUNT(*) FROM `tricounts` 
                            WHERE (creator = :userID OR id IN 
                            (SELECT tricount FROM `subscriptions` WHERE `user` = :userID))
                            AND title = :title",
            array("userID" => $user->getId(), "title" => $title)
        );

        $count = $query->fetchColumn();

        return $count > 0;
    }

    public function validate_title_add($title, $user)
    {
        $list = Tricount::get_all_sub_tricount_by_user($user->getId());

        $errors = array();
        if (empty($title) || !is_string($title) || strlen($title) < 3) {
            $errors[] = "Your tricount title must be a string with at least 3 characters";
        }
        foreach ($list as $tri) {
            if (strtolower($title) === strtolower($tri->getTitle())) {
                $errors[] = "You already have a tricount with the same title";
            }
        }
        return $errors;
    }

    public function validate_title_edit($title)
    {
        $errors = array();
        if (empty($title) || !is_string($title) || strlen($title) < 3) {
            $errors[] = "Your tricount title must be a string with at least 3 characters";
        }

        return $errors;
    }


    public function validate_description($description)
    {
        $errors = array();
        if (!empty($description) && preg_match('/^[a-zA-Z0-9\s]+$/', $description) && strlen(($description)) < 3) {
            $errors[] = "Your description must be at least 3 characters long and can only contain alphabets, numbers and whitespaces or empty.";
        }
        return $errors;
    }



    public static function get_tricount_by_title_and_subscription($new_title, $userID)
    {

        $query = self::execute(
            "SELECT tricounts.title
                                FROM tricounts 
                                INNER JOIN subscriptions ON tricounts.id = subscriptions.tricount
                                WHERE tricounts.title = :title
                                AND subscriptions.user = :userID",
            array(
                "userID" => $userID,
                "title" => $new_title
            )
        );

        $data = $query->fetch();

        return $data;
    }


    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description == null ? "" : $this->description;
    }

    public function getCreated_at()
    {
        return $this->created_at;
    }
    public function getCreator()
    {
        return $this->creator;
    }
    public function setTitle($title)
    {
        $this->title = $title;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }



    public function getSubs($tricount)
    {

        $query = self::execute("SELECT COUNT(user) FROM subscriptions 
                WHERE tricount = :tricount", array("tricount" => $tricount));
        $data = $query->fetch();
        return $data['COUNT(user)'];
    }

    public function getOps($tricount)
    {
        $query = self::execute("SELECT COUNT(id) FROM operations 
                            WHERE tricount = :tricount", array("tricount" => $tricount));
        $data = $query->fetch();
        return $data['COUNT(id)'];
    }

    // méthode qui calcule le nombre d'argent dépensé par un utilisateur dans le tricount regardé
    public  function get_users_expense($tricount)
    {

        $query = self::execute("SELECT SUM(amount) FROM operations WHERE tricount =: tricount", array("tricount" => $tricount));
        $data = $query->fetch();

        return $data['SUM(amount)'];
    }
}
