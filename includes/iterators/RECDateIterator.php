<?php

/**
 * File holding the RECDateIterator class
 *
 * @author Michele Fella <michele.fella@gmail.com>
 * @file
 * @ingroup RecurrentEventCalendar
 */
if ( !defined( 'REC_VERSION' ) ) {
	die( 'This file is part of the RecurrentEventCalendar extension, it is not a valid entry point.' );
}

/**
 * The RECDateIterator class.
 *
 * @ingroup RecurrentEventCalendar
 */
class RECDateIterator extends RECIterator {

	/**
	 * @return array An array containing the names of the parameters this iterator uses.
	 */
	function getParameterNames() {
		return array(
			'startday' => rec_MANDATORY,
			'endday' => rec_MANDATORY,
			'starttime' => rec_MANDATORY,
			'endtime' => rec_MANDATORY,
			'isrecurrent' => rec_MANDATORY,
			'recurrentstart' => rec_MANDATORY,
			'recurrentend' => rec_MANDATORY,
			'recurrentunit' => rec_MANDATORY,
			'recurrentperiod' => rec_MANDATORY
		);
	}

	/**
	 * @return an array of the values to be used in the target field of the target form
	 */
	
	function checkValues ( &$data ){
// 		print "data=<div>"; print_r($data); print "</div></br>";
		if ( array_key_exists( 'startday', $data ) ) {
			$startday = $data['startday'];
			if ( is_string( $startday ) ) {
				$startday = explode(' ',$startday);
				if (count($startday) > 1) {$starttime = trim($startday[1]);}
				$startday = trim($startday[0]);
			} else if ( is_array( $startday) &&
				array_key_exists( 'day', $startday ) &&
				array_key_exists( 'month', $startday ) &&
				array_key_exists( 'year', $startday ) ) {
				// start date provided as an array (e.g. by normal date input)
				$startday = trim( $startday['year'] ) . '/' .
					trim( $startday['month'] ) . '/' .
					trim( $startday['day'] ) ;
			} else {
				throw new RECException( RECUtils::buildMessage( 'recerror-date-startdaymissing' ) );
			}
		}
		if ( array_key_exists( 'endday', $data ) ) {
			$endday = $data['endday'];
			if ( is_string( $endday ) ) {
				$endday = explode(' ',$endday);
				if (count($endday) > 1) {$endtime = trim($endday[1]);}
				$endday = trim($endday[0]);
			} else if ( is_array( $endday ) &&
				array_key_exists( 'day', $endday ) &&
				array_key_exists( 'month', $endday ) &&
				array_key_exists( 'year', $endday ) ) {
				$endday = trim( $endday['year'] ) . '/' .
					trim( $endday['month'] ) . '/' .
					trim( $endday['day'] );
			}
			if ( is_null( $endday ) || $endday === '' ) {
				$endday = $startday;
			}
		}
		if ( array_key_exists( 'starttime', $data ) && (is_null($starttime) || ($starttime === '')) ) {
			$starttime = $data['starttime'];
			if ( is_string( $starttime ) ) {
				$starttime = trim( $starttime );
			} else if ( is_array( $starttime ) &&
				array_key_exists( 'hour', $starttime ) &&
				array_key_exists( 'minute', $starttime ) ) {
				$starttime = trim( $starttime['hour'] ) . ':' .
						trim( $starttime['minute'] ) ;
			} 
			if ( is_null( $starttime ) || $starttime === '' ) {
				$starttime = '00:00';
			}
		}
		if ( array_key_exists( 'endtime', $data ) && (is_null($endtime) || ($endtime === '')) ) {
			$endtime = $data['endtime'];
			if ( is_string( $endtime ) ) {
				$endtime = trim( $endtime );
			} else if ( is_array( $endtime ) &&
			array_key_exists( 'hour', $endtime ) &&
			array_key_exists( 'minute', $endtime ) ) {
				$endtime = trim( $endtime['hour'] ) . ':' .
						trim( $endtime['minute'] );
			}
			if ( is_null( $endtime ) || $endtime === '' ) {
				$endtime = '23:59';
			}
		}
		if ( array_key_exists( 'isrecurrent', $data ) ) {
			$isrecurrent = $data['isrecurrent'];
		}else{
			$isrecurrent = 'No';
		}
		if ( $isrecurrent === 'Yes' ) {
			if ( array_key_exists( 'recurrentstart', $data ) ) {
				$recurrentstart = $data['recurrentstart'];
				if ( is_string( $recurrentstart ) ) {
					$recurrentstart = explode(' ',$recurrentstart);
					$recurrentstart = trim($recurrentstart[0]);
				} else if ( is_array( $recurrentstart ) &&
				array_key_exists( 'day', $recurrentstart ) &&
				array_key_exists( 'month', $recurrentstart ) &&
				array_key_exists( 'year', $recurrentstart ) ) {
					// start date provided as an array (e.g. by normal date input)
					$recurrentstart = trim( $recurrentstart['year'] ) . '/' .
							trim( $recurrentstart['month'] ) . '/' .
							trim( $recurrentstart['day'] ) ;
				} 
			}
			if ( array_key_exists( 'recurrentend', $data ) ) {
				$recurrentend = $data['recurrentend'];
				if ( is_string( $recurrentend ) ) {
					$recurrentend = explode(' ',$recurrentend);
					$recurrentend = trim($recurrentend[0]);
				} else if ( is_array( $recurrentend ) &&
				array_key_exists( 'day', $recurrentend ) &&
				array_key_exists( 'month', $recurrentend ) &&
				array_key_exists( 'year', $recurrentend ) ) {
					// start date provided as an array (e.g. by normal date input)
					$recurrentend = trim( $recurrentend['year'] ) . '/' .
							trim( $recurrentend['month'] ) . '/' .
							trim( $recurrentend['day'] ) ;
				} 
			}
		}
// 		print "B - recurrentstart = ".$recurrentstart." - recurrentend = ".$recurrentend."</br>";
		if ( is_null( $recurrentstart ) || $recurrentstart === '' ) {
			$recurrentstart = $startday;
		}
		if ( is_null( $recurrentend ) || $recurrentend === '' ) {
			$recurrentend = $endday;
		}
// 		print "A - recurrentstart = ".$recurrentstart." - recurrentend = ".$recurrentend."</br>";
		$recurrentperiod = RECUtils::fromArray( $data, 'recurrentperiod', 1 );
		$recurrentunit   = RECUtils::fromArray( $data, 'recurrentunit', 'day' );

		$values = array(
			'startday' => $startday,
			'endday' => $endday,
			'starttime' => $starttime,
			'endtime' => $endtime,
			'isrecurrent' => $isrecurrent,
			'recurrentstart' => $recurrentstart,
			'recurrentend' => $recurrentend,
			'recurrentunit' => $recurrentunit,
			'recurrentperiod' => $recurrentperiod,
		);
		return $values;
	}

