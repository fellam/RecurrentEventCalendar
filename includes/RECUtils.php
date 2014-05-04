<?php

/**
 * File holding the RECUtils class
 *
 * @author Michele Fella <michele.fella@gmail.com>
 * @file
 * @ingroup RecurrentEventCalendar
 */
if ( !defined( 'REC_VERSION' ) ) {
	die( 'This file is part of the RecurrentEventCalendar extension, it is not a valid entry point.' );
}

/**
 * The RECUtils class.
 *
 * @ingroup RecurrentEventCalendar
 */
class RECUtils {

	/**
	 * Initialize the parser functions of the extension.
	 *
	 * Currently only #reclink
	 *
	 * @param Parser $parser
	 * @return bool
	 */
	static public function initParserFunction( &$parser ) {

		// Create a function hook associating the "example" magic word with the
		// efExampleParserFunction_Render() function.
		$parser->setFunctionHook( 'reclink', array('RECUtils', 'renderreclink') );

		// Return true so that MediaWiki continues to load extensions.
		return true;
	}

	/**
	 * Renders the #reclink parser function.
	 *
	 * @param Parser $parser
	 * @return string the unique tag which must be inserted into the stripped text
	 */
	static public function renderreclink( &$parser ) {

		$params = func_get_args();
// 		print var_dump($params);
		array_shift( $params ); // We don't need the parser.

		// remove the target parameter should it be present
		foreach ( $params as $key => $value ) {
			$elements = explode( '=', $value, 2 );
			if ( $elements[0] === 'target' ){
				unset($params[$key]);
			} elseif ( $elements[0] === 'keep parameters') {
				$params[$key] = 'keep parameters=1';
			}

		}
		
		// set the origin parameter
		// This will block it from use as iterator parameter. Oh well.
		$params[] = "origin=" . $parser->getTitle()->getArticleID();

		// hack to remove newline from beginning of output, thanks to
		// http://jimbojw.com/wiki/index.php?title=Raw_HTML_Output_from_a_MediaWiki_Parser_Function
		return $parser->insertStripItem( SFUtils::createFormLink( $parser, 'RecurrentEventCalendarEdit', $params ), $parser->mStripState );
	}


	/**
	 * Returns a value from an array if the $key exists, else returns the default.
	 *
	 * The value is trimmed.
	 *
	 * @param array $array
	 * @param mixed $key
	 * @param mixed $default
	 * @return mixed
	 */
	static public function fromArray( &$array, $key, $default = null ) {
		if ( array_key_exists( $key, $array ) && is_string( $array[$key] ) ) {
			return trim( $array[$key] );
		} else {
			return $default;
		}
	}

	/**
	 * Returns the parsed message for the given key and params
	 * @param string $key
	 * @return string
	 */
	static public function buildMessage ( $key ) {

		$args = func_get_args();
		array_shift($args);

		$msg= new Message($key,$args);

		return $msg->inContentLanguage()->parse();
	}
}
