<?php

/**
 * This extension allows the creatino of a sindle or a series of Event Calendar pages from one Form.
 *
 * @defgroup RecurrentEventCalendar Recurrent Event Calendar
 * @author Michele Fella <michele.fella@gmail.com>
 */
/**
 * The main file of the RecurrentEventCalendar extension
 *
 * @author Michele Fella <michele.fella@gmail.com>
 *
 * @file
 * @ingroup RecurrentEventCalendar
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of a MediaWiki extension, it is not a valid entry point.' );
}

if ( !defined( 'SMW_VERSION' ) ) {
	die( '<b>Error:</b> <a href="https://www.mediawiki.org/wiki/Extension:RecurrentEventCalendar">RecurrentEventCalendar</a> depends on the Semantic MediaWiki extension. You need to install <a href="https://www.mediawiki.org/wiki/Extension:Semantic_MediaWiki">Semantic MediaWiki</a> first.' );
}

if ( !defined( 'SF_VERSION' ) ) {
	die( '<b>Error:</b> <a href="https://www.mediawiki.org/wiki/Extension:RecurrentEventCalendar">RecurrentEventCalendar</a> depends on the Semantic Forms extension. You need to install <a href="https://www.mediawiki.org/wiki/Extension:Semantic_Forms">Semantic Forms</a> first.' );
}

/**
 * The Semantic Page Series version
 */
define( 'REC_VERSION', '0.1.0 alpha' );

// register the extension
$wgExtensionCredits['semantic'][] = array(
	'path' => __FILE__,
	'name' => 'Recurrent Event Calendar',
	'author' => '[http://www.mediawiki.org/wiki/User:Michele.Fella Michele Fella]',
	'url' => 'https://www.mediawiki.org/wiki/Extension:RecurrentEventCalendar',
	'descriptionmsg' => 'recurrenteventcalendar-desc',
	'version' => REC_VERSION,
);


// server-local path to this file
$dir = dirname( __FILE__ );

// register message files
$wgMessagesDirs['RecurrentEventCalendar'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['RecurrentEventCalendar'] = $dir . '/RecurrentEventCalendar.i18n.php';
$wgExtensionMessagesFiles['RecurrentEventCalendarMagic'] = $dir . '/RecurrentEventCalendar.magic.php';
$wgExtensionMessagesFiles['RecurrentEventCalendarAlias'] = $dir . '/RecurrentEventCalendar.alias.php';

// register class files with the Autoloader
$wgAutoloadClasses['RECUtils'] = $dir . '/includes/RECUtils.php';
$wgAutoloadClasses['RECSpecialRECEdit'] = $dir . '/includes/RECSpecialRECEdit.php';
$wgAutoloadClasses['RECException'] = $dir . '/includes/RECException.php';
$wgAutoloadClasses['RECPageCreationJob'] = $dir . '/includes/RECPageCreationJob.php';

$wgAutoloadClasses['RECIterator'] = $dir . '/includes/iterators/RECIterator.php';
$wgAutoloadClasses['RECDateIterator'] = $dir . '/includes/iterators/RECDateIterator.php';


// register Special page
$wgSpecialPages['RecurrentEventCalendarEdit'] = 'RECSpecialRECEdit'; # Tell MediaWiki about the new special page and its class name

// register hook handlers

// Specify the function that will initialize the parser function.
$wgHooks['ParserFirstCallInit'][] = 'RECUtils::initParserFunction';

// define constants
define('REC_NOLIMIT', PHP_INT_MAX);

// register iterators
$recgIterators = array (
	'date' => 'RECDateIterator',
);

$recgPageGenerationLimits = array(
	'*' => 0,
	'user' => 10,
	'sysop' => REC_NOLIMIT
);


$wgJobClasses['recCreatePage'] = 'RECPageCreationJob';
