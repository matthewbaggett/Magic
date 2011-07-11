for ((i = 0; i <= 500; i++))
do
php /home/matthew/Magic/application/bbindex/script/e621.batch.php;
rsync -rv /home/matthew/Magic/application/bbindex/upload/ ncms@bunnehbutt.com:~/Socializr/application/bbindex/upload/; 
rm /home/matthew/Magic/application/bbindex/upload/* -Rv
done
