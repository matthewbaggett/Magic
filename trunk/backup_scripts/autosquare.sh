#!/bin/bash
echo "****************************"
echo "*        AUTOSQUARE        *"
echo "****************************"

echo "Preparing backup directory"
mkdir ~/tmp/backup/autosquare/ -p 
cd ~/tmp/backup/autosquare/
svn checkout -q --no-auth-cache --non-interactive --username autosquare --password e7i30t582VE2MTm http://svn.turbocrms.com/magicsqlbackups/autosquare/ .

echo "Exporting database to files..."
for T in `mysql --host=sql.turbocrms.com --user=autosquare --password=n75G7ER2oJ5L3LL -N -B -e 'show tables from autosquare'`; do 
	echo "  > $T.sql"; 
	mysqldump \
	--host=sql.turbocrms.com \
	--user=autosquare \
	--password=n75G7ER2oJ5L3LL \
	--skip-comments \
	--skip-extended-insert \
	--skip-quick \
	autosquare \
	$T > $T.sql;
done

echo "Adding tables to subversion...";
svn add * --non-interactive -q
echo "Committing...";
svn commit -q -m "Committing at `date +%Y/%m/%d\ %H:%M:%S`." --non-interactive --username autosquare --password e7i30t582VE2MTm 
cd -
echo "Removing tmp files...";
rm ~/tmp/backup/autosquare/ -Rf
echo "Done!"
