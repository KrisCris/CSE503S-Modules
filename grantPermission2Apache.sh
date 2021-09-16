#!/bin/sh
if [ ! -d "../../module2res" ] 
then
    mkdir ../../module2res
fi
sudo chown -R connlost ../../module2res
sudo chgrp -R apache ../../module2res
sudo chmod -R 770 ../../module2res
sudo chmod g+s ../../module2res
