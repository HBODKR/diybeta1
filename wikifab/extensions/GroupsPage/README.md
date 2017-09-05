## Mediawiki Extension GroupsPage

This extension enable to link mediawiki pages to some other pages (called groups page)
It is designed to work with Wikifab extension, but could be used on any mediawiki.

## Requierements

Tt requires extensions wikifab/UsersPagesLinks and Wikifab/Explore

## Installation

Place this extension into the extension directory of you mediawiki.
Add this line to the LocalSettings.php file

	require_once("$IP/extensions/GroupsPage/GroupsPage.php");

## parser function

the list of tutoriel of a group page can be display by including this into the group page :

	{{#displayMemberTutorials:this}} 


## Contributors

Pierre Boutet
Clément Flipo

## License

This project is licensed under the terms of the MIT license.