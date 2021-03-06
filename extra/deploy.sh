#!/bin/bash 

path_to_module="/stages/www/"$1"/bitrix/modules/trusted.cryptoarmdocsfree/"

sudo rm -rf $path_to_module
sudo cp -R trusted.cryptoarmdocs/ $path_to_module
sudo chown -R alex:www-data $path_to_module
sudo find $path_to_module -type f -exec chmod 0664 {} \;
sudo find $path_to_module -type d -exec chmod 2775 {} \;
