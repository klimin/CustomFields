<?php
/*
Pixelpost
Custom Fields Addon

REQUIREMENTS
Pixelpost Version 1.5-1.7.2
Pixelpost www: http://www.pixelpost.org/

AUTHOR
Klimin Andrew
Contact: photoblog@birsk.info
WWW: http://photoblog.birsk.info

License: http://www.gnu.org/copyleft/gpl.html

Addon provided a new functionality: custom fields.

Additional info:
See README.TXT

Donations help with future development of this addon:
http://www.pledgie.com/campaigns/404
*/

//////////////////////////////////
$version = "1.4";
$lastupdatedate = "09 Jul 2009";
$lastupdatetime = "09:11";
//////////////////////////////////

// Quote variable to make safe
function quote_smart($value)
{
   // Stripslashes
   if (get_magic_quotes_gpc()) {
       $value = stripslashes($value);
   }
   // Quote if not a number or a numeric string
   if (!is_numeric($value)) {
       $value = "'" . mysql_real_escape_string($value) . "'";
   }
   return $value;
}

$alert = "";
$js = "<script src='script.js' type='text/javascript'></script>
<script type='text/javascript'>
function confirmDelete()
{
var agree=confirm('Are you sure you want to delete this field?');
if (agree) return true ;
else return false ;
}
</script>";

$addon_name = "<a name='cf'></a>Custom Fields addon, ".$lastupdatedate." ".$lastupdatetime;
$addon_version = $version;
$alert = $alert.$js."This addon added custom fields to your Pixelpost site.<br />";
$report = "";

// The workspace. Where to activate the function inside index.php
$addon_workspace1 = "new_image_form";
$addon_workspace2 = "image_uploaded";
$addon_workspace3 = "image_deleted";
$addon_workspace4 = "image_edit_form";

// menu where the addon should appear in admin panel. in this case: images menu
$addon_menu = "";

// What would be the title of submenu of this addon
$addon_admin_submenu = "";

// What is the function
$addon_function_name1 = "prepare_custom_fields";
$addon_function_name2 = "save_custom_fields";
$addon_function_name3 = "delete_custom_fields";
$addon_function_name4 = "edit_custom_fields";

add_admin_functions($addon_function_name1,$addon_workspace1,$addon_menu,$addon_admin_submenu);
add_admin_functions($addon_function_name2,$addon_workspace2,$addon_menu,$addon_admin_submenu);
add_admin_functions($addon_function_name3,$addon_workspace3,$addon_menu,$addon_admin_submenu);
add_admin_functions($addon_function_name4,$addon_workspace4,$addon_menu,$addon_admin_submenu);

function prepare_custom_fields()
{
  global $pixelpost_db_prefix;

  //Get some settings (show/don't show disabled and invisible fields)
  $query = "SELECT showdisabled, showinvisible, bilingual  FROM ".$pixelpost_db_prefix."customfieldssettings";
  $result = mysql_query($query) or die("Invalid query: " . mysql_error());
  list($cf_showdisabled, $cf_showinvisible, $cf_bilingual) = mysql_fetch_row($result);

  echo "<div class='jcaption'>Custom fields</div><div class='content'><table><tr><th></th><th>Name</th><th>Enter value</th>";

  if ( $cf_bilingual=='on' ) {
    echo "<th>Alt value</th>";
  }
  echo "<th>Status</th></tr>";
  
  $cf_ids = "";                                                                                          

  // building WHERE string for query
  $where = "";
  if ( $cf_showdisabled=='on'&&$cf_showinvisible=='on' ) {
    $where = "";
    $warning = "";
  }
  if ( $cf_showdisabled=='on'&&$cf_showinvisible=='' ) {
    $where = "WHERE visible='on'";
    $warning = "<strong>WARNING!</strong> According your addon settings, <strong>hidden</strong> fields is not shown!";
  }
  if ( $cf_showdisabled==''&&$cf_showinvisible=='on' ) {
    $where = "WHERE enable='on'";
    $warning = "<strong>WARNING!</strong> According your addon settings, <strong>disabled</strong> fields is not shown!";
  }
  if ( $cf_showdisabled==''&&$cf_showinvisible=='' ) {
    $where = "WHERE visible='on' AND enable='on'";
    $warning = "<strong>WARNING!</strong> According your addon settings, <strong>hidden</strong> and <strong>disabled</strong> fields is not shown!";
  }

  //Get custom fields
  $query = "SELECT id, name, defaultvalue, alt_defaultvalue, enable, visible, not_meta_keyword FROM ".$pixelpost_db_prefix."customfields ".$where." ORDER by fieldorder";
  $result = mysql_query($query) or die("Invalid query: " . mysql_error());
  while(list($cf_id, $cf_name, $cf_defaultvalue, $cf_altdefaultvalue, $cf_enable, $cf_visible, $cf_not_meta_keyword ) = mysql_fetch_row($result))
  {
    if ( $cf_enable=='on' ) {
      $disabled_text = "";
      $input_disabled = "";
    } else {
      $disabled_text = ", disabled";
      $input_disabled = "disabled";
    }
    if ( $cf_visible=='on' ) {
      $input_visible = "Visible";
    } else {
      $input_visible = "<font color='red'>Not visible</font>  ";
    }

    if ( $cf_not_meta_keyword=='on' ) {
      $cf_img_keyimg = "";
    } else {
      //Inserting key-image
      $cf_img_keyimg = "<img src='data:image/gif;base64,R0lGODlhEAAQAJECAP///xM7Xv///wAAACH5BAEAAAIALAAAAAAQABAAAAIdVI6Zpu0Po5SgThAqjrrr5h3YpnzTiabqGjCTUAAAOw==' title='This field is META Keyword'/>";
    }

    echo "<tr><td>".$cf_img_keyimg."</td><td>".$cf_name."</td><td><input type='text' name='cf_input_".$cf_id."' value='".$cf_defaultvalue."' style='width:200px' ".$input_disabled."></td>";

    //Bilingual input (alt values)
    if ( $cf_bilingual=='on' ) {
      echo "<td><input type='text' name='cf_altinput_".$cf_id."' value='".$cf_altdefaultvalue."' style='width:200px' ".$input_disabled."></td>";
    }
    echo "<td>".$input_visible.$disabled_text."</td></tr>";
    if ( $cf_ids=="" ) {
      $cf_ids = $cf_id;
    } else {
      $cf_ids = $cf_ids.",".$cf_id;
    }
  }
  echo "</table>".$warning."
    <p>p.s. You can change addon settings at <a href='index.php?view=addons#cf'>this page</a>.</div>";
  echo "<input type='hidden' name='cf_ids' value='".$cf_ids."'>";
}

