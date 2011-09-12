<?php 
class MagicTranslate{
	const languages = "Afrikaans|Albanian|Arabic|Belarusian|Bulgarian|Catalan|Chinese_Simplified|Chinese_Traditional|Croatian|Czech|Danish|Dutch|English|Estonian|Filipino|Finnish|French|Galician|German|Greek|Haitian_Creole|Hebrew|Hindi|Hungarian|Icelandic|Indonesian|Irish|Italian|Japanese|Latvian|Lithuanian|Macedonian|Malay|Maltese|Norwegian|Persian|Polish|Portuguese|Romanian|Russian|Serbian|Slovak|Slovenian|Spanish|Swahili|Swedish|Thai|Turkish|Ukrainian|Vietnamese|Welsh|Yiddish";
	const GOOGLE_API_LOCATION = 'https://www.googleapis.com/language/translate/v2';
	
	static public function getTranslation($original){
		/*
		 * Work out what to do with language
		 */
		if(isset($_GET['language'])){
			setcookie("language", $_GET['language'],strtotime("now + 1 week"),"/");
			$language = $_GET['language'];
		}elseif(isset($_COOKIE['language'])){
			$language = $_COOKIE['language'];
		}else{
			$language = 'English';
		}
		
		/*
		 * Do the translation fetch
		 */
		$oTranslation = TranslationSearcher::Factory()->search_by_original($original)->execute_one();
		if($oTranslation === FALSE){
			$oTranslation = Translation::Factory()->set_english($original)->set_original($original)->save();
		}
		$oTranslation = Translation::Cast($oTranslation);
		$method = "get_{$language}";
		$translation = call_user_method($method, $oTranslation);
		if(strlen(trim($translation)) > 0){
			return $translation;
		}else{
			return strtoupper("missing {$language} - ") . $original;
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
				if(strlen(trim($oMissingTranslation->get_original())) > 0){
					$scrape = new Scrape(
						self::GOOGLE_API_LOCATION . 
						"?key=" . SettingController::get("GOOGLE_API_KEY") . 
						"&q=" . urlencode($oMissingTranslation->get_original()) .
						"&source=" . "en" .
						"&target=" . self::mapLanguageToCode($language)
					);
					echo " > '{$oMissingTranslation->get_original()}'";
					$result = json_decode($scrape->html);
					$translations = $result->data->translations;
					if(strlen(trim($translations[0]->translatedText)) > 0){
						call_user_method("set_{$language}", $oMissingTranslation, $translations[0]->translatedText);
						echo " = {$translations[0]->translatedText}\n";
						$oMissingTranslation->save();	
					}else{
						echo " [FAIL]\n";
						print_r($result);
					}
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
			case 'Chinese_Simplified':
				return 'zh-CN';
			case 'Chinese_Traditional':
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
			case 'Haitian_Creole':
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

/**
 * t() is a function to wrap the MagicTranslate stuff into a convienient package for smarty.
 * @param string $english original input
 */

function t_smarty_block($params, $content, $template, &$repeat){
	$translated = MagicTranslate::getTranslation($content);
	if($params['nodfn']){
		return $translated;
	}else{
		return t_wrap_span($translated,$content);
	}
}
function t_smarty_modifier($content){
	return trans($content);
}
function t_wrap_span($translated,$content){
	return '<dfn title="'.$content.'">'.$translated.'</dfn>';
}
function trans($content){
	return t_wrap_span(MagicTranslate::getTranslation($content),$content);
}