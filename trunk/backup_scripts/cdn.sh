#!/bin/bash
echo "****************************"
echo "*        cdn        *"
echo "****************************"

echo "Preparing backup directory"
mkdir ~/tmp/backup/cdn/ -p 
cd ~/tmp/backup/cdn/
svn checkout -q --no-auth-cache --non-interactive --username cdn --password 7ZpMdRxvvqs72AeQ http://svn.turbocrms.com/magicsqlbackups/cdn/ .

echo "Exporting database to files..."
for T in `mysql --host=centro.turbocrms.com --user=cdn --password=7ZpMdRxvvqs72AeQ -N -B -e 'show tables from cdn'`; do 
	echo "  > $T.sql"; 
	mysqldump \
	--host=centro.turbocrms.com \
	--user=cdn \
	--password=7ZpMdRxvvqs72AeQ \
	--skip-comments \
	--skip-extended-insert \
	--skip-quick \
	cdn \
	$T > $T.sql;
done

echo "Adding tables to subversion...";
svn add * --non-interactive -q
echo "Committing...";
svn commit -q -m "Committing at `date +%Y/%m/%d\ %H:%M:%S`." --non-interactive --username cdn --password 7ZpMdRxvvqs72AeQ 
cd -
echo "Removing tmp files...";
rm ~/tmp/backup/cdn/ -Rf
echo "Done!"
