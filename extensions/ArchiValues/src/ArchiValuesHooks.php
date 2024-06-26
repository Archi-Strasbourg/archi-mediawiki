<?php
use MediaWiki\MediaWikiServices;
class ArchiValuesHooks{
    public static function onParserFirstCallInit( Parser $parser ) {
        $parser->setFunctionHook( 'ArchiValues', [ self::class, 'getAllValuesForProperty' ] );
    }

    public static function renderArchiValues( Parser $parser, $attribut='') {
        $output = "HAHA I AM A FUNCTION! $attribut";
        return $output;
    }

	public static function getMaxValuesToRetrieve( $substring = null ) {
		// $wgPageFormsMaxAutocompleteValues is currently misnamed,
		// or mis-used - it's actually used for those cases where
		// autocomplete *isn't* used, i.e. to populate a radiobutton
		// input, where it makes sense to have a very large limit
		// (current value: 1,000). For actual autocompletion, though,
		// with a substring, a limit like 20 makes more sense. It
		// would be good use the variable for this purpose instead,
		// with a default like 20, and then create a new global
		// variable, like $wgPageFormsMaxNonAutocompleteValues, to
		// hold the much larger number.
		if ( $substring == null ) {
			global $wgPageFormsMaxAutocompleteValues;
			return $wgPageFormsMaxAutocompleteValues;
		} else {
			return 20;
		}
	}

	public static function getSMWPropertyValues( $store, $subject, $propID, $requestOptions = null ) {
		// If SMW is not installed, exit out.
		if ( !class_exists( 'SMWDIWikiPage' ) ) {
			return [];
		}
		if ( $subject === null ) {
			$page = null;
		} else {
			$page = SMWDIWikiPage::newFromTitle( $subject );
		}
		$property = SMWDIProperty::newFromUserLabel( $propID );
		$res = $store->getPropertyValues( $page, $property, $requestOptions );
		$values = [];
		foreach ( $res as $value ) {
			if ( $value instanceof SMWDIUri ) {
				$values[] = $value->getURI();
			} elseif ( $value instanceof SMWDIWikiPage ) {
				$realValue = str_replace( '_', ' ', $value->getDBKey() );
				if ( $value->getNamespace() != 0 ) {
					$realValue = PFUtils::getCanonicalName( $value->getNamespace() ) . ":$realValue";
				}
				$values[] = $realValue;
			} else {
				// getSortKey() seems to return the correct
				// value for all the other data types.
				$values[] = str_replace( '_', ' ', $value->getSortKey() );
			}
		}
		return $values;
	}

    public static function getAllValuesForProperty(Parser $parser, $property_name='' ) {
		$store = PFUtils::getSMWStore();
		if ( $store == null ) {
			return [];
		}
		$requestoptions = new SMWRequestOptions();
		$requestoptions->limit = self::getMaxValuesToRetrieve();
		$values = self::getSMWPropertyValues( $store, null, $property_name, $requestoptions );
		$values = array_filter($values, function($value) {
			return $value !== 'ValInconnu';
		});
		sort( $values );
		return implode(';sep;',$values);
	}

    
}