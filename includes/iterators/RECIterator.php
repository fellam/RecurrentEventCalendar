<?php

/**
 * File holding the RECIterator class
 * 
 * @author Michele Fella <michele.fella@gmail.com>
 * @file
 * @ingroup RecurrentEventCalendar
 */
if ( !defined( 'REC_VERSION' ) ) {
	die( 'This file is part of the RecurrentEventCalendar extension, it is not a valid entry point.' );
}

	define('rec_OPTIONAL', 0);
	define('rec_MANDATORY', 1);
	
/**
 * The RECIterator class.
 *
 * @ingroup RecurrentEventCalendar
 */
abstract class RECIterator {

	/**
	 * @return array An array containing the names of the parameters this iterator uses.
	 */
	abstract function getParameterNames();
	
	/**
	 * @return an array of the checked values to be used in the target field of the target form
	 */
	abstract function checkValues ( &$data );
	
	/**
	 * @return an array of the values to be used in the target field of the target form
	 */
	abstract function getValues ( $start, $end, $unit, $period );
	
}
