<?php

if (SettingSearcher::Factory()->search_by_system_name("REGEN_COUNT")->count() == 0) {
    Setting::Factory()
            ->set_system_name("REGEN_COUNT")
            ->set_public_name("Count of the number of times the codebase has been regenerated on this server.")
            ->set_default_value(0)
            ->set_value(0)
            ->save();
}

/*
 * Configure the default email addresses
 */
if (SettingSearcher::Factory()->search_by_system_name("ADMIN_EMAIL")->count() == 0) {
    Setting::Factory()
            ->set_system_name("ADMIN_EMAIL")
            ->set_public_name("Administrator Email")
            ->set_default_value("geeks@turbocrms.com")
            ->set_value("geeks@turbocrms.com")
            ->save();
}
if (SettingSearcher::Factory()->search_by_system_name("SERVER_EMAIL")->count() == 0) {
    Setting::Factory()
            ->set_system_name("SERVER_EMAIL")
            ->set_public_name("Server email - Where email from this server is sent from, by default")
            ->set_default_value("geeks@turbocrms.com")
            ->set_value("geeks@turbocrms.com")
            ->save();
}

/*
 * Configure the SMTP settings
 */
if (SettingSearcher::Factory()->search_by_system_name("EMAIL_SMTP_HOST")->count() == 0) {
    Setting::Factory()
            ->set_system_name("EMAIL_SMTP_HOST")
            ->set_public_name("Email SMTP host - The server which email is sent from")
            ->set_default_value("localhost")
            ->set_value("smtp.turbocrms.com")
            ->save();
}
if (SettingSearcher::Factory()->search_by_system_name("EMAIL_SMTP_PORT")->count() == 0) {
    Setting::Factory()
            ->set_system_name("EMAIL_SMTP_PORT")
            ->set_public_name("Email SMTP port - The port on the SMTP mail server to send to")
            ->set_default_value("25")
            ->set_value("25")
            ->save();
}
if (SettingSearcher::Factory()->search_by_system_name("EMAIL_SMTP_SSL")->count() == 0) {
    Setting::Factory()
            ->set_system_name("EMAIL_SMTP_SSL")
            ->set_public_name("Email SMTP ssl - Should I use SSL to talk to the mail server?")
            ->set_default_value("0")
            ->set_value("0")
            ->save();
}

/*
 * SMTP settings
 */

if (SettingSearcher::Factory()->search_by_system_name("EMAIL_SMTP_USERNAME")->count() == 0) {
    Setting::Factory()
            ->set_system_name("EMAIL_SMTP_USERNAME")
            ->set_public_name("Email SMTP Username")
            ->set_default_value("geeks@turbocrms.com")
            ->set_value("geeks@turbocrms.com")
            ->save();
}
if (SettingSearcher::Factory()->search_by_system_name("EMAIL_SMTP_PASSWORD")->count() == 0) {
    Setting::Factory()
            ->set_system_name("EMAIL_SMTP_PASSWORD")
            ->set_public_name("Email SMTP Password")
            ->set_default_value("n75G7ER2oJ5L3LL")
            ->set_value("n75G7ER2oJ5L3LL")
            ->save();
}

/*
 * Default cron config
 */
            
if (SettingSearcher::Factory()->search_by_system_name("CRON_ACTIVE")->count() == 0) {
    Setting::Factory()
            ->set_system_name("CRON_ACTIVE")
            ->set_public_name("Cron active - Should the cron be running currently?")
            ->set_default_value("1")
            ->set_value("1")
            ->save();
}