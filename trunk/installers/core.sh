echo "Checking out Core..."
svn co http://svn.turbocrms.com/magic/trunk ./
echo "Checking out Exception Decorator..."
svn co http://svn.turbocrms.com/magicapps/exception/trunk/ ./application/Exception
echo "Checking out plugins/CMS..."
svn co http://svn.turbocrms.com/magicplugins/cms/trunk/ ./plugins/cms
echo "Checking out plugins/Gallery..."
svn co http://svn.turbocrms.com/magicplugins/gallery/trunk/ ./plugins/gallery
echo "Checking out plugins/Pages..."
svn co http://svn.turbocrms.com/magicplugins/pages/trunk/ ./plugins/pages
echo "Checking out plugins/CDN..."
svn co http://svn.turbocrms.com/magicplugins/cdn/trunk/ ./plugins/cdn
echo "Checking out plugins/GoogleChartAPI..."
svn co http://svn.turbocrms.com/magicplugins/googlechartapi/trunk/ ./plugins/googlechartapi
echo "Checking out plugins/FederatedLogin..."
svn co http://svn.turbocrms.com/magicplugins/federatedlogin/trunk/ ./plugins/federatedlogin
echo "Checking out plugins/blog..."
svn co http://svn.turbocrms.com/magicplugins/blog/trunk/ ./plugins/blog
