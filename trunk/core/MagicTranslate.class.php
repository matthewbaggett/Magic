<?php 
class MagicTranslate{
	const languages = "german|french";
	const GOOGLE_API_LOCATION = 'https://www.googleapis.com/language/translate/v2';
	
	static public function getTranslation($english){
		if(isset($_GET['language'])){
			$language = $_GET['language'];
		}else{
			$language = 'english';
		}
		if($language == 'english'){
			return $english;
		}
		$oTranslation = TranslationSearcher::Factory()->search_by_english($english)->execute_one();
		if($oTranslation === FALSE){
			$oTranslation = Translation::Factory()->set_english($english)->save();
		}
		$oTranslation = Translation::Cast($oTranslation);
		$method = "get_{$language}";
		$translation = call_user_method($method, $oTranslation);
		if(strlen(trim($translation)) > 0){
			return $translation;
		}else{
			return strtoupper("missing {$language} - ") . $english;
		}

	}
	static public function checkForTranslations(){
		foreach(explode("|",self::languages) as $language){
			$oSearcher = TranslationSearcher::Factory();
			call_user_method("search_by_" . $language, $oSearcher, '');
			//echo $oSearcher->query();
			$oMissingTranslations = $oSearcher->execute();
			echo "Processing ".count($oMissingTranslations)." missing {$language} translations\n";
			foreach($oMissingTranslations as $oMissingTranslation){
				$scrape = new Scrape(
					self::GOOGLE_API_LOCATION . 
					"?key=" . SettingController::get("GOOGLE_TRANSLATE_KEY") . 
					"&q=" . urlencode($oMissingTranslation->get_english()) .
					"&source=" . "en" .
					"&target=" . self::mapLanguageToCode($language)
				);
				$result = json_decode($scrape->html);
				$translations = $result->data->translations;
				if(strlen(trim($translations[0]->translatedText)) > 0){
					call_user_method("set_{$language}", $oMissingTranslation, $translations[0]->translatedText);
					echo " > {$oMissingTranslation->get_english()} = {$translations[0]->translatedText}\n";
					$oMissingTranslation->save();	
				}else{
					print_r($result);
				}
			}
			echo "\n\n";
		}
	}
	
	static private function mapLanguageToCode($language){
		switch(ucfirst($language)){
			case 'Afrikaans':
				return 'af';
			case 'Albanian':
				return 'sq';
			case 'Arabic':
				return 'ar';
			case 'Belarusian':
				return 'be';
			case 'Bulgarian':
				return 'bg';
			case 'Catalan':
				return 'ca';
			case 'Chinese Simplified':
				return 'zh-CN';
			case 'Chinese Traditional':
				return 'zh-TW';
			case 'Croatian':
				return 'hr';
			case 'Czech':
				return 'cs';
			case 'Danish':
				return 'da';
			case 'Dutch':
				return 'nl';
			case 'English':
				return 'en';
			case 'Estonian':
				return 'et';
			case 'Filipino':
				return 'tl';
			case 'Finnish':
				return 'fi';
			case 'French':
				return 'fr';
			case 'Galician':
				return 'gl';
			case 'German':
				return 'de';
			case 'Greek':
				return 'el';
			case 'Haitian Creole':
				return 'ht';
			case 'Hebrew':
				return 'iw';
			case 'Hindi':
				return 'hi';
			case 'Hungarian':
				return 'hu';
			case 'Icelandic':
				return 'is';
			case 'Indonesian':
				return 'id';
			case 'Irish':
				return 'ga';
			case 'Italian':
				return 'it';
			case 'Japanese':
				return 'ja';
			case 'Latvian':
				return 'lv';
			case 'Lithuanian':
				return 'lt';
			case 'Macedonian':
				return 'mk';
			case 'Malay':
				return 'ms';
			case 'Maltese':
				return 'mt';
			case 'Norwegian':
				return 'no';
			case 'Persian':
				return 'fa';
			case 'Polish':
				return 'pl';
			case 'Portuguese':
				return 'pt';
			case 'Romanian':
				return 'ro';
			case 'Russian':
				return 'ru';
			case 'Serbian':
				return 'sr';
			case 'Slovak':
				return 'sk';
			case 'Slovenian':
				return 'sl';
			case 'Spanish':
				return 'es';
			case 'Swahili':
				return 'sw';
			case 'Swedish':
				return 'sv';
			case 'Thai':
				return 'th';
			case 'Turkish':
				return 'tr';
			case 'Ukrainian':
				return 'uk';
			case 'Vietnamese':
				return 'vi';
			case 'Welsh':
				return 'cy';
			case 'Yiddish':
				return 'yi';
			default:
				return 'en';
		}
	}
}

function t($english){
	return MagicTranslate::getTranslation($english);
}