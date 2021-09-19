<?php
class User{
    private $name;
    private $passwd;

    function __construct($name, $passwd = null)
    {
        $this->name = $name;
        $this->passwd = $passwd;
    }

    public static function fromDecodedJson($jsonObj){
        // echo var_dump($jsonObj);
        if(array_key_exists("name",$jsonObj)){
            return new static($jsonObj["name"]);
        } else {
            return null;
        }
    }

    public function toJson(){
        $res = array("name"=>$this->name);
        return json_encode($res);
    }

    public function getName(){
        return $this->name;
    }

    

}
