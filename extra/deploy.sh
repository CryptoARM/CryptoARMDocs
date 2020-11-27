#!/bin/bash 

path_to_module="/var/www/"$1"/bitrix/modules/trusted.cryptoarmdocscrp/"

sudo rm -rf $path_to_module
sudo cp -R trusted.cryptoarmdocs/ $path_to_module
sudo chown -R alr:www-data $path_to_module
sudo find $path_to_module -type f -exec chmod 0664 {} \;
sudo find $path_to_module -type d -exec chmod 2775 {} \;

sudo cp  extra/crnm.php $path_to_module
cd $path_to_module
php crmn.php
cd ..
sudo mv trusted.cryptoarmdocscrp trusted.cryptoarmdocsbusiness





