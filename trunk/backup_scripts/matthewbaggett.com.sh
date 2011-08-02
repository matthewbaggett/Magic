#!/bin/bash
echo "Preparing backup directory"
mkdir ~/tmp/backup/matthewbaggett.com/ -p 
cd ~/tmp/backup/matthewbaggett.com/
svn checkout -q --no-auth-cache --non-interactive --username matthewbaggett.com --password G546I6P4Z2802Pg http://svn.turbocrms.com/magicsqlbackups/matthewbaggett.com/ .

echo "Exporting database to files..."
for T in `mysql --host=sql.turbocrms.com --user=mbaggett --password=g5BpO2SVvLUPwti -N -B -e 'show tables from mbaggett'`; do 
	echo "  > $T.sql"; 
	mysqldump \
	--host=sql.turbocrms.com \
	--user=mbaggett \
	--password=g5BpO2SVvLUPwti \
	--skip-comments \
	--skip-extended-insert \
	--skip-quick \
	mbaggett \
	$T > $T.sql;
done

echo "Adding tables to subversion...";
svn add * --non-interactive -q
echo "Committing...";
svn commit -q -m "Committing at `date +%Y/%m/%d\ %H:%M:%S`." --non-interactive --username matthewbaggett.com --password G546I6P4Z2802Pg 
cd -
echo "Removing tmp files...";
rm ~/tmp/backup/matthewbaggett.com/ -Rf
echo "Done!"
