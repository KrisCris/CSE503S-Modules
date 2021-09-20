#!/bin/sh
if [ ! -d "/media/module2res" ] 
then
    sudo mkdir /media/module2res
fi
sudo chown -R connlost /media/module2res
sudo chgrp -R apache /media/module2res
sudo chmod -R 770 /media/module2res
sudo chmod g+s /media/module2res
