<?php
/*
 * this files job is to define all functions relating to drawing the web pages.
 * 
 * At the moment, the menu build targets links to the content frame, the tabs
 * builder targets the menu frame. In reality, we should be able to create
 * a hidden target somewhere that everything can target so that in code we
 * only do reloads on framed targets when needed
 * 
 * All functions here start with bls_pb_
 */
 
// our pagebuilder variables and arrays.
global $bls_pb_pre_build_funcs, $bls_pb_titleName;


// a prebuild for pageBuilder functions
$bls_pb_pre_build_funcs["pagebuilder_prebuild"] = "bls_pb_pagebuilder_prebuild_function";


function bls_pb_pagebuilder_prebuild_function()
{
	bls_ut_backTrace("bls_pb_pagebuilder_prebuild_function");
	
	if(isset($_REQUEST["function"])) {
		switch($_REQUEST["function"]) {
			case "go_home":
				bls_pb_reloadFrame("fr_content");
				bls_pb_reloadFrame("fr_menu");
				exit(0);
				break;
			case "global_options":
				bls_pb_reloadFrame("fr_head", "index.php?frame=head&title=Global Options");
				bls_pb_reloadFrame("fr_menu", "index.php?frame=menu&function=loadgloboptmenu");
				bls_pb_reloadFrame("fr_content");
				exit(0);
				break;
			case "loadgloboptmenu":
				global $bls_pb_menus;
				
				
				// TODO: the link below shows how links can be implemented with targets... this is messy.
				$bls_pb_menus = array();
				$bls_pb_menus["Package Providers"] = "index.php?function=bls_pp_provideoptionsloader";
				$bls_pb_menus["Base Packages"] = "index.php?frame=content&function=globalbasepackages";
				$bls_pb_menus["Cleanups"] = "index.php?frame=content&function=globalcleanuppage";
				$bls_pb_menus["User Management"] = "something";
				break;
			case "blankpage":
				exit(0);
				break;	
			case "auth_logout":
				bls_au_logoutUser();
				exit(0);
				break;	
		}
	}	
}




 
// the entry funciton - all web calls come through this function
function bls_pb_webCall()
{
	bls_ut_backTrace("bls_pb_webCall");
	
	if(!bls_au_isAuthed()) {
		bls_au_authDialog();
		exit(0);
	} 
	
	bls_pb_preBuild();
	
	if(!isset($_REQUEST["frame"])) {
		bls_pb_buildFrameLayout();
	} else {
		$build_fsframe_name = $_REQUEST["frame"];
		$frame_function = "bls_pb_frame$build_fsframe_name";
		if(function_exists($frame_function)) $frame_function();
	}
	
}

// the job of pre-build is to execute any per-page defined functions.
function bls_pb_preBuild()
{
	bls_ut_backTrace("bls_pb_preBuild");
	global $bls_pb_pre_build_funcs;
	
	// first set the default content for frames
	//bls_pb_setFrameContent();
	if(isset($bls_pb_pre_build_funcs)) foreach($bls_pb_pre_build_funcs as $func) {
		if(function_exists("$func")) $func();
	}
}


// this is how we build menu's
// Menu's always targt "content" So plan for this.

global $bls_pb_menus;

$bls_pb_menus["Welcome"] = "index.php?frame=content";
$bls_pb_menus["Exported Builds (*)"] = "index.php?frame=content&function=projectexportmanager";
$bls_pb_menus["Published Builds (*)"] = "index.php?frame=content&function=publishedbuildsmanager";

function bls_pb_menuBuilder()
{
	bls_ut_backTrace("bls_pb_menuBuilder");
	global $bls_pb_menus;
	
	echo "<table>";
	echo "<tr><th>Menu</th></tr>";
	if(isset($bls_pb_menus)) foreach($bls_pb_menus as $key => $var) {
		$menu_addr = $var;
		$menu_name = $key;
		echo "<tr><td><a href=\"$menu_addr\" target=\"fr_content\">$menu_name</a></td></tr>";		
	}
	echo "<tr><td></td></tr>";
	echo "</table>";
	
}








// and this is how we build tabs, for this we need a pre-build
// tab build always targets "menu" so that it wont need to be 
// reloaded except when explicitly done so.
// As such, you should plan for this.
global $bls_pb_tabs;
$bls_pb_tabs["Home"] = "index.php?function=go_home";
$bls_pb_tabs["Global Options"] = "index.php?function=global_options";
$bls_pb_tabs["New Project"] = "index.php?function=new_project";

function bls_pb_tabBuilder()
{
	bls_ut_backTrace("bls_pb_menuBuilder");
	global $bls_pb_tabs;
	
	echo "<table><tr>";
	if(isset($bls_pb_tabs)) foreach($bls_pb_tabs as $key => $var) {
		$tabs_addr = $var;
		$tabs_name = $key;
		echo "<td><a href=\"$tabs_addr\" target=\"fr_menu\">$tabs_name</a></td>";		
	}
	echo "</tr></table>";
	
}


// the next set of functions build the frame contents of each frame
// these will build a "Default" frame. This is overridden in "prebuild".
// for for example, if the webpage is called with $_REQUEST[] = something
// then you may want to change $bls_pb_..._content to point at a different function