function save_custom_fields()
{
  global $pixelpost_db_prefix,$theid,$_POST;

//  echo "GET=".var_dump($_GET)."<br />";
//  echo "POST=".var_dump($_POST);
//  die("");

  //Get some settings
  $query = "SELECT bilingual  FROM ".$pixelpost_db_prefix."customfieldssettings";
  $result = mysql_query($query) or die("Invalid query: " . mysql_error());
  list($cf_bilingual) = mysql_fetch_row($result);

  if(!empty($_POST["imgid"])) {
    $theid = $_POST["imgid"];
  } else {
    if(!empty($_GET["imageid"])) {
      $theid = $_GET["imageid"];
    }
  }

  if (eregi("[^0-9]",$theid)) {
    die('Hack!');
  }
  
  $ids = explode(",", strip_tags($_POST["cf_ids"]));
  foreach($ids as $k => $v)
  {
    $value = quote_smart($_POST["cf_input_{$v}"]); // Get value

    if ( $cf_bilingual=='on' ) { // use alt value
      $alt_value = quote_smart($_POST["cf_altinput_{$v}"]); // Get alt value
      if ( trim($value)<>"''"||trim($alt_value)<>"''" ) {
        $query = "INSERT INTO ".$pixelpost_db_prefix."customfieldsvalues(parent_id,customfield_id,value,alt_value) VALUES (".$theid.",".$v.",".$value.",".$alt_value.")";
        mysql_query($query) or die( "Insert error: ".mysql_error().$query );
      }
    } else {// do not use alt value
      if ( trim($value)<>"''") {
        $query = "INSERT INTO ".$pixelpost_db_prefix."customfieldsvalues(parent_id,customfield_id,value) VALUES (".$theid.",".$v.",".$value.")";
        mysql_query($query) or die( "Insert error: ".mysql_error().$query );
      }
    }
  }
}

function delete_custom_fields()
{
  global $pixelpost_db_prefix,$_POST, $_GET;

  if(!empty($_POST["imgid"])) $_GET['imageid'] = $_POST["imgid"];
  if (eregi("[^0-9]",$_POST["imgid"])) {
    die('Hack!');
  }

  $id = quote_smart($_GET['imageid']);
  $query = "DELETE FROM {$pixelpost_db_prefix}customfieldsvalues WHERE parent_id = ".$id;
  mysql_query($query) or die( "Delete error: ".mysql_error() );
}

