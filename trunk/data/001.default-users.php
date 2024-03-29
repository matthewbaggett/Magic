<?php

if(UserSearcher::Factory()->search_by_username("system")->count() == 0){
	//echo "  Adding system user\n";
	MagicQuery::Factory("INSERT","Users")
		->addSet("id",-1)
		->addSet('username','system')
		->addSet('email','geeks@turbocrms.com')
		->addSet('nickname','Turbo System')
		->addSet('level','basic')
		->addSet('active','unactive')
		->addSet('firstname','Turbo')
		->addSet('surname','System')
		->addSet('date_of_birth',time())
		->addSet('date_of_registration',time())
		->execute();

}
if(UserSearcher::Factory()->search_by_username("geusebio")->count() == 0){
	//echo "  Adding G. Eusebio\n";
    User::Factory()
        ->set_username("geusebio")
        ->set_email("matthew@baggett.me")
        ->set_password('dcec2cc1cb31eb4483cb3acf27b96463eab58573')
        ->set_salutation("Mr")
        ->set_firstname("Matthew")
        ->set_surname("Baggett")
        ->set_nickname("G. Eusebio")
        ->set_date_of_birth(strtotime("01/06/1990"))
        ->set_date_of_registration(time())
        ->set_active(USER::ACTIVE_ACTIVE)
        ->set_level(USER::LEVEL_SUPERADMIN)
        ->save();
}
if(UserSearcher::Factory()->search_by_username("guest")->count() == 0){
	//echo "  Adding Guest\n";
    User::Factory()
        ->set_username("guest")
        ->set_email("geeks@turbocrms.com")
        ->set_password(hash("SHA1","alamo"))
        ->set_salutation("Mr")
        ->set_firstname("John Q.")
        ->set_surname("Public")
        ->set_nickname("Guest User")
        ->set_date_of_birth(strtotime("28th February 1998"))
        ->set_date_of_registration(time())
        ->set_active(USER::ACTIVE_ACTIVE)
        ->set_level(USER::LEVEL_BASIC)
        ->save();
}