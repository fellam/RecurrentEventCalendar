<?php

/**
 * File holding the RECSpecialRECEdit class
 *
 * @author Michele Fella <michele.fella@gmail.com>
 * @file
 * @ingroup RecurrentEventCalendar
 */
if ( !defined( 'REC_VERSION' ) ) {
	die( 'This file is part of the RecurrentEventCalendar extension, it is not a valid entry point.' );
}

/**
 * The RECSpecialRECEdit class.
 *
 * @ingroup RecurrentEventCalendar
 */
class RECSpecialRECEdit extends SpecialPage {

	public function __construct() {
		parent::__construct( 'RecurrentEventCalendarEdit' );
	}

	public function execute( $parameters ) {
		global $wgRequest;

		$this->setHeaders();
// 		print "wgRequest=<div>"; print_r($wgRequest); print "</div>";
		if ( $wgRequest->getCheck( 'wpDiff' ) ) {

			// no support for the diff action
			throw new RECException( RECUtils::buildMessage( 'recerror-diffnotsupported' ) );

		} elseif ( $wgRequest->getCheck( 'wpPreview' ) ) {

			// no support for the preview action
			throw new RECException( RECUtils::buildMessage( 'recerror-previewnotsupported' ) );

		} elseif ( $wgRequest->getCheck( 'wpSave' ) ) {

			// saving requested
			$this->evaluateForm( $wgRequest );

		} elseif ( isset( $_SESSION ) && count( $_SESSION ) > 0 && isset( $_SESSION['recForm'] ) && isset( $_SESSION['recResult'] ) ) {

			// cookies enabled and result data stored
			$this->printSuccessPage( $_SESSION['recForm'], $_SESSION['recResult'], $_SESSION['recOrigin'] );

			unset( $_SESSION['recForm'] );
			unset( $_SESSION['recResult'] );
			unset( $_SESSION['recOrigin'] );

		} elseif ( ( !isset( $_SESSION ) || count( $_SESSION ) ) && count( $_GET ) === 2 ) {

			// cookies disabled, try getting result data from URL

			$get = $_GET;
			unset( $get['title'] );
			$get = array_keys($get);
			$get = explode( ';', $get[0], 3);

			$this->printSuccessPage( $get[0], $get[1], $get[2] );

		} else {

			// no action requested, show form
			$this->printForm( $parameters, $wgRequest );
		}
	}

	private function printForm( &$parameters, WebRequest &$request ) {
		global $wgOut, $sfgFormPrinter;
		// Prepare parameters for SFFormPrinter::formHTML
		// there is no ONE target page
		$targetTitle = null;
		// formDefinition
		$formName = $request->getText( 'form' );
		// if query string did not contain these variables, try the URL
		if ( $formName === '' ) {
			$queryparts = explode( '/', $parameters );
			$formName = isset( $queryparts[0] ) ? $queryparts[0] : null;

			// if the form name wasn't in the URL either, throw an error
			if ( is_null( $formName ) || $formName === '' ) {
				throw new RECException( RECUtils::buildMessage( 'recerror-noformname' ) );
			}
		}
		$formTitle = Title::makeTitleSafe( SF_NS_FORM, $formName );
		if ( !$formTitle->exists() ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-formunknown', $formName ) );
		}
		$formArticle = new Article( $formTitle );
		$formDefinition = StringUtils::delimiterReplace( '<noinclude>', '</noinclude>', '', $formArticle->getContent() );
		// formSubmitted
		$formSubmitted = false;
		// pageContents
		$pageContents = null;
		// get 'preload' query value, if it exists
		if ( $request->getCheck( 'preload' ) ) {
			$pageContents = SFFormUtils::getPreloadedText( $request->getVal( 'preload' ) );
		} else {
			// let other extensions preload the page, if they want
			wfRunHooks( 'sfEditFormPreloadText', array(&$pageContents, $targetTitle, $formTitle) );
		}
		// pageIsSource
		$pageIsSource = ( $pageContents != null );
		// get the iterator parameters
		$iteratorData = $this->buildIteratorParameters( $request );
		// Call SFFormPrinter::formHTML
		list ( $formText, $javascriptText, $dataText, $formPageTitle, $generatedPageName ) =
			$sfgFormPrinter->formHTML( $formDefinition, $formSubmitted, $pageIsSource, $formArticle->getID(), $pageContents );
		// Set Special page main header;
		// override the default title for this page if a title was specified in the form
		if ( $formPageTitle != null ) {
			$wgOut->setPageTitle( $formPageTitle );
		} else {
			$wgOut->setPageTitle( RECUtils::buildMessage( 'sf_formedit_createtitlenotarget', $formTitle->getText() ) );
		}
		$preFormHtml = '';
		wfRunHooks( 'sfHTMLBeforeForm', array(&$targetTitle, &$preFormHtml) );
		$text = '<form name="createbox" id="sfForm" action="" method="post" class="createbox">'
			. $preFormHtml
			. "\n"
			. Html::hidden( 'iteratordata', $iteratorData )
			. Html::hidden( 'formName', $formName )
			. $formText;
		SFUtils::addJavascriptAndCSS();
		if ( !empty( $javascriptText ) ) {
			$wgOut->addScript( '		<script type="text/javascript">' . "\n$javascriptText\n" . '</script>' . "\n" );
		}
		$wgOut->addHTML( $text );
		return null;
	}

