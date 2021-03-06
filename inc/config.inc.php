<?php
// DO NOT EDIT THIS FILE!
// Deviations from these default values go to config.local.inc.php!
$version	= '1.0.1';

// This is for logging. Unless you experience bugs and want to help fixing them,
// leave these settings as they are.
$cfg['logger']['facility']	= 'null';
$cfg['logger']['name']		= '';
// Beware: Do not set this to true or your connection data might get revealed.
$cfg['show_exceptions_online']	= false;

// If language detection fails, use that language as fallback.
$default_lang = 	$lang	= 'en';
$cfg['force_default_language']	= false;

// You may want to move some files to a second webserver
// in order to decrease the load on your SSL-enabled one.
// (Directories out of the client's browser's view.)
$cfg['images_dir']	= 'images';	// This is where you store all the images
$cfg['design_dir']	= 'design';	// CSS

// You do not intend to have all 3000 mailboxes displayed on one page, do you?
// (Set to false for no upper limit.)
$cfg['max_elements_per_page']	= 25;
// set this to true and in mailbox-view the owner's name is shown and not the mailbox name.
$cfg['mboxview_pers']		= false;
// Preferred date and time format for displaying.
$cfg['date_format']		= 'Y-m-d';

// Allow creation of catchalls? (i.e.: @example.com)
$cfg['address']['allow_catchall']	= true;
// Shall creation of catchalls be limited to the owner of that domain?
$cfg['address']['restrict_catchall']	= true;
// If an address has at least that many destinations, it will be hidden.
$cfg['address']['hide_threshold']	= 5;
// To disable this feature set these to a ridiculous high number:
$cfg['address']['max_dest_p_catchall']	= 4;
$cfg['address']['max_dest_p_address']	= 16000;
// Shall unneccesary whitespace between tags be removed?
// Setting this to false will make Firefox' odd DOM implementation obvious!
$cfg['remove_whitespace']		= true;

// Shall we accept other user's mailbox-names as targets?
// Only use this if you have few mailboxes!
$cfg['allow_mbox_as_target']	= true;
// Moved from Webcyradm? An specialized equivalent:
$cfg['allow_wcyr_as_target']	= false;

// Shall the canonical-address be created along with the new mailbox?
// (Set this to false if your users may own several mailboxes with same canonicals
//  or if external addresses occur here.)
$cfg['create_canonical']	= true;

// Set this to true and domain-administrators may delete "their" domains,
// else only the owner is allowed to. If true implies, that admins cannot
// change ownership of domains which are not their own.
$cfg['admins_delete_domains']	= false;

// Comment out the following if you not wish to get these subfolders created.
$cfg['folders']['create_default']	= array('Trash','UMS');

// Pretend these users don't exist. Creation of these mailboxes will be denied.
$cfg['user_ignore']		= array('cyrus');
// Default dimensions for mailbox names.
$cfg['mbox']['min_length']	= 4;
$cfg['mbox']['max_length']	= 16;
// If a user logged in since this amount of seconds his entry will be highlighted.
// Set to 0 to deactivate.
$cfg['mboxview_sec']		= 5*86400;

// Name of class to handle ciphering of passwords. Defaults to PasswordMD5.
// See inc/lib/Password* for available ciphering strategies.
$cfg['passwd']['strategy']	= 'PasswordMD5';
// Default settings for passwords.
$cfg['passwd']['min_length']	= 8;
$cfg['passwd']['max_length']	= 16;

// If this file exists it will be shown on login.
$cfg['motd']		= 'motd.txt';

// Select one of: shadow, blueray
$cfg['theme']		= 'shadow';

$i = 0;
/*
 * Please see samples/config.local.inc.php for appropriate database configuration.
 * Change connection settings in delivered SQL- and pam-files, too!
 */

?>