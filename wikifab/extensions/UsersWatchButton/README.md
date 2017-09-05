Description
===============

This MediaWiki extension add a button to control usersWatchList when viewing a user page to "follow this user".

Features :
* add a button 'follow' on user's profile pages
* add a notification for each changes followed on watchlist
* add a notification for now followers

Installation
===============

It requires extensions  :
* wikifab/UsersWatchlist
* wikifab/Echo
* wikifab/SocialProfile


Instructions :

1. clone UsersWatchList into the 'extensions' directory of your mediawiki installation
2. add the folling Line to your LocalSettings.php file :
> require_once("$IP/extensions/UsersWatchButton/UsersWatchButton.php");



MediaWiki Versions
===============
Version 0.1 of this extension has been tested on MediaWiki version 1.24