function edit_custom_fields()
{
  global $pixelpost_db_prefix,$theid,$_GET,$_POST;

  //Get some settings (show/don't show disabled and invisible fields)
  $query = "SELECT showdisabled, showinvisible, bilingual FROM ".$pixelpost_db_prefix."customfieldssettings";
  $result = mysql_query($query) or die("Invalid query: " . mysql_error());
  list($cf_showdisabled, $cf_showinvisible, $cf_bilingual) = mysql_fetch_row($result);

  echo "<div class='jcaption'>Edit custom fields</div><div class='content'><table><tr><th>Name</th><th>Enter value</th>";

  if ( $cf_bilingual=='on' ) {
    echo "<th>Alt value</th>";
  }
  echo "<th>Status</th></tr>";

  $cf_ids = "";                                                                                          

  // building WHERE string for query
  $where = "";
  if ( $cf_showdisabled=='on'&&$cf_showinvisible=='on' ) {
    $where = "";
    $warning = "";
  }
  if ( $cf_showdisabled=='on'&&$cf_showinvisible=='' ) {
    $where = "WHERE visible='on'";
    $warning = "<strong>WARNING!</strong> According your addon settings, <strong>hidden</strong> fields is not shown!";
  }
  if ( $cf_showdisabled==''&&$cf_showinvisible=='on' ) {
    $where = "WHERE enable='on'";
    $warning = "<strong>WARNING!</strong> According your addon settings, <strong>disabled</strong> fields is not shown!";
  }
  if ( $cf_showdisabled==''&&$cf_showinvisible=='' ) {
    $where = "WHERE visible='on' AND enable='on'";
    $warning = "<strong>WARNING!</strong> According your addon settings, <strong>hidden</strong> and <strong>disabled</strong> fields is not shown!";
  }

  $theid = $_GET["id"];
  if (eregi("[^0-9]",$theid)) {
    die('Hack!');
  }
  //Get custom fields
  $query = "SELECT id, name, defaultvalue, alt_defaultvalue, enable, visible FROM ".$pixelpost_db_prefix."customfields ".$where." ORDER by fieldorder";
  $result = mysql_query($query) or die("Invalid query: " . mysql_error());
  while(list($cf_id, $cf_name, $cf_defaultvalue, $cf_altdefaultvalue, $cf_enable, $cf_visible) = mysql_fetch_row($result))
  {
    if ( $cf_enable=='on' ) {
      $disabled_text = "";
      $input_disabled = "";
    } else {
      $disabled_text = ", disabled";
      $input_disabled = "disabled";
    }
    if ( $cf_visible=='on' ) {
      $input_visible = "Visible";
    } else {
      $input_visible = "<font color='red'>Not visible</font>  ";
    }

    //Get value (instead of defaultvalue)
    $query = "SELECT value, alt_value FROM ".$pixelpost_db_prefix."customfieldsvalues WHERE customfield_id='".$cf_id."' and parent_id='".$theid."' LIMIT 1";
    $result2 = mysql_query($query) or die("Invalid query: " . mysql_error());
    list($cf_value, $cf_alt_value) = mysql_fetch_row($result2);

    // if you want use default values on the blank fields, delete remark on the next two line

    // if ( $cf_value=="" ) $cf_value = $cf_defaultvalue;
    // if ( $cf_alt_value=="" ) $cf_alt_value = $cf_altdefaultvalue;


    echo "<tr><td>".$cf_name."</td><td><input type='text' name='cf_input_".$cf_id."' value='".$cf_value."' style='width:200px' ".$input_disabled.">";

    //Bilingual input (alt values)
    if ( $cf_bilingual=='on' ) {
      echo "<td><input type='text' name='cf_altinput_".$cf_id."' value='".$cf_alt_value."' style='width:200px' ".$input_disabled."></td>";
    }
    echo "<td>".$input_visible.$disabled_text."</td></tr>";
    if ( $cf_ids=="" ) {
      $cf_ids = $cf_id;
    } else {
      $cf_ids = $cf_ids.",".$cf_id;
    }
  }
  echo "</table>".$warning."
    <p>p.s. You can change addon settings at <a href='index.php?view=addons#cf'>this page</a>.</div>";
  echo "<input type='hidden' name='cf_ids' value='".$cf_ids."'>";
}

if($_GET['view'] == "images" && $_GET['x'] == "update" && $_GET["imageid"] >= 0)
{
  delete_custom_fields();
  save_custom_fields();
}

// customfields table
$query = "select count(*) from ".$pixelpost_db_prefix."customfields";
$result = mysql_query($query);
if ($result) {
//    $alert = $alert."<br>Table <font color=blue>".$pixelpost_db_prefix."customfields</font> exists.<br />";
} else {
  $query = "
  CREATE TABLE IF NOT EXISTS ".$pixelpost_db_prefix."customfields (
    id int(11) NOT NULL auto_increment,
    name varchar(121) default '',
    alt_name varchar(121) default '',
    defaultvalue varchar(121) default '',
    alt_defaultvalue varchar(121) default '',
    visible varchar(3) default 'on',
    enable varchar(3) default 'on',
    not_meta_keyword varchar(3) default '',
    type varchar(10) default '',
    fieldorder int(3),
    KEY id (id)
  )
  ";
  mysql_query($query) or die("Error: ". mysql_error());
  $alert = $alert."Table <font color='green'>".$pixelpost_db_prefix."customfields</font> created ...<br />";

  // inserting default custom fields
  $query = "INSERT INTO ".$pixelpost_db_prefix."customfields (id,name,fieldorder) VALUES(1,'Location',10)";
  mysql_query($query) or die("Error inserting data into the table: ".$pixelpost_db_prefix."customfields".mysql_error());
  $query = "INSERT INTO ".$pixelpost_db_prefix."customfields (id,name,fieldorder) VALUES(2,'Genre',20)";
  mysql_query($query) or die("Error inserting data into the table: ".$pixelpost_db_prefix."customfields".mysql_error());
  $query = "INSERT INTO ".$pixelpost_db_prefix."customfields (id,name,fieldorder) VALUES(3,'Now playing',30)";
  mysql_query($query) or die("Error inserting data into the table: ".$pixelpost_db_prefix."customfields".mysql_error());
}
  // customfieldsvalues table
