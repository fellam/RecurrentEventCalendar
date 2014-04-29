RecurrentEventCalendar
======================

This Mediawiki extension allows user to create multiple events using a single form

For details click [here](https://www.mediawiki.org/Extension:RecurrentEventCalendar) 

=======
Please help me improving this project making a donation [here](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=UBX4YGMGGWHEN)

=======
== Dependencies ==

This extension was developed for MediaWiki 1.17 with at least Semantic
MediaWiki 1.5.3 and Semantic Forms 2.4 installed. Other version might work, but
are not tested.

=======
== Installation ==

1. Download the package. Unpack the folder inside /extensions (so that the files
   are in /extensions/RecurrentEventCalendar, rename the folder if necessary).

2. In your LocalSettings.php, add the following line to the end of the file:

   require_once("$IP/extensions/RecurrentEventCalendar/RecurrentEventCalendar.php");

=== Configuration parameters ===

The following settings may be used:

* $recgPageGenerationLimits to specify the maximum number of pages that may be
  generated per request by a member of a user group. If a user is in more than
  one group, the highest number is used.

  Default setting:

    $recgPageGenerationLimits = array(
      '*' => 0,
      'user' => 10,
      'sysop' => REC_NOLIMIT
    );

If you want to use these settings, just include them in LocalSettings.php AFTER
the require_once("$IP/extensions/RecurrentEventCalendar/RecurrentEventCalendar.php");
