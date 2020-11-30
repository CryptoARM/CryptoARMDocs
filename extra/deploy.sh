#!/bin/bash 

path_to_module="/var/www/"$1"/bitrix/modules/trusted.cryptoarmdocsbusiness/"
reduction="start"

sudo rm -rf $path_to_module
sudo cp -R trusted.cryptoarmdocs/ $path_to_module
sudo cp  extra/crnm.php $path_to_module
sudo chown -R alr:www-data $path_to_module
sudo find $path_to_module -type f -exec chmod 0664 {} \;
sudo find $path_to_module -type d -exec chmod 2775 {} \;

cd "/var/www/"$1"/bitrix/modules/"
if [ -d "bitrix.eshop" ]; then
  reduction="biz"
fi

cd "/var/www/"$1
if [ -d "bizproc" ]; then
  reduction="b24"
fi

cd $path_to_module

case $reduction in
     start)
		  sudo sed -i -e "s;cryptoarmdocsbusiness;cryptoarmdocsstart;g" crnm.php
		  sudo chmod 775 crnm.php
		  sudo php crnm.php
		  cd ..
		  sudo mv trusted.cryptoarmdocsbusiness trusted.cryptoarmdocsstart
          ;;
     b24)

		  sudo chmod 775 crnm.php
		  sudo php crnm.php
		  cd ..
		  sudo mv trusted.cryptoarmdocsbusiness trusted.cryptoarmdocscrp
          ;;
esac








