#!/bin/sh
if [ ! -d "/var/www/module2res" ] 
then
    sudo mkdir /var/www/module2res
fi
sudo chown -R connlost /var/www/module2res
sudo chgrp -R apache /var/www/module2res
sudo chmod -R 770 /var/www/module2res
sudo chmod g+s /var/www/module2res
