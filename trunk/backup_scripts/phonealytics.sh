#!/bin/bash
echo "****************************"
echo "*       PHONEALYTICS       *"
echo "****************************"

echo "Preparing backup directory"
mkdir ~/tmp/backup/phonealytics/ -p 
cd ~/tmp/backup/phonealytics/
svn checkout -q --no-auth-cache --non-interactive --username phonealytics --password 975x2nM4JP5427Q http://svn.turbocrms.com/magicsqlbackups/phonealytics/ .

echo "Exporting database to files..."
for T in `mysql --host=sql.turbocrms.com --user=phonealytics --password=N7GtUeeyi814g2G -N -B -e 'show tables from phonealytics'`; do 
	echo "  > $T.sql"; 
	mysqldump \
	--host=sql.turbocrms.com \
	--user=phonealytics \
	--password=N7GtUeeyi814g2G \
	--skip-comments \
	--skip-extended-insert \
	--skip-quick \
	phonealytics \
	$T > $T.sql;
done

echo "Adding tables to subversion...";
svn add * --non-interactive -q
echo "Committing...";
svn commit -q -m "Committing at `date +%Y/%m/%d\ %H:%M:%S`." --non-interactive --username phonealytics --password 975x2nM4JP5427Q 
cd -
echo "Removing tmp files...";
rm ~/tmp/backup/phonealytics/ -Rf
echo "Done!"
