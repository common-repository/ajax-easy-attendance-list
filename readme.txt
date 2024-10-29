=== Ajax Easy Attendance List ===
Contributors: MrFelicity
Tags: attendance
Requires at least: 3.0
Tested up to: 3.5
Stable tag: 0.5.25
License: GPLv2

A simple ajax attendance list

== Description ==

For the local hockey club a small plugin was required, which provides an overview of the attendance of players. The reason was on the irregular attendance. To avoid an empty training with just a few players this plugin was established. Players can easily sign in by just entering their name. Later the attendance can be changed, but never deleted. This plugin provides no checking on the entries on purpose. Everyone can load the page and mark people as not attending.

After a while the Plugin developed and became a tool with rich functionality.

See an example at [wordpress.dasweb.net](http://wordpress.dasweb.net/)

== Installation ==

After installing the plugin has the following settings available:

* Number of attendant: This is the number of minimum/maximum players. If filled it will show a number next to the number of attending players. It will also draw a line in the statistics. The idea is to point out the minimum of maximum players for this training.
* Special Date:  If the attendance list is for a special date, please fill it in here. Please be aware to put the date in the correct format (dd.mm.yyyy). For example 01.03.2011
* Weekly Day: If the training is on a regular weekly basis, please set the day here
* Change status: Allow everyone to edit any status.
* Name for attendant: Which term should be used?
* Input Text: Which text should be appear, if list is entry
* Next date color: You can change the font color of date here
* List width: The width of the list in pixel
* Stats Width: The width of the stats in pixel
* Design: Which stylesheet shall be used?
* Facebook App ID: If you want to have the Facebook support, you need to create a Facebook App and fill in here the Facebook App ID.

Integrating:
After activating the plugin you can add the attendance list by writing
[easyattendancelist]
in your post. If you want to set a width different from the settings you can type
[easyattendancelist width=400]
for a width of 400 pixel. For the statistic you have to add the text
[easyattendancelist_stat]
in your post. It will always generate the new statistic and adds it as a new picture.

If you want to add a list with a different date from the settings please write
[easyattendancelist date=01.03.2015] or [easyattendancelist date=monday]

Name format:
To avoid any hacking on the database the name of players are only allowed to contain letters and spaces. Any extra signs will be deleted before adding to the database. By being signed in the user can add more complicated names. Also it is possible to delete names, while being logged in.

Administration:
As mentioned before, while being logged in an extra link "delete" appears, which provides to delete an entry. The settings, which were mentioned before, have to be done in the administration panel.

== Changelog ==
= 0.5.25 =
* Fixed problems with a change of Facebook's API

= 0.5.21 =
* functions.php has to be ANSI

= 0.5.20 =
* Bug fixes according stats
* Changed picture rendering to on demand 

= 0.5.19 =
* Bug fixes according big user ids of Facebook

= 0.5.17 =
* Bug fixes according big user ids of Facebook
* Security fix
* Name Umlaute fix

= 0.5.14 =
* Small bug fixes according Facebook
* Bug fixes when deleting entries

= 0.5.13 =
* Small bug fixes
* Date format in options
* Facebook fix
* jQuery not loading anymore, since new Wordpress is loading jQuery already

= 0.5.12 =
* Small bug fix

= 0.5.11 =
* Facebook image bug

= 0.5.10 =
* WP Poll compatibility

= 0.5.9 =
* Theme adjustment

= 0.5.8 =
* Theme adjustment

= 0.5.7 =
* Small bug fixes according to Safari compatibility

= 0.5.6 =
* Small bug fixes according to IE6 compatibility

= 0.5.5 =
* Bug fixes (Important)

= 0.5.3 =
* Small bug fixes

= 0.5.1 =
* Small bug fixes

= 0.5 =
* Several designs
* Support of several lists
* Support of Facebook

= 0.4.1 =
* IE6 compatibility

= 0.4 =
* Ajax update
* Loading bar
* change to ul and li
* jQuery UI added
* Autocomplete added

= 0.3.1 =
* Small design fixes

= 0.3 =

* Code optimized
* More dynamic AJAX functions
* No TABLEs, only DIVs
* Stylesheet optimized and commented

= 0.2.12 =
* Small bug fixes

= 0.2.11 =

* Error messages added
* Entry field appears after entry or change
* Doube entry bug fix

= 0.2.10 =

* Bug fix
* width can be changed with shortcode

= 0.2.9 =

* Space is allowed in names

= 0.2.8 =

* Statistic color change
* List width can be changed

= 0.2.6 =

* Statistic reloads automatically at change
* Term Attendant can be changed
* Next date font color can be changed

= 0.2.5 =

* Players changed to attendant

= 0.2.4 =

* Anonymous change of status can be deactivated

= 0.2.3 =

* Small bug fixes according to Wordpress

= 0.2 =

* Small bug fixes

= 0.1 =

* First version

== Screenshots ==

1. Attendance List
2. Attendance List - Statistics
3. Attendance List - Admin Panel