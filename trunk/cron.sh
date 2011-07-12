#!/bin/bash
MY_PATH="`dirname \"$0\"`"
echo "$MY_PATH"
#/usr/bin/php ./scripts/cron.php
/usr/local/bin/php-5.3 $MY_PATH/scripts/cron.php