	private function evaluateForm( WebRequest &$request ) {
		global $wgUser, $recgIterators;
		$requestValues = $_POST;
// 		print "requestValues=<div>"; print_r($requestValues); print "</div></br>";
		if ( array_key_exists( 'iteratordata', $requestValues ) ) {
			$iteratorData = FormatJson::decode( $requestValues['iteratordata'], true );
			unset( $requestValues['iteratordata'] );
		} else {
			throw new RECException(  RECUtils::buildMessage( 'recerror-noiteratordata' ) );
		}
		if ( array_key_exists( 'keep_parameters', $requestValues ) ) {
			$keepParameters = FormatJson::decode( $requestValues['keep_parameters'], false );
		}
		$targetFormName = $request->getText( 'formName' );
		$iteratorName = null;
		$originPageId = null;
		
		print "iteratorData=<div>"; print_r($iteratorData); print "</div></br>";
// 		print "requestValues=<div>"; print_r($requestValues); print "</div></br>";
		
		foreach ( $iteratorData as $param => $value ) {
// 			print($param." => ".$value."</br>");
			switch ( $param ) {
				case 'iterator':
					// iteratorName
					$iteratorName = $value;
					break;
				case 'origin':
					$originPageId = $value;
					break;
				case 'keep_parameters':
					$keepParameters = true;
					break;
				default :
					$iteratorParams[$param] = $this->getAndRemoveFromArray( $requestValues, $value, $keepParameters );
// 					print " P - ".$param." = ".$value."</br>";
			}
		}
		if ( is_null( $iteratorName ) || $iteratorName === '' ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-noiteratorname' ) );
		}
		if ( !array_key_exists( $iteratorName, $recgIterators ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-iteratorunknown', $iteratorName ) );
		}
		// iterator
		$iterator = new $recgIterators[$iteratorName];	
// 		print "iteratorParams=<div>"; print_r($iteratorParams); print "</div></br>";
		print " B - iteratorParams['starttime'] = ".$iteratorParams['starttime']."</br>";
		print " B - iteratorParams['endtime'] = ".$iteratorParams['endtime']."</br>";
		$iteratorParams = $iterator->checkValues( $iteratorParams );
		print " A - iteratorParams['starttime'] = ".$iteratorParams['starttime']."</br>";
		print " A - iteratorParams['endtime'] = ".$iteratorParams['endtime']."</br>";
// 		print "iteratorParams=<div>"; print_r($iteratorParams); print "</div></br>";
		SFAutoeditAPI::addToArray( $requestValues, $iteratorData['isrecurrent'], $iteratorParams['isrecurrent'], true );
		if ( $iteratorParams['isrecurrent'] === 'No' ) {
			$iteratorStartValues = $iterator->getValues( $iteratorParams['startday'], $iteratorParams['endday'], $iteratorParams['recurrentunit'], $iteratorParams['recurrentperiod'] );
			$iteratorEndValues = $iterator->getValues( $iteratorParams['endday'], $iteratorParams['endday'], $iteratorParams['recurrentunit'], $iteratorParams['recurrentperiod'] );
// 			print "iteratorStartValues=<div>"; print_r($iteratorStartValues); print "</div></br>";
// 			print "iteratorEndValues=<div>"; print_r($iteratorEndValues); print "</div></br>";
			$iteratorParams['startday'] = $iteratorStartValues[0];
			$iteratorParams['endday'] = $iteratorEndValues[0];
// 			print "iteratorParams['startday']=<div>"; print_r($iteratorParams['startday']); print "</div></br>";
// 			print "iteratorParams['endday']=<div>"; print_r($iteratorParams['endday']); print "</div></br>";
			$iteratorValuesCount = 1;
		} else if ( $iteratorParams['isrecurrent'] === 'Yes' ) {
			$iteratorStartValues = $iterator->getValues( $iteratorParams['startday'], $iteratorParams['recurrentend'], $iteratorParams['recurrentunit'], $iteratorParams['recurrentperiod'] );
			$iteratorEndValues = $iterator->getValues( $iteratorParams['endday'], $iteratorParams['recurrentend'], $iteratorParams['recurrentunit'], $iteratorParams['recurrentperiod'] );
// 			$iteratorParams['startday'] = $iteratorStartValues[0];
// 			$iteratorParams['endday'] = $iteratorEndValues[count($iteratorEndValue)-1];
// 			$iteratorParams['recurrentstart'] = $iteratorStartValues[0];
// 			$iteratorParams['recurrentend'] = $iteratorEndValues[count($iteratorEndValue)-1];
			$iteratorValuesCount = count( $iteratorEndValues );
			$userlimit = $this->getPageGenerationLimit();
			// check userlimit
			if ( $iteratorValuesCount > $userlimit ) {
				throw new RECException( RECUtils::buildMessage( 'recerror-pagegenerationlimitexeeded', $iteratorValuesCount, $userlimit ) );
			}	
		}
		$targetFormTitle = Title::makeTitleSafe( SF_NS_FORM, $targetFormName );
		$targetFormPageId = $targetFormTitle->getArticleID();
		$requestValues['user'] = $wgUser->getId();
// 		print "BEFORErequestValues=<div>"; print_r($requestValues); print "</div></br>";

		print " - starttime = ".$iteratorData['starttime']." - ".$iteratorParams['starttime']."</br>";
		print " - endtime = ".$iteratorData['endtime']." - ".$iteratorParams['endtime']."</br>";
		
		SFAutoeditAPI::addToArray( $requestValues, $iteratorData['startday'], $iteratorParams['startday'], true );
		SFAutoeditAPI::addToArray( $requestValues, $iteratorData['endday'], $iteratorParams['endday'], true );
		SFAutoeditAPI::addToArray( $requestValues, $iteratorData['starttime'], $iteratorParams['starttime'], true );
		SFAutoeditAPI::addToArray( $requestValues, $iteratorData['endtime'], $iteratorParams['endtime'], true );
		SFAutoeditAPI::addToArray( $requestValues, $iteratorData['recurrentstart'], $iteratorParams['recurrentstart'], true );
		SFAutoeditAPI::addToArray( $requestValues, $iteratorData['recurrentend'], $iteratorParams['recurrentend'], true );
		SFAutoeditAPI::addToArray( $requestValues, $iteratorData['recurrentunit'], $iteratorParams['recurrentunit'], true );
		SFAutoeditAPI::addToArray( $requestValues, $iteratorData['recurrentperiod'], $iteratorParams['recurrentperiod'], true );
// 		print "AFTERrequestValues=<div>"; print_r($requestValues); print "</div></br>";
		
// 		print "iteratorParams=<div>"; print_r($iteratorParams); print "</div></br>";
// 		print "iteratorValues=<div>"; print_r($iteratorValues); print "</div></br>";
		
		if ( $iteratorParams['isrecurrent'] === 'Yes' ) {
			foreach ( $iteratorEndValues as $key => $value ) {
// 				print "YESrequestValues</br>";
// 				print " - KEY = ".$key." - startday = ".$iteratorStartValues[$key]." - endday ".$value."</br>";
// 				print " - EventRecurrentEvery = ".$requestValues['Event']['EventRecurrentEvery']."</br>";
// 				print " - EventRecurrentPeriod = ".$requestValues['Event']['EventRecurrentPeriod']."</br>";
				SFAutoeditAPI::addToArray( $requestValues, $iteratorData['startday'], $iteratorStartValues[$key], true );
				SFAutoeditAPI::addToArray( $requestValues, $iteratorData['endday'], $value, true );
				print "YESrequestValues=<div>"; print_r($requestValues); print "</div></br>";
				wfDebugLog( 'rec', 'Insert RECPageCreationJob' );
				$job = new RECPageCreationJob( $targetFormTitle, $requestValues );
				$job->insert();
			}
		} else if ( $iteratorParams['isrecurrent'] === 'No' ) {
			print "NOrequestValues=<div>"; print_r($requestValues); print "</div></br>";
			wfDebugLog( 'rec', 'Insert RECPageCreationJob' );
			$job = new RECPageCreationJob( $targetFormTitle, $requestValues );
			$job->insert();
		} else {
			throw new RECException( RECUtils::buildMessage( 'recerror-notargetisrecurrent') );
		}
		// if given origin page does not exist use Main page
		if ( Title::newFromID( $originPageId ) === null ) {
			$originPageId = Title::newMainPage()->getArticleID();
		}

		if ( isset( $_SESSION ) ) {
			// cookies enabled
			$request->setSessionData( 'recResult', $iteratorValuesCount );
			$request->setSessionData( 'recForm', $targetFormPageId );
			$request->setSessionData( 'recOrigin', $originPageId );
			header( 'Location: ' . $this->getTitle()->getFullURL() );
		} else {
			// cookies disabled, write result data to URL
			header( 'Location: ' . $this->getTitle()->getFullURL() . "?$targetFormPageId;$iteratorValuesCount;$originPageId" );
		}
		return null;
	}

	private function printSuccessPage( $formId, $createdPages, $originId ) {
// 		print "printSuccessPage</br>";
		global $wgOut;

		$originTitle = Title::newFromID( $originId );

		$wgOut->setPageTitle( RECUtils::buildMessage( 'recsuccesstitle', Title::newFromID( $formId )->getText() ));
		$wgOut->addHTML(
			Html::rawElement( 'p', array( 'class' => 'recsuccess' ), RECUtils::buildMessage( 'recsuccess', $createdPages ) ) .
			(($originTitle !== null)?Html::rawElement( 'p', array( 'class' => 'recsuccess-returntoorigin' ), RECUtils::buildMessage(
					'recsuccess-returntoorigin', '[[' . $originTitle->getPrefixedText() . ']]'
				)
			):'')
		);
	}

	/**
	 * Builds a JSON blob of the data required to use the iterator.
	 * @param WebRequest $request
	 * @return type
	 */
	private function buildIteratorParameters( WebRequest &$request ) {
		global $recgIterators;
		// iteratorName
		$iteratorName = $request->getVal( 'iterator' );
		if ( is_null( $iteratorName ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-noiteratorname' ) );
		}
		if ( !array_key_exists( $iteratorName, $recgIterators ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-iteratorunknown', $iteratorName ) );
		}
// 		print "iteratorName=<div>"; print_r($iteratorName); print "</div></br>";
		// iterator
		$iterator = new $recgIterators[$iteratorName];
		// keep parameters?
		$keepParams = $request->getVal( 'keep_parameters' ) !== null;
		// startdate
		$startdate = $request->getVal( 'startday' );
		if ( is_null( $startdate ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-notargetstartdate' ) );
		}
		// enddate
		$enddate = $request->getVal( 'endday' );
		if ( is_null( $enddate ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-notargetenddate' ) );
		}
		// starttime
		$starttime = $request->getVal( 'starttime' );
		if ( is_null( $starttime ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-notargetstarttime' ) );
		}
		// endtime
		$endtime = $request->getVal( 'endtime' );
		if ( is_null( $endtime ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-notargetendtime' ) );
		}
		// isrecurrent
		$isrecurrent = $request->getVal( 'isrecurrent' );
		if ( is_null( $isrecurrent ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-notargetisrecurrent' ) );
		}
		// recurrentstart
		$recurrentstart = $request->getVal( 'recurrentstart' );
		if ( is_null( $recurrentstart ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-notargetrecurrentstart' ) );
		}
		// recurrentend
		$recurrentend = $request->getVal( 'recurrentend' );
		if ( is_null( $recurrentend ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-notargetrecurrentend' ) );
		}
		// recurrentevery
		$recurrentevery = $request->getVal( 'recurrentunit' );
		if ( is_null( $recurrentevery ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-notargetrecurrentunit' ) );
		}
		// recurrentperiod
		$recurrentperiod = $request->getVal( 'recurrentperiod' );
		if ( is_null( $recurrentperiod ) ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-notargetrecurrentperiod' ) );
		}
		$params = array(
			'iterator' => $iteratorName,
			'startday' => $startdate,
			'endday' => $enddate,
			'starttime' => $starttime,
			'endtime' => $endtime,
			'isrecurrent' => $isrecurrent,
			'recurrentstart' => $recurrentstart,
			'recurrentend' => $recurrentend,
			'recurrentunit' => $recurrentevery,
			'recurrentperiod' => $recurrentperiod,
			'origin' => $request->getVal( 'origin' )
		);
		if ($keepParams) {
			$params['keep_parameters'] = true;
		}
		// add the iterator-specific values
		$paramNames = $iterator->getParameterNames();
		$errors = '';
		foreach ( $paramNames as $paramName => $paramOptional ) {
			$param = $request->getVal( $paramName );
			if ( is_null( $param )  ) {
				if ( $paramOptional === rec_MANDATORY) {
					// mandatory parameter missing
					$errors .= "* $paramName\n";
				}
			} else {
				$params[$paramName] = $param;
			}
		}
		if ( $errors !== '' ) {
			throw new RECException( RECUtils::buildMessage( 'recerror-iteratorparammissing', $errors ) );
		}
		return FormatJson::encode( $params );
	}

	/**
	 * This function recursively retrieves a value from an array of arrays and deletes it.
	 * $key identifies path.
	 * Format: 1stLevelName[2ndLevel][3rdLevel][...], i.e. normal array notation
	 * $toplevel: if this is a toplevel value.
	 *
	 * @param type $array
	 * @param type $key
	 * @param type $toplevel
	 */
	private function getAndRemoveFromArray( &$array, $key, $keepParameters = false, $toplevel = true ) {
		$matches = array();

		if ( array_key_exists( $key, $array ) ) {
			$value = $array[$key];
			if ( !$keepParameters ) {
				unset( $array[$key] );
			}
			return $value;
		} elseif ( preg_match( '/^([^\[\]]*)\[([^\[\]]*)\](.*)/', $key, $matches ) ) {

			// for some reason toplevel keys get their spaces encoded by MW.
			// We have to imitate that.
			// FIXME: Are there other cases than spaces?
			if ( $toplevel ) {
				$key = str_replace( ' ', '_', $matches[1] );
			} else {
				$key = $matches[1];
			}

			if ( !array_key_exists( $key, $array ) ) {
				return null;
			}

			$value = $this->getAndRemoveFromArray( $array[$key], $matches[2] . $matches[3], $keepParameters, false );

			if ( empty( $array[$key] ) ) {
				unset( $array[$key] );
			}

			return $value;
		} else {
			// key not found in array
			return null;
		}
	}

	public function getPageGenerationLimit() {
// 		print "getPageGenerationLimit</br>";
		global $wgUser, $recgPageGenerationLimits;

		$limit = 0;
		$groups = $wgUser->getEffectiveGroups();

		foreach ( $groups as $group ) {
			if ( array_key_exists( $group, $recgPageGenerationLimits) ) {
				$limit = max($limit, $recgPageGenerationLimits[$group]);
			}
		}

		return $limit;
	}

}
