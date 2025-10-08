<?php

require_once 'framework/Model.php';
require_once 'model/Repartition_template.php';
require_once 'model/User.php';

class repartition_template_item extends Model
{

    private $user;
    private $repartition_template;
    private $weight;

    public function __construct($user, $repartition_template, $weight)
    {

        $this->user = $user;
        $this->repartition_template = $repartition_template;
        $this->weight = $weight;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    public function getRepartition()
    {
        return $this->repartition_template;
    }


    public static function delete_repartition_template_item($repartition)
    {
        self::execute(
            "DELETE FROM repartition_template_items 
                      WHERE repartition_template = :id",
            array("id" => $repartition->getId())
        );
    }
}
