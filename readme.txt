=== HelloAdhérents ===
Contributors: drcode
Donate link: 
Tags: HelloAsso, API, Google groups, Mailchimp
Requires at least: 4.7
Tested up to: 6.0.1
Stable tag: 1.2.4
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Retrieve data from HelloAsso and use it to automatically update mailing lists, and more.

== Description ==

This plugin is made to automate some tasks for users of HelloAsso, a platform for handling association memberships, with great customization possibilities.

It uses a Cron job, at the frequency of your choice, to perform :
* A HelloAsso API call to retrieve your association members' data, such as Email address, Name, and any other data they provided during sign-in
* The outputting of that data to different services

For now, the possible outputs are :
* Google groups : use Google API to add members' email addresses to the group(s) of your choice
* Mailchimp : use Mailchimp API to add or update members, including for merge fields and tags  
* Wordpress : create Wordpress user accounts on your site, and warn your members via automatic email
* Custom function : execute any local PHP function with the data outputted from the HelloAsso API call

To help you for troubleshooting if necessary, every of these functions have a testing feature directly in the HelloAdherents settings page, and systematically output a response message in a log.txt file.

== Frequently Asked Questions ==

= Can I handle multiple HelloAsso campaigns with this plugin ? =

Not for now. This option might be coming if needed.

= Can I separate members into different Google groups ?  =

Yes, if you have set multiple prices ("tarifs") for your HelloAsso campaign, you will be able to divide members into different groups based on that criterion. More options might be coming if needed. 

= What about the Mailchimp Merge Fields ?  =

Mailchimp merge fields are useful for quickly displaying data in your audience, and filter your contacts into specific segments. 
Default merge fields include First Name (FNAME), Last Name (LNAME), Address (ADDRESS), Phone number (PHONE NUMBER) and Birthday (BIRTHDAY), but these are customizable, and you can add more of them.

For now, the Address and Birthday merge fields are not supported by HelloAdherent. This option might be coming if needed.

Any other merge field, including custom ones that you created, are supported.

= I need to use the members' data for something else. Is that possible ?  =

Yes ! With the custom function part, you can write your own PHP function, register it where you like (child theme, your own plugin...) and use it to do whatever you want with the HelloAsso data. You could write anything to fit exactly your needs !

== Screenshots ==

== Changelog ==

= 1.0 =
* First instance

= 1.0.1 =
* Adds separation of log files by month and deletion of log files older than 1 month

= 1.0.2 =
* Corrected http-user-agent parameter which caused a failure to retrieve HelloAsso data when run from the cron job
* Corrected the date query to make sure HelloAsso data is most up to date

= 1.1.0 =
* Added a section for built-in Wordpress User account creation, including automatic email for password reset

= 1.2.0 =
* Added a field for Wordpress User's biographical description. Bug fixes for PHP 8

= 1.2.2 =
* Added the possibility to retrieve discount code

= 1.2.4 =
* Fixed syntax error preventing Mailchimp and Google modules from working

== Upgrade Notice ==

= 1.0 =
First instance

= 1.0.1 =
* Adds separation of log files by month and deletion of log files older than 1 month

= 1.0.2 =
* Corrected http-user-agent parameter which caused a failure to retrieve HelloAsso data when run from the cron job
* Corrected the date query to make sure HelloAsso data is most up to date

= 1.1.0 =
* Added a section for built-in Wordpress User account creation, including automatic email for password reset

= 1.2.0 =
* Added a field for Wordpress User's biographical description. Bug fixes for PHP 8

= 1.2.2 =
* Added the possibility to retrieve discount code

= 1.2.4 =
* Fixed syntax error preventing Mailchimp and Google modules from working