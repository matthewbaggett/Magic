<?php
if (SettingSearcher::Factory()->search_by_system_name("EMAIL_SMTP_SSL")->count() == 0) {
    Setting::Factory()
            ->set_system_name("WOLFRAM_ALPHA_API_KEY")
            ->set_public_name("Wolfram Alpha API Key. By default, this is the 'experimental' key, '8R9245-TEEKKL8X9E'")
            ->set_default_value("8R9245-TEEKKL8X9E")
            ->set_value("8R9245-TEEKKL8X9E")
            ->save();
}


