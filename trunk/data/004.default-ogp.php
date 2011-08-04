<?php

if (SettingSearcher::Factory()->search_by_system_name("OGP_ENABLE")->count() == 0) {
    Setting::Factory()
            ->set_system_name("OGP_ENABLE")
            ->set_public_name("Open Graph Protocol Enable")
            ->set_default_value(1)
            ->set_value(1)
            ->save();
}
if (SettingSearcher::Factory()->search_by_system_name("OGP_TYPE")->count() == 0) {
    Setting::Factory()
            ->set_system_name("OGP_TYPE")
            ->set_public_name("Open Graph Protocol Default Type")
            ->set_default_value('website')
            ->set_value('website')
            ->save();
}

if (SettingSearcher::Factory()->search_by_system_name("OGP_LOCATION_ENABLE")->count() == 0) {
    Setting::Factory()
            ->set_system_name("OGP_LOCATION_ENABLE")
            ->set_public_name("Open Graph Protocol - Location - Enable")
            ->set_default_value(0)
            ->set_value(0)
            ->save();
}

if (SettingSearcher::Factory()->search_by_system_name("OGP_LOCATION_LATITUDE")->count() == 0) {
    Setting::Factory()
            ->set_system_name("OGP_LOCATION_LATITUDE")
            ->set_public_name("Open Graph Protocol - Location - Latitude")
            ->set_default_value('53.800651')
            ->set_value('53.800651')
            ->save();
}

if (SettingSearcher::Factory()->search_by_system_name("OGP_LOCATION_LONGITUDE")->count() == 0) {
    Setting::Factory()
            ->set_system_name("OGP_LOCATION_LONGITUDE")
            ->set_public_name("Open Graph Protocol - Location - Longitude")
            ->set_default_value('-4.064941')
            ->set_value('-4.064941')
            ->save();
}

if (SettingSearcher::Factory()->search_by_system_name("OGP_LOCATION_STREET_ADDRESS")->count() == 0) {
    Setting::Factory()
            ->set_system_name("OGP_LOCATION_STREET_ADDRESS")
            ->set_public_name("Open Graph Protocol - Location - Street Address")
            ->set_default_value('1, St Aldate\'s')
            ->set_value('1, St Aldate\'s')
            ->save();
}

if (SettingSearcher::Factory()->search_by_system_name("OGP_LOCATION_LOCALITY")->count() == 0) {
    Setting::Factory()
            ->set_system_name("OGP_LOCATION_LOCALITY")
            ->set_public_name("Open Graph Protocol - Location - Locality")
            ->set_default_value('Oxford City')
            ->set_value('Oxford City')
            ->save();
}

if (SettingSearcher::Factory()->search_by_system_name("OGP_LOCATION_REGION")->count() == 0) {
    Setting::Factory()
            ->set_system_name("OGP_LOCATION_REGION")
            ->set_public_name("Open Graph Protocol - Location - Region/County")
            ->set_default_value('Oxfordshire')
            ->set_value('Oxfordshire')
            ->save();
}

if (SettingSearcher::Factory()->search_by_system_name("OGP_LOCATION_POSTCODE")->count() == 0) {
    Setting::Factory()
            ->set_system_name("OGP_LOCATION_POSTCODE")
            ->set_public_name("Open Graph Protocol - Location - Postcode")
            ->set_default_value('OX11BX')
            ->set_value('OX11BX')
            ->save();
}

if (SettingSearcher::Factory()->search_by_system_name("OGP_LOCATION_COUNTRY")->count() == 0) {
    Setting::Factory()
            ->set_system_name("OGP_LOCATION_COUNTRY")
            ->set_public_name("Open Graph Protocol - Location - Country")
            ->set_default_value('United Kingdom')
            ->set_value('United Kingdom')
            ->save();
}