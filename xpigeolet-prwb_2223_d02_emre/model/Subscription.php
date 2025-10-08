<?php

require_once 'framework/Model.php';
require_once 'model/User.php';
require_once 'model/Tricount.php';


class Subscription  extends Model
{

    private $tricount;
    private $user;

    public function __construct($tricount, $user)
    {

        $this->tricount = $tricount;
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
    public function getTricount()
    {
        return $this->tricount;
    }


    public static function get_subscriptions_by_user_and_tricount($tricount, $user)
    {
        $query = self::execute(
            "SELECT * FROM subscriptions
                                WHERE user =:userID AND tricount = :tricountID",
            array("userID" => $user->getId(), "tricountID" => $tricount->getId())
        );
        $row = $query->fetch();
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new Subscription(
                Tricount::get_tricount_by_id($row['tricount']),
                User::get_user_by_id($row['user'])
            );
        }
    }


    public function persist(): Subscription
    {

        self::execute(
            "INSERT INTO subscriptions(tricount,user) 
                            VALUES(:tricountID,:userID)",
            array(
                "userID" => $this->getUser()->getId(),
                "tricountID" => $this->getTricount()->getId(),
            )
        );
        return $this;
    }





    public static function get_subscriptions_by_user($user)
    {
        $query = self::execute("SELECT * FROM subscriptions 
            WHERE subscriptions.user = :user", array("user" => $user->getId()));
        $data = $query->fetchAll();
        $subscriptions = [];
        foreach ($data as $row) {
            $subscriptions[] = new Subscription(
                Tricount::get_tricount_by_id($row['tricount']),
                User::get_user_by_id($row['user'])
            );
        }
        return $subscriptions;
    }

    public static function get_number_of_subscribers($tricount)
    {

        $query = self::execute("SELECT COUNT(user) FROM subscriptions 
                WHERE tricount = :tricount", array("tricount" => $tricount));
        $data = $query->fetch();
        return $data['COUNT(user)'];
    }

    public static function get_list_subscription()
    {
        $query = self::execute("SELECT * FROM subscriptions", array());
        $data = $query->fetchAll();
        $result = [];
        foreach ($data as $row) {
            $result[] = new Subscription($row['tricount'], User::get_user_by_id($row['user']));
        }
        return $result;
    }



    public static function get_not_subscribed_users_by_tricount($tricount)
    {
        $query = self::execute(
            "SELECT * FROM users
                                WHERE id NOT IN (SELECT user FROM subscriptions WHERE tricount = :tricountID)",
            array(':tricountID' => $tricount->getId())
        );
        $data = $query->fetchAll();
        $not_subscribed_users = [];
        foreach ($data as $row) {
            $not_subscribed_users[] = User::get_user_by_id($row['id']);
        }
        return $not_subscribed_users;
    }

    public static function get_not_subscribed_users_by_tricount_json($tricount)
    {
        $query = self::execute(
            "SELECT * FROM users
        WHERE id NOT IN (SELECT user FROM subscriptions WHERE tricount = :tricountID)",
            array(':tricountID' => $tricount->getId())
        );
        $data = $query->fetchAll();
        $not_subscribed_users = [];
        foreach ($data as $row) {
            $user = User::get_user_by_id($row['id']);
            $not_subscribed_users[] = array(
                'id' => $user->getId(),
                'fullname' => $user->getFullname()
            );
        }
        return json_encode($not_subscribed_users);
    }

    public static function get_subscribed_users_by_tricount($tricount)
    {
        $query = self::execute(
            "SELECT * FROM users
                                WHERE id IN (SELECT user FROM subscriptions WHERE tricount = :tricountID)",
            array(':tricountID' => $tricount->getId())
        );
        $data = $query->fetchAll();
        $subscribed_users = [];
        foreach ($data as $row) {
            $subscribed_users[] = User::get_user_by_id($row['id']);
        }
        return $subscribed_users;
    }

    public function delete()
    {
        self::execute(
            "DELETE FROM subscriptions 
            WHERE user = :user AND tricount = :tricount",
            array("user" => $this->user->getId(), "tricount" => $this->tricount->getId())
        );
        return true;
    }
}
