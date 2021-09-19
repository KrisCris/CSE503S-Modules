<?php
class User{
    private $name;
    private $passwd;

    function __construct($name, $passwd = null)
    {
        $this->name = $name;
        $this->passwd = $passwd;
    }

    # create instance from json object
    public static function fromDecodedJson($jsonObj){
        if(array_key_exists("name",$jsonObj)){
            return new static($jsonObj["name"]);
        } else {
            return null;
        }
    }

    # dump object to json string
    public function toJson(){
        $res = array("name"=>$this->name);
        return json_encode($res);
    }

    # getter
    public function getName(){
        return $this->name;
    }

    

}
