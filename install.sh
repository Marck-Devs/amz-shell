#!/bin/bash
installDir=/usr/local/bin
curDir=$(pwd)
fileName=amz-shell
echo This script will generate a simple script to set globally amz-shell.
echo To make this, the script need root privilages.
echo Press ENTER to continue...
read data
printf "\e[1;32mCreating the tmp file\e[0m\n"
touch $fileName 
echo "#!/bin/bash" > $fileName
printf "cd $curDir\nphp amz-shell.php \$@\n" >> $fileName
chmod +x $fileName
printf "\e[1;33mCreating a copy in /usr/local/bin/\e[0m\n"
sudo cp  $fileName $installDir
echo Removing the tmp file
rm $fileName
printf "\e[0;30;42mDone!\e[0m"
echo "";
