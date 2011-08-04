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
