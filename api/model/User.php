<?php
class User
{
    private $id;
    private $username;
    private $password;
    private $time;
    private $photo;

    function __construct()
    {
        $this->id = 0;
        $this->username = 'tom';
        $this->password = 'hashsalt';
        $this->time = "??";
        $this->photo="/media/module3res/userPhoto/defaultPhoto.png";
    }

    public function getPhoto()
    {
        $type = pathinfo($this->photo, PATHINFO_EXTENSION);
        $data = file_get_contents($this->photo);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}
?>