<?php

require_once 'framework/Model.php';
require_once 'model/User.php';
require_once 'model/Operation.php';

class Repartition extends Model
{

    private $operation;
    private $user;
    private $weight;

    public function __construct($operation, $user, $weight)
    {
        $this->operation = $operation;
        $this->user = $user;
        $this->weight = $weight;
    }

    public static function getByOperationId($operationID)
    {
        $query = self::execute(
            "SELECT * FROM repartitions WHERE operation =:operationID",
            array("operationID" => $operationID)
        );

        $repartitions = [];
        while ($data = $query->fetch()) {
            $repartitions[] = new Repartition(
                Operation::get_operation_by_id($data['operation']),
                User::get_user_by_id($data['user']),
                $data['weight']
            );
        }
        return $repartitions;
    }


    public static function getByOperationIdAndUserId($operation, $user)
    {
        $query = self::execute(
            "SELECT * FROM repartitions 
                        WHERE operation =:operationID 
                        AND user = :userID",
            array(
                "userID" => $user,
                "operationID" => $operation
            )
        );
        $data = $query->fetch();
        if ($data) {
            return new Repartition(
                Operation::get_operation_by_id($data['operation']),
                User::get_user_by_id($data['user']),
                $data['weight']
            );
        }
        return false;
    }



    public function delete()
    {
        self::execute("DELETE FROM repartitions WHERE user =:userID", array("userID" => $this->getUser()->getId()));
    }

    public function delete_repartition($operationID)
    {
        self::execute(
            "DELETE FROM repartitions 
        WHERE operation = :operationID 
        AND user =:userID",
            array("userID" => $this->getUser()->getId(), "operationID" => $operationID)
        );
    }

    public function add()
    {
        self::execute("INSERT INTO repartitions (operation,user,weight)
                VALUES (:operation,:user,:weight)", array(
            "operation" => $this->operation,
            "user" => $this->user,
            "weight" => $this->weight
        ));
        return $this;
    }



    public function update()
    {
        self::execute(
            "UPDATE repartitions
                        SET user = :user
                            weight = :weight
                        WHERE operation = :operation",
            array(
                "user" => $this->getUser(),
                "weight" => $this->getWeight()
            )
        );

        return $this;
    }

    public function validate_weight()
    {
        $errors = [];
        if ((($this->getWeight() != NULL) && is_numeric($this->getWeight()) && ($this->getWeight()) <= 0)) {
            $errors[] = "Your are an issue with your weight";
        }
        return $errors;
    }

    public static function get_number_of_user_by_operation($operationID)
    {
        $query = self::execute(
            "SELECT COUNT(user) FROM repartitions 
                                WHERE operation = :operationID",
            array("operationID" => $operationID)
        );
        $data = $query->fetch();
        return $data['COUNT(user)'];
    }


    public static function get_users_and_weights_by_operation($operationID)
    {
        $query = self::execute("SELECT user, weight FROM repartitions
                                    WHERE operation =:operationID", array("operationID" => $operationID));
        $data = $query->fetchAll();
        $users_and_weights = array();
        foreach ($data as $row) {
            $users_and_weights[] = array(
                User::get_user_by_id($row['user']),
                $row['weight']
            );
        }
        return $users_and_weights;
    }

    public static function get_users_from_an_operation($operationID)
    {
        $query = self::execute("SELECT user FROM repartitions
                                WHERE operation =:operationID", array("operationID" => $operationID));
        $data = $query->fetchAll();
        $liste_users = [];

        foreach ($data as $row) {
            $liste_users[] = User::get_user_by_id($row['user']);
        }

        return $liste_users;
    }

    public static function get_weight_by_operation($operationID, $userID)
    {
        $query = self::execute("SELECT weight FROM repartitions
                                    WHERE operation =:operationID and user =:userID", array("userID" => $userID, "operationID" => $operationID));
        $data = $query->fetch();

        if (is_array($data)) {
            return $data['weight'];
        } else {
            return false;
        }
    }

    public static function isAnyDepenseInTheTricount($user, $tricount)
    {
        $query = self::execute(
            "SELECT repartitions.weight
                                FROM repartitions
                                JOIN operations ON operations.id = repartitions.operation
                                JOIN tricounts ON tricounts.id = operations.tricount
                                JOIN subscriptions ON subscriptions.tricount = tricounts.id AND subscriptions.user = repartitions.user
                                WHERE subscriptions.user = :userID AND tricounts.id = :tricountID",
            array("userID" => $user->getId(), "tricountID" => $tricount->getId())
        );
        return $query->rowCount() > 0;
    }

    public static function get_repartition_by_operation_id_and_user($operationID, $userID)
    {
        $query = self::execute(
            "SELECT * from repartitions 
                                WHERE operation = :operationID
                                AND user = :userID",
            array(
                "operationID" => $operationID,
                "userID" => $userID
            )
        );
        $data = $query->fetch();

        return new Repartition(
            Operation::get_operation_by_id($data['operation']),
            User::get_user_by_id($data['user']),
            $data['weight']
        );
    }


    public function getOperation()
    {
        return $this->operation;
    }

    public function getUser()
    {
        return $this->user;
    }
    
    public function getWeight()
    {
        return $this->weight;
    }
    
    public function setWeight($weight)
    {
        return $this->weight = $weight;
    }
}
