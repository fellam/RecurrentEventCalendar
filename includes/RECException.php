<?php

/**
 * File holding the RECException class
 * 
 * @author Michele Fella <michele.fella@gmail.com>
 * @file
 * @ingroup RecurrentEventCalendar
 */
if ( !defined( 'REC_VERSION' ) ) {
	die( 'This file is part of the RecurrentEventCalendar extension, it is not a valid entry point.' );
}

/**
 * The RECException class.
 *
 * @ingroup RecurrentEventCalendar
 */
class RECException extends MWException {

	/**
	 * Return a HTML message.
	 * 
	 * Overrides method from MWException: We don't need a backtrace
	 * 
	 * @return String html to output
	 */
	function getHTML() {
		return Html::rawElement( 'p', array('class' => 'recerror'), nl2br( $this->getMessage() ) ) ;
	}

	/**
	 * Return a text message.
	 * 
	 * Overrides method from MWException: We don't need a backtrace
	 * 
	 */
	function getText() {
		return $this->getMessage();
	}

	/**
	 * Return titles of this error page
	 * 
	 * Overrides method from MWException: We have a different page title
	 * 
	 */
	function getPageTitle() {
		if ( $this->useMessageCache() ) {
			return wfMsgForContent( 'recerror' );
		} else {
			global $wgSitename;
			return "$wgSitename error";
		}
	}

}
