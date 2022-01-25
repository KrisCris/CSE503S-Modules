#!/bin/sh
if [ ! -d "/media/module3res" ] 
then
    sudo mkdir /media/module3res
    sudo mkdir /media/module3res/userPhoto
    sudo mkdir /media/module3res/storyImg
    cp defaultPhoto.png /media/module3res/userPhoto
    cp story.svg /media/module3res/storyImg
fi
sudo chown -R connlost /media/module3res
sudo chgrp -R apache /media/module3res
sudo chmod -R 770 /media/module3res
sudo chmod g+s /media/module3res
