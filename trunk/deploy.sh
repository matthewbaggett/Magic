#!/bin/bash
./update.sh; 
php-5.3 scripts/regenerate_objects.php --all --no-tests
