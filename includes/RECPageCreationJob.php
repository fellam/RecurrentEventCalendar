<?php

/**
 * File holding the RECPageCreationJob class
 *
 * @author Michele Fella <michele.fella@gmail.com>
 * @file
 * @ingroup RecurrentEventCalendar
 */
if ( !defined( 'REC_VERSION' ) ) {
	die( 'This file is part of the RecurrentEventCalendar extension, it is not a valid entry point.' );
}

/**
 * The RECPageCreationJob class.
 *
 * @ingroup RecurrentEventCalendar
 */
class RECPageCreationJob extends Job {

	function __construct( $title, $params = '', $id = 0 ) {
		parent::__construct( 'recCreatePage', $title, $params, $id );
	}

	/**
	 * Run the job
	 * @return boolean success
	 */
	function run() {

		global $wgUser, $wgCommandLineMode;

		$oldUser = $wgUser;
		$wgUser = User::newFromId( $this->params['user'] );

		unset( $this->params['user'] );
		
		$this->params['form'] = $this->title->getText();
		$this->params['target'] = '';

		$handler = new SFAutoeditAPI( new ApiMain(), 'sfautoedit' );

		// TODO: Method is removed in SF 2.5 onwards. Remove the whole if-clause
		// when compatibility to SF pre2.5 is dropped
		if ( method_exists( $handler, 'isApiQuery' ) ) {
			$handler->isApiQuery( false );
		}

		$handler->setOptions( $this->params );

		// TODO: Method storeSemanticData is removed in SF 2.5 onwards. Clean this up
		// when compatibility to SF pre2.5 is dropped
		if ( method_exists( $handler, 'storeSemanticData' ) ) {
			$result = $handler->storeSemanticData( false );

			// wrap result in ok/error message
			if ( $result === true ) {
				$options = $handler->getOptions();
				$result = wfMsg( 'sf_autoedit_success', $options['target'], $options['form'] );
			} else {
				$result = wfMsgReplaceArgs( '$1', array( $result ) );
			}
		} else {

			try {
				$handler->execute();
				$options = $handler->getOptions();
				$result = wfMsg( 'sf_autoedit_success', $options['target'], $options['form'] );
			} catch ( MWException $e ) {
				$result = wfMsgReplaceArgs( '$1', array( $result ) );
			}
		}

		$this->params = array( 'result' => $result, 'user' => $wgUser->getName() );
		wfDebugLog( 'rec', 'Page Creation Job: ' . $result );

		$wgUser = $oldUser;

	}

}
