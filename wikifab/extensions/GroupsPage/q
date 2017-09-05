[1mdiff --git a/GroupsPage.php b/GroupsPage.php[m
[1mindex 1a41a43..b1c7a27 100644[m
[1m--- a/GroupsPage.php[m
[1m+++ b/GroupsPage.php[m
[36m@@ -32,6 +32,7 @@[m [m$wgAutoloadClasses['GroupsPage\\Hook'] = __DIR__ . '/includes/Hook.php';[m
 $wgAutoloadClasses['GroupsPage\\ApiGroupsPage'] = __DIR__ . '/includes/ApiGroupsPage.php';[m
 $wgAutoloadClasses['GroupsPage\\GroupsPageCore'] = __DIR__ . '/includes/GroupsPageCore.php';[m
 $wgAutoloadClasses['GroupsPage\\Buttons'] = __DIR__ . "/includes/Buttons.php";[m
[32m+[m[32m$wgAutoloadClasses['GroupsPage\\ExploreGroupsTag'] = __DIR__ . "/includes/ExploreGroupsTag.php";[m
 [m
 $wgSpecialPages['ExploreGroups'] = 'GroupsPage\\SpecialExploreGroups';[m
 [m
[1mdiff --git a/includes/ExploreGroupsTag.php b/includes/ExploreGroupsTag.php[m
[1mindex d9092c8..16cde42 100644[m
[1m--- a/includes/ExploreGroupsTag.php[m
[1m+++ b/includes/ExploreGroupsTag.php[m
[36m@@ -1,5 +1,7 @@[m
 <?php[m
 namespace GroupsPage;[m
[32m+[m[32muse SMW\SpecialPage;[m
[32m+[m
 /**[m
  * class for include explore area[m
  *[m
[36m@@ -11,13 +13,14 @@[m [mnamespace GroupsPage;[m
 [m
 class ExploreGroupsTag {[m
 [m
[32m+[m	[32mconst PAGE_LIMIT = 12;[m
 [m
 	public static function simpleSearch() {[m
 		$page = 1;[m
 [m
 		$dbr = wfGetDB( DB_SLAVE );[m
 [m
[31m-		$limit = 12;[m
[32m+[m		[32m$limit = self::PAGE_LIMIT;[m
 [m
 		$offset = ($page -1 ) * $limit;[m
 [m
[36m@@ -56,7 +59,19 @@[m [mclass ExploreGroupsTag {[m
 		) );[m
 		$input->getOutput ()->addModules( 'ext.wikifab.wfExplore.js');[m
 [m
[31m-		$WfExploreCore = new WfExploreCore();[m
[32m+[m		[32m$WfExploreCore = new \WfExploreCore();[m
[32m+[m
[32m+[m		[32m$WfExploreCore->setNamespace(array('Group'));[m
[32m+[m		[32m$WfExploreCore->setSearchPageTitle( SpecialPage::getTitleFor( 'ExploreGroups' ));[m
[32m+[m		[32m$WfExploreCore->setPageResultsLimit(self::PAGE_LIMIT);[m
[32m+[m		[32m$WfExploreCore->setFilters(array());[m
[32m+[m		[32m$WfExploreCore->setMessageKey('load-more', 'exploregroups-explore-load-more');[m
[32m+[m
[32m+[m
[32m+[m		[32m$formatter = new \WikifabExploreResultFormatter();[m
[32m+[m		[32m$formatter->setTemplate(__DIR__ . '/../layout/layout-group-search-result.html');[m
[32m+[m
[32m+[m		[32m$WfExploreCore->setFormatter($formatter);[m
 [m
 		$params = [];[m
 [m
[1mdiff --git a/includes/SpecialExploreGroups.php b/includes/SpecialExploreGroups.php[m
[1mindex 775306d..504ad79 100644[m
[1m--- a/includes/SpecialExploreGroups.php[m
[1m+++ b/includes/SpecialExploreGroups.php[m
[36m@@ -4,11 +4,13 @@[m [mnamespace GroupsPage;[m
 [m
 class SpecialExploreGroups extends \SpecialWfExplore {[m
 [m
[31m-	private $pageLimitResult = 12;[m
[32m+[m	[32mprivate $pageLimitResult;[m
 [m
 	public function __construct() {[m
 		parent::__construct( 'ExploreGroups', array(NS_GROUP) );[m
 [m
[32m+[m		[32m$this->pageLimitResult = ExploreGroupsTag::PAGE_LIMIT;[m
[32m+[m
 		$this->WfExploreCore->setNamespace(array('Group'));[m
 		$this->WfExploreCore->setSearchPageTitle( self::getTitleFor( 'ExploreGroups' ));[m
 		$this->WfExploreCore->setPageResultsLimit($this->pageLimitResult);[m
