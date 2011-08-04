#!/bin/bash
echo "****************************"
echo "*        bunnehbutt        *"
echo "****************************"

echo "Preparing backup directory"
mkdir ~/tmp/backup/bunnehbutt/ -p 
cd ~/tmp/backup/bunnehbutt/
svn checkout -q --no-auth-cache --non-interactive --username bunnehbutt --password sYQvXNtzcYMa5QBn http://svn.turbocrms.com/magicsqlbackups/bunnehbutt/ .

echo "Exporting database to files..."
for T in `mysql --host=centro.turbocrms.com --user=bunnehbutt --password=sYQvXNtzcYMa5QBn -N -B -e 'show tables from bunnehbutt'`; do 
	echo "  > $T.sql"; 
	mysqldump \
	--host=centro.turbocrms.com \
	--user=bunnehbutt \
	--password=sYQvXNtzcYMa5QBn \
	--skip-comments \
	--skip-extended-insert \
	--skip-quick \
	bunnehbutt \
	$T > $T.sql;
done

echo "Adding tables to subversion...";
svn add * --non-interactive -q
echo "Committing...";
svn commit -q -m "Committing at `date +%Y/%m/%d\ %H:%M:%S`." --non-interactive --username bunnehbutt --password sYQvXNtzcYMa5QBn 
cd -
echo "Removing tmp files...";
rm ~/tmp/backup/bunnehbutt/ -Rf
echo "Done!"
