<?php
class User{
    var $name;
    var $files;

    function __construct($name)
    {
        $this->name = $name;
        $this->files = null; //todo
    }

}
