<?php

require_once 'framework/Model.php';
require_once 'model/User.php';
require_once 'model/Tricount.php';

class repartition_template extends Model
{

    private $id;
    private $title;
    private $tricount;

    public function __construct($title, $tricount, $id = null)
    {

        $this->title = $title;
        $this->tricount = $tricount;
        $this->id = $id;
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
        return $this->tricount;
    }

    public static function get_repartition_template_by_id($id)
    {
        $query = self::execute(
            "SELECT * FROM repartition_templates 
                                WHERE id =:id",
            array("id" => $id)
        );
        $data = $query->fetch();
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new repartition_template(
                $data["title"],
                Tricount::get_tricount_by_id($data['tricount']),
                $data["id"]
            );
        }
    }
    
    public static function get_all_repartition_template_of_the_tricount($tricountID)
    {

        $query = self::execute(
            "SELECT * FROM repartition_templates 
                                WHERE tricount =:tricountID",
            array("tricountID" => $tricountID)
        );
        $data = $query->fetchAll();
        $templates = [];

        if ($query->rowCount() == 0) {
            return false;
        } else {
            foreach ($data as $row) {
                $templates[] = new repartition_template(

                    $row["title"],
                    Tricount::get_tricount_by_id($row['tricount']),
                    $row["id"]
                );
            }
            return $templates;
        }
    }

    public static function delete_repartition_template($tricount)
    {
        self::execute(
            "DELETE FROM repartition_templates
                        WHERE tricount =:tricount",
            array("tricount" => $tricount)
        );
    }
}
