=== LH Email Registration ===
Contributors: shawfactor
Donate link: http://lhero.org/plugins/lh-email-registration/
Tags: email, backend, wp-admin, admin, registration
Requires at least: 3.0.
Tested up to: 4.7
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Streamlines user registration in the back end by allowing administrators to remove redundant fields and replaces usernames with email addresses

== Description ==

This plugin allows the following you to configure the backend new user screen to incorporate the following features

* Optional use of email addresses only instead of username and email.
* Optionally hide the new user url field
* Optionally allow you to specify whether an email should be sent to each new user or not

These changes make adding new users to WordPress a lot faster.

One word of caution is that IF YOU USE EMAL ADDRESSES ONLly the plugin replaces the username with the email address of the user when a user is saved in the database. meaning your users will no longer have a separate username. 

Check out [our documentation][docs] for more information.

All tickets for the project are being tracked on [GitHub][].

[docs]: http://lhero.org/plugins/lh-email-registration/
[GitHub]: https://github.com/shawfactor/lh-email-registration

== Installation ==

1. Upload the entire `lh-email-registration` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure the setting via Settings->LH Email Registration
3. Start adding user using the streamlined form via Users->Add New


== Changelog ==

**1.0 July 30, 2015**  
Initial release.

**2.0 July 30, 2015**  
Complete overhaul for WordPress 4.3

**2.1 December 01, 2015**  
Multisite safe


**2.20 April 20, 2017**  
Multiple improvements