$query = "select count(*) from ".$pixelpost_db_prefix."customfieldsvalues";
$result = mysql_query($query);
if ($result) {
//    $alert = $alert."Table <font color=blue>".$pixelpost_db_prefix."customfieldsvalues</font> exists.<br />";
} else {
  $query = "
  CREATE TABLE IF NOT EXISTS ".$pixelpost_db_prefix."customfieldsvalues (
    id int(11) NOT NULL auto_increment,
    parent_id int(11) NOT NULL,
    customfield_id int(11) NOT NULL,
    value varchar(121) default '',
    alt_value varchar(121) default '',
    PRIMARY KEY (id),
    KEY IDX_PARENT (parent_id)
  )
  ";
  mysql_query($query) or die("Error: ". mysql_error());
  $alert = $alert."Table <font color='green'>".$pixelpost_db_prefix."customfieldsvalues</font> created ...<br />";
}
// customfieldssettings table
$query = "select count(*) from ".$pixelpost_db_prefix."customfieldssettings";
$result = mysql_query($query);
if ($result) {
//    $alert = $alert."Table <font color=blue>".$pixelpost_db_prefix."customfieldssettings</font> exists.<br />";
} else {
  $query = "
  CREATE TABLE IF NOT EXISTS ".$pixelpost_db_prefix."customfieldssettings (
    id int(11) NOT NULL auto_increment,
    cfprefix varchar(51) default '',
    cfsuffix varchar(51) default '',
    prefix varchar(51) default '',
    delimiter varchar(51) default '',
    suffix varchar(51) default '',
    showdisabled varchar(3) default 'on',
    showinvisible varchar(3) default 'on',
    bilingual varchar(3) default '',
    css varchar(255) default '',
    version varchar(21) default '".$version."',
    KEY id (id)
  )
  ";
  mysql_query($query) or die("Error: ". mysql_error());
  $alert = $alert."Table <font color='green'>".$pixelpost_db_prefix."customfieldssettings</font> created ...<br />";
  // inserting default settings
  $query = "INSERT INTO ".$pixelpost_db_prefix."customfieldssettings (cfprefix,cfsuffix,prefix,delimiter,suffix)
    VALUES('','','<strong>','</strong>: ','<br />')";
  mysql_query($query) or die("Error inserting data into the table: ".$pixelpost_db_prefix."customfieldssettings".mysql_error());
  $alert = $alert."Default settings values inserted in the table <font color='green'>".$pixelpost_db_prefix."customfieldssettings</font>...<br />";
}

// Upgrade database to version 1.1 (checkbox for suppress custom field from keywords)

//    FIELD_EXISTS 
//    Checks whether specified field exists in current or specified table. 
   $fieldname = "not_meta_keyword"; 
   $table = $pixelpost_db_prefix."customfields"; 
   $exists = 0; 
   $i = 0; 
   if ($table != "") { 
       $result_id = mysql_list_fields( $pixelpost_db_pixelpost, $table ); 
       for ($i = 0; $i < mysql_num_fields($result_id); $i++) { 
           if (strtolower($fieldname) == strtolower(mysql_field_name($result_id, $i))) {             
               $exists = 1; 
               break; 
           }      
       } 
   } 
   // if the field does not exit: Create it! 
   if ($exists==0) {  
      $result = mysql_query("ALTER TABLE $table ADD `".$fieldname."` VARCHAR(3) DEFAULT ''");
      $alert = $alert."<font color='green'> Upgrade to v1.1: A new field named <strong>".$fieldname."</strong> has just been added to the table ".$table.".</font><br />";
   } 

$sortby = "ORDER by fieldorder";

// Set sorting order by name
if (isset($_GET['x'])&&$_GET['x'] == "sortbyname") {
  $sortby = "ORDER by name";
}

// Set sorting order by id
if (isset($_GET['x'])&&$_GET['x'] == "sortbyid") {
  $sortby = "ORDER by id";
}

// Set sorting order by fieldorder
if (isset($_GET['x'])&&$_GET['x'] == "sortbyorder") {
  $sortby = "ORDER by fieldorder";
}

// Save updated settings
if (isset($_GET['x'])&&$_GET['x'] == "customfieldssettingsupdate") {
  $cfprefix = $_POST['cfprefix'];
  $prefix = $_POST['prefix'];
  $delimiter = $_POST['delimiter'];
  $suffix = $_POST['suffix'];
  $cfsuffix = $_POST['cfsuffix'];
  $cf_showdisabled = $_POST['cf_showdisabled'];
  $cf_showinvisible = $_POST['cf_showinvisible'];
  $cf_bilingual = $_POST['cf_bilingual'];

  $query = "update ".$pixelpost_db_prefix."customfieldssettings set
      cfprefix='" .$cfprefix."',
      prefix='" .$prefix."',
      delimiter='" .$delimiter."',
      suffix='" .$suffix."',
      cfsuffix='" .$cfsuffix."',
      showdisabled='" .$cf_showdisabled."',
      showinvisible='" .$cf_showinvisible."',
      showinvisible='" .$cf_showinvisible."',
      bilingual='" .$cf_bilingual."'
      ";
  $result = mysql_query($query) or die("Update error: ". mysql_error());
  $report="<font color='blue'>Settings updated sucessfully!</font><br />";
}

