<?php

require_once 'framework/Model.php';
require_once 'model/User.php';
require_once 'model/Tricount.php';


class Operation extends Model
{

    private $id;
    private $title;
    private $tricount;
    private $amount;
    private $operation_date;
    private $initiator;
    private $created_at;

    public function __construct(
        $title,
        $tricount,
        $amount,
        $operation_date,
        $initiator,
        $created_at,
        $id = null
    ) {

        $this->title = $title;
        $this->tricount = $tricount;
        $this->amount = $amount;
        $this->operation_date = $operation_date;
        $this->initiator = $initiator;
        $this->created_at = $created_at;
        $this->id = $id;
    }

    public function validate_title()
    {
        $errors = [];
        if (!(isset($this->title) && is_string($this->title) && strlen($this->title) > 0))
            $errors[] = "Title is required.";
        if (!(isset($this->title) && is_string($this->title) && strlen($this->title) >= 3))
            $errors[] = "Title length must be at least 3 characters";
        return $errors;
    }
    public function validate_amount()
    {
        $errors = [];

        if (!(isset($this->amount) && is_numeric($this->amount) && $this->amount > 0)) {
            $errors[] = "The amount have to be numeric, positive, and have a value";
        }

        return $errors;
    }


    public function validate()
    {
        $errors = $this->validate_dates();
        $errors = array_merge($errors, $this->validate_title());
        $errors = array_merge($errors, $this->validate_amount());
        return $errors;
    }

    public function validate_dates()
    {
        $errors = [];
        if (empty($this->operation_date)) {
            $errors[] = "Please specify a start date for your operation";
        }
        return $errors;
    }

    public static function delete_operation($operation)
    {

        self::delete_all_repartition($operation);
        self::execute("DELETE FROM operations where id = :operation", array("operation" => $operation->getId()));

        return true;
    }

    public static function delete_all_repartition($operation)
    {
        self::execute("DELETE FROM `repartitions` WHERE operation = :operation", array("operation" => $operation->getId()));
    }

    public function add()
    {
        self::execute(
            "INSERT INTO operations
         (title, tricount, amount, operation_date, initiator, created_at)
        VALUES 
        (:title, :tricount, :amount, :operation_date, :initiator, :created_at)",
            array(
                "title" => $this->title,
                "tricount" => $this->tricount->getId(),
                "amount" => $this->amount,
                "operation_date" => $this->operation_date,
                "initiator" => $this->initiator,
                "created_at" => $this->created_at
            )
        );
        $this->id = self::lastInsertId();

        return $this;
    }

    public function update()
    {

        if (!is_null($this->getId())) {
            self::execute(
                "UPDATE operations SET 
                            title = :title,
                            tricount = :tricount,
                            amount = :amount,
                            operation_date = :operation_date,
                            initiator = :initiator,
                            created_at = :created_at
                            WHERE id = :id",
                array(
                    "title" => $this->title,
                    "tricount" => $this->tricount->getId(),
                    "amount" => $this->amount,
                    "operation_date" => $this->operation_date,
                    "initiator" => $this->initiator->getId(),
                    "created_at" => $this->created_at,
                    "id" => $this->getId()
                )
            );
        }
        return $this;
    }

    public static function get_operation_by_id($id): ?Operation
    {
        $query = self::execute("SELECT * FROM operations WHERE id =:id", array("id" => $id));
        $data = $query->fetch();

        if ($data === false) {
            return null;
        }

        return new Operation(
            $data["title"],
            Tricount::get_tricount_by_id($data["tricount"]),
            $data["amount"],
            $data['operation_date'],
            User::get_user_by_id($data['initiator']),
            $data["created_at"],
            $data["id"]
        );
    }

    public static function get_tricount_by_id($id): Tricount
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
    public static function get_all_operations()
    {

        $query = self::execute("SELECT * From operations", array());
        $data = $query->fetchAll();
        $results = [];

        foreach ($data as $row) {
            $results[] = new Operation(

                $row["title"],
                $row["tricount"],
                $row["amount"],
                $row["operation_date"],
                $row["initiator"],
                $row["created_at"],
                $row["id"]
            );
        }
        return $results;
    }

    public static function getUsersOperationsByTricount($tricount_id)
    {
        $query = self::execute(
            "SELECT u.*, o.* FROM operations o
                                JOIN users u ON o.initiator = u.id
                                WHERE o.tricount_id = :tricount_id",
            array(':tricount_id' => $tricount_id)
        );
        $data = $query->fetchAll();
        $users_operations = array();
        foreach ($data as $row) {
            $user = new User(
                $row['mail'],
                $row['hashed_password'],
                $row['full_name'],
                $row['role'],
                $row['iban'],
                $row['id']
            );
            $operation = new Operation($row["title"], $row["tricount"], $row["amount"], $row["operation_date"], $row['initiator'], $row["created_at"], $row["id"]);
            $users_operations[] = array('user' => $user, 'operation' => $operation);
        }
        return $users_operations;
    }

    public static function get_operation_by_tricount($id)
    {
        $query = self::execute("SELECT * FROM operations where tricount=:id", array("id" => $id));
        $data = $query->fetchAll();

        $op = [];
        foreach ($data as $row) {
            $op[] =  new Operation(
                $row["title"],
                $row["tricount"],
                $row["amount"],
                $row["operation_date"],
                User::get_user_by_id($row['initiator']),
                $row["created_at"],
                $row["id"]
            );
        }
        return $op;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }
    public function getTricount()
    {
        $tricount = Tricount::get_tricount_by_id($this->tricount);

        return $tricount;
    }
    public function getAmount()
    {
        $amount = $this->amount;
        return $amount;
    }
    public function getOperationDate()
    {
        return $this->operation_date;
    }
    public function getInitiator()
    {
        return $this->initiator;
    }
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    public function setTitle($title)
    {
        return $this->title = $title;
    }
    public function setAmount($amount)
    {
        return $this->amount = $amount;
    }
    public function setOperationDate($operationDate)
    {
        return $this->operation_date = $operationDate;
    }
    public function setInitiator($initiator)
    {
        return $this->initiator = $initiator;
    }

    // retourne le nombre d'abonné au tricount
    public function getSubs($tricount)
    {

        $query = self::execute("SELECT COUNT(user) FROM subscriptions 
                WHERE tricount = :tricount", array("tricount" => $tricount));
        $data = $query->fetch();
        return $data['COUNT(user)'];
    }
    // retourne le nombre d'opérations d'un tricount
    public function get_number_operations($tricount)
    {
        $query = self::execute("SELECT COUNT(id) FROM operations WHERE tricount = :tricount", array("tricount" => $tricount));
        $data = $query->fetch();

        return $data['COUNT(id)'];
    }
}