function bls_pb_framehead()
{
	bls_ut_backTrace("bls_pb_framehead");
	global $bls_pb_framehead_content, $bls_pb_titleName;
	
	if(isset($bls_pb_framehead_content)) {
		$bls_pb_framehead_content();
	} else {
		if(isset($_REQUEST["title"])) {
			$title = "Minifed Web - ".$_REQUEST["title"];
		} else {
			$title = "Minifed Web";
		}
		echo "<h1>$title</h1>";
	}
}

function bls_pb_frametabs()
{
	bls_ut_backTrace("bls_pb_frametabs");
	global $bls_pb_frametabs_content;
	
	if(isset($bls_pb_frametabs_content)) {
		$bls_pb_frametabs_content();
	} else {
		bls_pb_tabBuilder();
	}
}

function bls_pb_framemenu()
{
	bls_ut_backTrace("bls_pb_framemenu");
	global $bls_pb_framemenu_content;
	
	if(isset($bls_pb_framemenu_content)) {
		$bls_pb_framemenu_content();
	} else {
		bls_pb_menuBuilder();
	}
}

function bls_pb_framecontent()
{
	bls_ut_backTrace("bls_pb_framecontent");
	global $bls_pb_framecontent_content;
	
	if(isset($bls_pb_framecontent_content)) {
		$bls_pb_framecontent_content();		
	} else {
?>
<h2>Welcome!</h2>
Welcome to minifed - The builder of utility linux OS's. Minifed's goal is
to provide a kernel and initial ram disk containing some utility function
(such as file server, web server, vitualisation, etc). There are two components
to any minifed based application. This builder and the mfapi. The mfapi is still
in its beginning stages, but its still possible to build a small OS here and
write custom code to control it via a web interface (which you have to write
from scratch at this point).<br><br>
To get started, you'll first need to define a package provider, and then package
source for your package provide. These are
simply some place for minifed to get packages it will build the OS from and can
just be a location on the disk full of files. To start out (for example), create
a fedora 9 one, grab a DVD iso and copy the following packages to somewhere
the web server can get to:
<pre>
- MAKEDEV
- busybox
- device-mapper
- device-mapper-libs
- e2fsprogs
- e2fsprogs-libs
- glibc
- kernel
- libselinux
- libsepol
- lvm2
- module-init-tools
- readline
- udev
</pre>
point the package source at that location, the hit refresh.
<br>
Next you'll want a "base package". Add those in via the global options link, and
you'll want the "mfbase-f9" (available where you got this from). Create a project
and enable the f9 provider you just created. Add the packages in and click build.
This should end with the creation of a kernel/initrd combo you can now "boot" (boots
in qemu and vnc's to it via tightvnc in java).
<br>
There will be "real" doco coming soon enough, but for now if you've gotten this far
(i.e. your seeing this web page) you're probably smart enough to do the bits above
and build your first "utility OS" (that does nothing).
<br><br>
Thanks for downloading minifed and giving it a try!
 
<?php		
	}
}

function bls_pb_framestatus()
{
	bls_ut_backTrace("bls_pb_framestatus");
	global $bls_pb_framestatus_content;
	
	if(isset($bls_pb_framestatus_content)) {
		$bls_pb_framestatus_content();
	} else {
		bls_pb_printDebug();
	}
}








function bls_pb_reloadFrame($frame, $url="")
{
	
	if($url == "") {
		switch($frame) {
			case "fr_head":
				$url = "index.php?frame=head";
				break;
			case "fr_tabs":
				$url = "index.php?frame=tabs";
				break;
			case "fr_menu":
				$url = "index.php?frame=menu";
				break;
			case "fr_status":
				$url = "index.php?frame=status";
				break;
			case "fr_content":
				$url = "index.php?frame=content";
				break;
		}
	}
?>
<script>
top.<?php echo $frame ?>.location='<?php echo $url ?>'
</script>
<?php
}




// this builds the frame layout
function bls_pb_buildFrameLayout()
{
	bls_ut_backTrace("bls_pb_buildFrameLayout");
	
	global $bls_pb_title;
	
	$bls_pb_title = "Minifed";
	
	$gpage = "";
	if(isset($_REQUEST["page"])) {
		$gpage = "&page=".$_REQUEST["page"];
	}
?>
<HTML>
<HEAD>
<TITLE><?php echo $bls_pb_title ?></TITLE>
</HEAD>
  <FRAMESET rows="80, 30, *, 120">
      <FRAME src="index.php?frame=head<?php echo $gpage ?>" name="fr_head" frameborder="no">
      <FRAME src="index.php?frame=tabs<?php echo $gpage ?>" name="fr_tabs" frameborder="no">
      <FRAMESET cols="180,*">
         <FRAME src="index.php?frame=menu<?php echo $gpage ?>" name="fr_menu">
         <FRAME src="index.php?frame=content<?php echo $gpage ?>" name="fr_content">
      </FRAMESET>
      <FRAME src="index.php?frame=status<?php echo $gpage ?>" name="fr_status" frameborder="no">
  </FRAMESET>

</HTML>
<?php
	bls_pb_printDebug();
}

function bls_pb_printDebug()
{
	bls_ut_backTrace("bls_pb_printDebug");
	
	echo "<pre>";
	print_r($GLOBALS);
	echo "</pre>";
}

?>