// Update custom fields (name, order, visibility)
if (isset($_GET['x'])&&$_GET['x'] == "customfieldsupdate") {
  $report="";
  $id_list = $_GET['id_list'];
  $ids = explode(",", $id_list);
  $counter = 0;

  // Check bilingual
  $query = "SELECT bilingual FROM ".$pixelpost_db_prefix."customfieldssettings LIMIT 1";
  $result = mysql_query($query) or die("Invalid query: " . mysql_error());
  list($bilingual) = mysql_fetch_row($result);
  
  while ( $ids[$counter]<>"" ) {
    $cf_name = $_POST['cf_name'.$ids[$counter]];
    $cf_order = $_POST['cf_order'.$ids[$counter]];
    if (eregi("[^0-9]",$cf_order)) {
      die('Hack!');
    }
    $cf_visible = $_POST['cf_visible'.$ids[$counter]];
    $cf_enable = $_POST['cf_enable'.$ids[$counter]];
    $cf_not_meta_keyword = $_POST['cf_not_meta_keyword'.$ids[$counter]];
    $cf_defaultvalue = $_POST['cf_defaultvalue'.$ids[$counter]];

    if ($bilingual=='on') {
      $cf_alt_name = $_POST['cf_alt_name'.$ids[$counter]];
      $cf_alt_defaultvalue = $_POST['cf_alt_defaultvalue'.$ids[$counter]];
      $alt_name_query = "alt_name='" .$cf_alt_name."',";
      $alt_defaultvalue_query = "alt_defaultvalue='" .$cf_alt_defaultvalue."',";
    } else {
      //$cf_alt_name = "";
      //$cf_alt_defaultvalue = "";
      $alt_name_query = "";
      $alt_defaultvalue_query = "";
    }
    $result = mysql_query("update ".$pixelpost_db_prefix."customfields set
        name='" .$cf_name."',".
        $alt_name_query."
        fieldorder='" .$cf_order."',
        defaultvalue='" .$cf_defaultvalue."',".
        $alt_defaultvalue_query."
        visible='" .$cf_visible."',
        enable='" .$cf_enable."',
        not_meta_keyword='" .$cf_not_meta_keyword."'
        WHERE id='".$ids[$counter]."'
        ") or die("Update error: ". mysql_error());

    $report = $report."<font color='blue'>Record with <strong>id=".$ids[$counter++]."</strong> updated!</font> <br />";

//    echo "$cf_name $cf_order $cf_visible";
  }
}

// Add custom field
if (isset($_GET['x'])&&$_GET['x'] == "addcustomfield") {
        //Get next available Field Order (+10)
  $result = mysql_query("SELECT max(fieldorder) FROM ".$pixelpost_db_prefix."customfields") or die("Invalid query: " . mysql_error());
  list($nextfieldorder) = mysql_fetch_row($result);
  $nextfieldorder = $nextfieldorder + 10;

  $result = mysql_query("insert into ".$pixelpost_db_prefix."customfields(fieldorder) VALUES(".$nextfieldorder.")")
      or die("Insert error: ". mysql_error());
  $report="<font color='blue'>Custom field added sucessfully!</font><br />";
}

// Delete custom field
if (isset($_GET['x'])&&$_GET['x']=="deletecustomfield") {
  $deleted_id = $_GET['deleted_id'];
  if (eregi("[^0-9]",$deleted_id)) {
    die('Hack!');
  }
  $result = mysql_query("delete from ".$pixelpost_db_prefix."customfields WHERE id='".$deleted_id."'")
      or die("Delete error: ". mysql_error());
  $report="<font color='blue'>Custom field with <strong>id=".$deleted_id."</strong> deleted sucessfully!</font><br />";

  // Delete records of table pixelpost_customfieldsvalues with customfield_id=$deleted_id
  $result = mysql_query("delete from ".$pixelpost_db_prefix."customfieldsvalues WHERE customfield_id='".$deleted_id."'")
      or die("Delete error: ". mysql_error());
  $report="<font color='blue'>Custom field values with <strong>customfield_id=".$deleted_id."</strong> deleted sucessfully!</font><br />";
}

// check to see if you are in the admin/addons page to do the database stuff
// check if tables exists
if($_GET['view'] == "addons") {
  // Get list of custom fields
  // You can add custom field, modify custom fields or delete custom field
  $alert = $alert."<br />The list of custom fields (ordered):<br />";
  $tmp = "";
  $id_list = ""; //list of id's to edit

  // Check bilingual
  $query = "SELECT bilingual FROM ".$pixelpost_db_prefix."customfieldssettings LIMIT 1";
  $result = mysql_query($query) or die("Invalid query: " . mysql_error());
  list($bilingual) = mysql_fetch_row($result);
//  $bilingual = CheckBilingual();

  $query = "SELECT * FROM ".$pixelpost_db_prefix."customfields ".$sortby;
  $result = mysql_query($query) or die("Invalid query (1): " . mysql_error());
  while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    if ( $row["visible"]=='on' ) {
      $checked_visible="CHECKED";
    } else {
      $checked_visible="";
    }
    if ( $row["enable"]=='on' ) {
      $checked_enable="CHECKED";
    } else {
      $checked_enable="";
    }
    if ( $row["not_meta_keyword"]=='on' ) {
      $checked_not_meta_keyword="CHECKED";
    } else {
      $checked_not_meta_keyword="";
    }
    // If bilingual ON, show alt_name and alt_defaultvalue fields
    if ($bilingual=='on') {
      $alt_name_td = "<td><input type='text' name='cf_alt_name".$row["id"]."' value='".$row["alt_name"]."'' style='width:100px'></td>";
      $alt_defaultvalue_td = "<td><input type='text' name='cf_alt_defaultvalue".$row["id"]."' value='".$row["alt_defaultvalue"]."'' style='width:70px'></td>";
    } else {
      $alt_name_td = "";
      $alt_defaultvalue_td = "";
    }


    $tmp = $tmp."
    <tr>
    <td><strong>".$row["id"].".</strong></td>
    <td><input type='text' name='cf_name".$row["id"]."' value='".$row["name"]."'' style='width:120px'></td>".$alt_name_td."
    <td><input type='text' name='cf_order".$row["id"]."' value='".$row["fieldorder"]."'' style='width:30px'></td>
    <td align='center'><input type='checkbox' $checked_visible name='cf_visible".$row["id"]."'></td>
    <td align='center'><input type='checkbox' $checked_enable name='cf_enable".$row["id"]."'></td>
    <td align='center'><input type='checkbox' $checked_not_meta_keyword name='cf_not_meta_keyword".$row["id"]."'></td>
    <td><input type='text' name='cf_defaultvalue".$row["id"]."' value='".$row["defaultvalue"]."'' style='width:80px'></td>".$alt_defaultvalue_td."
    <td align='center'><a onclick='return confirmDelete()' href='index.php?view=addons&amp;x=deletecustomfield&deleted_id=".$row["id"]."#cf' class='delete'>Delete</a></td>
    </tr>
    ";
    if ( $id_list=="" ) {
      $id_list = $row["id"];
    } else {
      $id_list = $id_list.",".$row["id"];
    }

  }
  // If bilingual ON, show table <th> for alt fields
  if ($bilingual=='on') {
    $alt_name_th = "<th>Alt name</th>";
    $alt_defaultvalue_th = "<th>Alt default value</th>";
  } else {
    $alt_name_th = "";
    $alt_defaultvalue_th = "";
  }

  // Generate next ID for adding new custom field 
  // (used only for correct refresh page in browsers, when clock 'Add custom field' link!)
  $result = mysql_query("SELECT max(id) FROM ".$pixelpost_db_prefix."customfields") or die("Invalid query: " . mysql_error());
  list($next_id) = mysql_fetch_row($result);
  $next_id++;

  $alert = $alert."<form method='post' action='index.php?view=addons&amp;x=customfieldsupdate&id_list=".$id_list."#cf'>
      <table>
      <tr>
      <th><a href='index.php?view=addons&amp;x=sortbyid#cf'>ID</a></th>
      <th><a href='index.php?view=addons&amp;x=sortbyname#cf'>Name</a></th>".$alt_name_th."
      <th><a href='index.php?view=addons&amp;x=sortbyorder#cf'>Order</a></th>
      <th>Visible?</th>
      <th>Enable?</th>
      <th>Not a keyword?</th>
      <th>Default value</th>".$alt_defaultvalue_th."
      <th><font color='red'>Delete link</font></th>
      </tr>".$tmp."</table>";
  $alert = $alert."<a href='index.php?view=addons&amp;x=addcustomfield&amp;tmp=".$next_id."#cf'>+ Add custom field</a><br />".$report."
       <input type='submit' value='Update fields'></form>";
  mysql_free_result($result);

} // $_GET['view'] == "addons"

