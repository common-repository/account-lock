=== Account Lock ===
Contributors: Dennis Hoppe
Tags: account, user, lock, block        widget,Post,plugin,admin,posts,sidebar,comments,google,images,page,image,links
Requires at least: 3.1
Tested up to: 3.8.1
Stable tag: trunk

This plugin enables administrators to lock user accounts without deleting the users posts and contents.


== Description ==
[Account Lock](http://dennishoppe.de/en/wordpress-plugins/account-lock) is a state of the art [WordPress Plugin](http://dennishoppe.de/en/wordpress-plugins/account-lock) which enables administrators to lock user accounts without deleting the users posts and contents. Locked users will not be able to login or reset their password. All content created by users whose accounts are locked keep accessible by website visitors.


= Main features =
* Lock user accounts via the "Edit User" Screen
* Prohibit users to reset their passwords if their account is locked
* See the current account status in the user management section
* Fast status change directly from the user management page


= Questions & Support =
Unfortunately I cannot give support for the free plugins. There is a separate support package available for the [Pro Version](http://dennishoppe.de/en/wordpress-plugins/account-lock) of this plugin. Please use it. Of course you can hire me for consulting, support, programming and customizations at any time.


= Language =
* This Plugin is available in English.
* Diese Erweiterung ist in Deutsch verfügbar. ([Dennis Hoppe](http://DennisHoppe.de/))
* Dette plugin er tilgængelig på Dansk. ([Thomas Jensen](http://spisestuestole.dk/))


= Translate this plugin =
If you have translated this plugin in your language feel free to send me the language file (.po file) via E-Mail with your name and this translated sentence: "This plugin is available in %YOUR_LANGUAGE_NAME%." So i can add it to the plugin.

You can find the *Translation.pot* file in the *language/* folder in the plugin directory.

* Copy it.
* Rename it (to your language code).
* Translate everything.
* Send it via E-Mail to &lt;Mail [@t] [DennisHoppe](http://DennisHoppe.de/) [dot] de&gt;.
* Thats it. Thank you! =)


== Frequently Asked Questions ==
I am still collecting frequently asked questions. ;)


== Screenshots ==
1. Edit user form
2. Login form for a locked account
3. Password reset form for a locked account
4. User management page with account status column


== Changelog ==

= 1.1.1 =
* Added russian language file.

= 1.1 =
* Fire lock_account action when account got locked.
* Fire unlock_account action when account got unlocked.

= 1.0.4 =
* Added "account_lock_message" filter

= 1.0.3 =
* User accounts keep locked after plugin deactivation

= 1.0.2 =
* Delete user meta fields on plugin deactivation (clean up)

= 1.0.1 =
* Merged double translatable strings
* Added Dansk language file

= 1.0 =
* Everything works fine.