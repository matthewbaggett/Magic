svn co http://svn.turbocrms.com/magicapps/rabbit/trunk/ ./application/Rabbit
echo "Checking out plugins/ecommerce..."
svn co http://svn.turbocrms.com/magicplugins/ecommerce/trunk/ ./plugins/ecommerce
./installers/core.sh