// Get settings (prefix, suffix etc)
$query = "SELECT cfprefix,cfsuffix,prefix,delimiter,suffix, showdisabled, showinvisible, bilingual FROM ".$pixelpost_db_prefix."customfieldssettings LIMIT 1";
$result = mysql_query($query) or die("Invalid query: " . mysql_error());
list($cfprefix, $cfsuffix, $prefix, $delimiter, $suffix, $showdisabled, $showinvisible, $bilingual) = mysql_fetch_row($result); 
if ( $showdisabled=='on' ) {
  $checked_showdisabled="CHECKED";
} else {
  $checked_showdisabled="";
}
if ( $showinvisible=='on' ) {
  $checked_showinvisible="CHECKED";
} else {
  $checked_showinvisible="";
}
if ( $bilingual=='on' ) {
  $checked_bilingual="CHECKED";
} else {
  $checked_bilingual="";
}

// STATICTICS
//Calculate custom fields counter
$result = mysql_query("SELECT count(*) FROM ".$pixelpost_db_prefix."customfields") or die("Invalid query: " . mysql_error()); 
  list($cf_counter) = mysql_fetch_row($result);

//Calculate invisible custom fields counter
$result = mysql_query("SELECT count(*) FROM ".$pixelpost_db_prefix."customfields WHERE visible=''") or die("Invalid query: " . mysql_error());
list($cf_invisible_counter) = mysql_fetch_row($result);