	/**
	 * @return an array of the values to be used in the target field of the target form
	 */
	function getValues ( $start, $end, $unit, $period, $userDateFormat ){
		// TODO: SMWSetRecurringEvent does not exist from SMW 1.9 onwards
		// remove when compatibility to SMW pre1.9 is dropped
		if ( class_exists( 'SMWSetRecurringEvent' ) ) {
			//prepare params for getDatesForRecurringEvent
			$params = array(
				'property=SomeDummyProperty',
				'start=' . $start,
				'end=' . $end,
				'unit=' . $unit,
				'period=' . $period,
			);
			
			$values = SMWSetRecurringEvent::getDatesForRecurringEvent( $params );
						
			if ( $values === null ) {
				throw new RECException( RECUtils::buildMessage( 'recerror-date-internalerror', 'Unknown error. This could be due to a malformed start or end date.' ) );
			}
			
			$values = $values[1];
			
		} else { // SMW 1.9 and later
			//prepare params for getDatesForRecurringEvent
			$params = array(
				'property' => array( 'SomeDummyProperty' ),
				'start' => array( $start ),
				'end' => array( $end ),
				'unit' => array( $unit ),
				'period' => array( $period ),
			);

			$settings = SMW\Settings::newFromArray( array(
						'smwgDefaultNumRecurringEvents' => $GLOBALS['smwgDefaultNumRecurringEvents'],
						'smwgMaxNumRecurringEvents' => $GLOBALS['smwgMaxNumRecurringEvents'] )
			);

			$events = new SMW\RecurringEvents( $params, $settings );
			
			$values = $events->getDates();
			
			if ( $values === null || count( $events->getErrors() ) > 0 ) {
				throw new RECException( RECUtils::buildMessage( 'recerror-date-internalerror', implode( ' ', $events->getErrors() ) ) );
			}
		}

		// if the first date did not contain a time, remove the time from all
		// generated dates
		if ( preg_match( '/.:../', $values[0] ) === 0 ) {
			foreach ( $values as $key => $value ) {
				$values[$key] = trim( preg_replace( '/..:..:../', '', $value ) );
			}
		}
		$formattedvalues = array();
		//return values ad format
// 		print "userDateFormat=".$userDateFormat."</br>";
// 		print "values=".$values."</br>";
		foreach ($values as &$value) {
// 			print "	VAL=".$value."</br>";
			$date = new DateTime($value);
// 			print "	DATE=".$value."</br>";
			$formattedvalues[] = $date->format($userDateFormat);
		}
// 		print "formattedvalues=<div>"; print_r($formattedvalues); print "</div></br>";
// 		print "values=<div>"; print_r($values); print "</div></br>";
		return $formattedvalues;
	}
	
}