//Calculate disabled custom fields counter
$result = mysql_query("SELECT count(*) FROM ".$pixelpost_db_prefix."customfields WHERE enable=''") or die("Invalid query: " . mysql_error());
list($cf_disabled_counter) = mysql_fetch_row($result);

//Calculate custom field values counter
$result = mysql_query("SELECT count(*) FROM ".$pixelpost_db_prefix."customfieldsvalues")  or die("Invalid query: " . mysql_error());
list($cf_values_counter) = mysql_fetch_row($result);

$addon_description = "$alert<br />
<strong>Settings</strong>
<form method='post' action='index.php?view=addons&amp;x=customfieldssettingsupdate#cf'>
<input type='checkbox' $checked_showdisabled name='cf_showdisabled'> Show disabled fields in admin area?<br>
<input type='checkbox' $checked_showinvisible name='cf_showinvisible'> Show invisible fields in admin area?<br>
<input type='checkbox' $checked_bilingual name='cf_bilingual'> Enable bilingual features?<br>
Custom fields prefix: <input type='text' name='cfprefix' value='".$cfprefix."' style='width:60px'>
Item prefix: <input type='text' name='prefix' value='".$prefix."' style='width:60px'>
Items delimiter: <input type='text' name='delimiter' value='".$delimiter."' style='width:60px'>
Item suffix: <input type='text' name='suffix' value='".$suffix."' style='width:60px'>
Custom fields suffix: <input type='text' name='cfsuffix' value='".$cfsuffix."' style='width:60px'><br />
Format: &lt;Custom fields prefix&gt;&lt;prefix&gt;&lt;NAME&gt;&lt;delimiter&gt;&lt;VALUE&gt;&lt;suffix&gt;&lt;Custom fields suffix&gt;<br />
HTML: ".htmlentities($cfprefix)."".htmlentities($prefix)."&lt;NAME&gt;".htmlentities($delimiter)."&lt;VALUE&gt;".htmlentities($suffix)."".htmlentities($cfsuffix)."<br />
Example: ".$cfprefix.$prefix."Mood".$delimiter."Good".$suffix.$cfsuffix."
<input type='submit' value='Update settings'>
</form>

<strong>Some statistics:</strong><br />
Custom fields counter: <strong>".$cf_counter."</strong> (invisible: ".$cf_invisible_counter.", disabled: ".$cf_disabled_counter.")<br />
Custom field values counter: <strong>".$cf_values_counter."</strong><br /><br />

<strong>Tags:</strong> <br />
Main tag:<br />
<strong>&lt;CUSTOM_FIELDS&gt;</strong> - insert all visible custom fields of the image<br />
Additional tags:<br />
<strong>&lt;CUSTOM_FIELD_NAME_ID=NNN&gt;</strong> - insert name of the custom field with ID=NNN<br />
<strong>&lt;CUSTOM_FIELD_VALUE_ID=NNN&gt;</strong> - insert value of the custom field with ID=NNN.<br />
<strong>&lt;CUSTOM_FIELD_COUNTER&gt;</strong> - number of the custom fields.<br />
<strong>&lt;CUSTOM_FIELD_KEYWORDS_VALUES&gt;</strong> - list of all custom fields values for META.<br />
<strong>&lt;CUSTOM_FIELD_VALUES_COUNTER&gt;</strong> - number of the custom fields values.<br /><br />

<strong>Credits:</strong><br />
Author: <strong>Klimin Andrew</strong> E-mail: <a href='mailto:photoblog@birsk.info'>photoblog@birsk.info</a><br />
Photoblog: <a href='http://photoblog.birsk.info'>http://photoblog.birsk.info</a><br><br>
(<a href='http://www.versioncheckr.com/42/".$addon_version."'/>Check for update</a>)";

// Run only if exists $image_id and exists <CUSTOM_FIELD*> tags
if ( isset($image_id) and preg_match("<CUSTOM_FIELD.*?>", $tpl)) {

  // Get custom fields values
  $custom_fields = $cfprefix;
  $cf_keywords_values = "";

  //Values
  $query = "SELECT a.id, a.name, a.alt_name, b.value, b.alt_value, a.visible, a.not_meta_keyword FROM ".$pixelpost_db_prefix."customfields AS a, ".$pixelpost_db_prefix."customfieldsvalues AS b WHERE b.customfield_id = a.id AND b.parent_id=$image_id AND a.enable='on' order by a.fieldorder";
  $result = mysql_query($query) or die("Invalid query (2): " . mysql_error());
  while(list($cf_id, $cf_name, $cf_alt_name, $cf_value, $cf_alt_value, $cf_visible, $cf_not_meta_keyword) = mysql_fetch_row($result))
  {
    $cf_name = pullout($cf_name);
    $cf_value = pullout($cf_value);


    //Ver 1.3
    //URL autoreplace to HTML-code:    'http://URL'  ->  '<a href="http://URL">http://URL</a>'
    $s = '^http://[a-zA-Z0-9_\.-/]*$';
    if (eregi($s, $cf_value, $regs)) {
        $cf_value = '<a href="'.$cf_value.'">'.$cf_value.'</a>';
    }

    if (eregi($s, $cf_alt_value, $regs)) {
        $cf_alt_value = '<a href="'.$cf_alt_value.'">'.$cf_alt_value.'</a>';
    }


    // Bilingual = on
    if ( $bilingual=='on' ) {
      $cf_alt_name = pullout($cf_alt_name);  //alt name
      $cf_alt_value = pullout($cf_alt_value); //alt value

      if ( strtolower($_GET['lang'])==strtolower($PP_supp_lang[$cfgrow['langfile']][0])) {
        $cf_method = 'DEFAULT'; //get==default lang
      } else {
        if ( strtolower($_GET['lang'])==strtolower($PP_supp_lang[$cfgrow['altlangfile']][0])) {
          $cf_method = 'ALT'; //get==alt lang
        } else {
          if ( strtolower($_COOKIE['lang'])==strtolower($PP_supp_lang[$cfgrow['langfile']][0])) {
             $cf_method = 'DEFAULT'; //get=='' and cookie==default lang
          } else {
            if ( strtolower($_COOKIE['lang'])==strtolower($PP_supp_lang[$cfgrow['altlangfile']][0])) {
                $cf_method = 'ALT'; //get=='' and cookie==alt lang
            } else {
                  $cf_method = 'DEFAULT'; //get=='' and cookie=='' (default lang)
            }
          }
        }
      }

      if ( $cf_method=='DEFAULT' ) {
        if ( $cf_value<>'') {
          $tpl = str_replace("<CUSTOM_FIELD_NAME_ID=".$cf_id.">",$cf_name,$tpl);
          $tpl = str_replace("<CUSTOM_FIELD_VALUE_ID=".$cf_id.">",$cf_value,$tpl);
          if ( $cf_visible=='on' ) {
            $custom_fields = $custom_fields.$prefix.$cf_name.$delimiter.$cf_value.$suffix;
          }
          //Keywords
          if ($cf_not_meta_keyword=='') {
            if ($cf_keywords_values=='') {
              $cf_keywords_values = $cf_value;
            } else {
              $cf_keywords_values = $cf_keywords_values.','.$cf_value;
            }
          }

        }
      } else { // ALT
        if ( $cf_alt_value<>'') {
          $tpl = str_replace("<CUSTOM_FIELD_NAME_ID=".$cf_id.">",$cf_alt_name,$tpl);
          $tpl = str_replace("<CUSTOM_FIELD_VALUE_ID=".$cf_id.">",$cf_alt_value,$tpl);
          if ( $cf_visible=='on' ) {
            $custom_fields = $custom_fields.$prefix.$cf_alt_name.$delimiter.$cf_alt_value.$suffix;
          }
          //Keywords
          if ($cf_not_meta_keyword=='') {
            if ($cf_keywords_values=='') {
              $cf_keywords_values = $cf_alt_value;
            } else {
              $cf_keywords_values = $cf_keywords_values.','.$cf_alt_value;
            }
          }

        }
      } //if ( $cf_method=='DEFAULT' )
      
    } else { // non bilingual
      $tpl = str_replace("<CUSTOM_FIELD_NAME_ID=".$cf_id.">",$cf_name,$tpl);
      $tpl = str_replace("<CUSTOM_FIELD_VALUE_ID=".$cf_id.">",$cf_value,$tpl);
      if ($cf_visible=='on') {
        $custom_fields = $custom_fields.$prefix.$cf_name.$delimiter.$cf_value.$suffix;
      }
      //Keywords
      if ($cf_not_meta_keyword=='') {
        if ($cf_keywords_values=='') {
          $cf_keywords_values = $cf_value;
        } else {
          $cf_keywords_values = $cf_keywords_values.','.$cf_value;
        }
      } 
    }
  } //while

  $custom_fields = $custom_fields.$cfsuffix;
  $tpl = str_replace("<CUSTOM_FIELDS>",$custom_fields,$tpl);
  $tpl = str_replace("<CUSTOM_FIELDS_COUNTER>",$cf_counter,$tpl);
  $tpl = str_replace("<CUSTOM_FIELDS_VALUES_COUNTER>",$cf_values_counter,$tpl);
  $tpl = str_replace("<CUSTOM_FIELDS_KEYWORDS_VALUES>",eregi_replace ("[^0-9a-zA-Z, \-]","",$cf_keywords_values),$tpl);
} //if
?>