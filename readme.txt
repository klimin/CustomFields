*************************************
* Custom Fields addon for Pixelpost *
*************************************

AUTHOR
------
Klimin Andrew
Contact: photoblog@birsk.info
WWW: http://photoblog.birsk.info
License: http://www.gnu.org/copyleft/gpl.html
Addon Homepage: http://www.pixelpost.org/extend/addons/custom-fields/
Addon Discussion: http://www.pixelpost.org/forum/showthread.php?t=5780

VERSION
-------
1.4

LAST UPDATED
-----------
09 Jul 2009


REQUIREMENTS
------------
Pixelpost Version 1.5-1.7.2
Pixelpost Version 1.6-1.7.2 for bilingual feature.
Pixelpost URL: http://www.pixelpost.org/

ABOUT
-----
Custom Fields Addon provides the ability to add additonal parameters or details 
to your images. You can add an unlimited number of fields, disable selected fields, 
change a field's visibility, set field order or delete them.

Caution!!! Deleted fields cannot be restored!


For example: 
  Custom field name Possible values
        ------------------------------------------------------
  Cropped   Yes, no, a little, etc.
  Image Quality JPG, RAW, TIFF, etc.
  RAW Converter Adobe Camera Raw, Nikon Capture, C1 Pro, etc.
  Used software Adobe Photoshop CS2, Paintbrush, etc.
  Noise   Original, NeatImage, etc.
  Rotation  None, 90, -90, 180, minor rotation, etc.
  Place   Home, work, travel to Africa, travel to Russia, indoor, outddor, etc.
  Event   My birthday, Christmas, etc.
  City    New-York, Paris, London, Moscow, Birsk :-), etc.
  Flash   Not used, Nikon SB800, multiple flashes, etc.
  Genre   Portrait, street photo, landscape, nude, macro, etc.
  Tripod    Hands, tripod, monopod, table, etc.
  Photographer  John Smith, Ivan Susanin, Van Halen, etc.
  Mood    ...
  Weather ...
  Season  ...
  Now playing ...
  GPS Coords ...
  Keywords ...
  HTML-elements (colors, styles, etc)
  ...and so on.

TAGS
-----
<CUSTOM_FIELDS>
<CUSTOM_FIELD_NAME_ID=NNN>
<CUSTOM_FIELD_VALUE_ID=NNN>
<CUSTOM_FIELD_COUNTER>
<CUSTOM_FIELD_VALUES_COUNTER>
<CUSTOM_FIELDS_KEYWORDS_VALUES>


VERSION HISTORY
---------------
[!] Important
[+] New
[-] Fixed error
[*] Changes

0.1 12 Dec 2006
  [!] Initial Release

0.2 13 Dec 2006
  [+] Added fields 'showdisabled', 'showinvisible' to table Pixelpost_CustomFieldsSettings.
  [+] Added user settings 'Show disabled fields' and 'Show invisible fields'.
  [+] If you delete custom fields, records from pixelpost_customfieldsvalues is deleted too.
  [+] Added some statictics (number of custom fields: overall, invisible, disabled).
  [*] Minor enhancements.
  [-] Fixed problem with Visible in template.

0.3 14 Dec 2006
  [!] Fixed some problems with code-security.
  [+] Added warning dialog to "Delete" link.

0.4 15 Dec 2006
  [!] Added bilingual feature (BETA, for compatibility with Pixelpost 1.6 and higher).
  [-] Fixed bug with settings table creating.
  [-] Fixed bug with adding new custom field record.

0.5 17 Dec 2006[*] New installation creates table pixelpost_customfields with one default record.
  [*] Tags <b> changed to <strong>.
  [*] Addon transformed into one file: admin_customfields.php.
  [*] When adding new custom field, Order filled automatically.
  [*] Removed information about tables existence.
  [+] Added HTML example for prefixes and suffixes.

0.6 18 Dec 2006
  [-] Fixed bug with blank custom field saving of editing image.
  [-] Fixed bug with custom field saving of new image.

0.7 18 Dec 2006
  [*] Small code corrections. First Release.

0.8 28 Dec 2006
  [+] Now you can sort Custom Fields by ID, by Name or by Order (default).
  [-] Fixed small sorting bug.

0.9 01 Apr 2007
  [!] Bilingual feature is fully completed. Required Pixelpost 1.6!
  [+] Added tags <CUSTOM_FIELD_NAME_ID=NNN> and <CUSTOM_FIELD_VALUE_ID=NNN>, where NNN is a ID of the
      custom field. These tags show only name and values of the selected custom field without prefixes
      and suffixes.

0.91 23 Jul 2007
  [*] You can insert invisible custom fields with tags <CUSTOM_FIELD_NAME_ID=NNN> and
      <CUSTOM_FIELD_VALUE_ID=NNN>. The <CUSTOM_FIELDS> tag doesn't show invisible custom fields.

1.0 08 Nov 2007
  [+] Added some statistical tags <CUSTOM_FIELD_COUNTER> and <CUSTOM_FIELD_VALUES_COUNTER> - number
      of the custom fields and custom fields values.
  [+] Added "Check for update" (admin area) feature (via http://versioncheckr.com).
  [*] Minor corrections

1.1 25 Jun 2008
  [+] Added <CUSTOM_FIELDS_KEYWORDS_VALUES> - a list of custom fields values for META Keywords
      (only alphabetical characters, digits, spaces and hyphens allowed, list separator - comma).
      Added ability to exclude selected custom fields from keywords-list in the addon settings
      (checkbox "Not a keyword").
  [+] Added some sample custom fields for new addon installation.
  [*] All tags now are case-insensitive.
  [*] Minor code optimization and corrections.
  [-] Fixed bug with Visible in non-bilingual mode.

1.2 08 Jul 2008
  [-] All tags now are case-sensitive back (PHP4 compatible function str_replace instead of str_ireplace).
  [*] Increased increment interval of custom fields order from 1 to 10.

1.3 25 Aug 2008
  [+] Autoreplace URL 'http://<URL>' at the custof field's values to HTML-code, for example, 
          'http://www.photoblog.com/large001.jpg' 
      will be replaced to
          '<a href="http://www.photoblog.com/large001.jpg">http://www.photoblog.com/large001.jpg</a>' 
      Note: you must fill CF value exactly URL without beginnig of trailing spaces or other characters.

1.4 09 Jul 2009
  [*] META keyword fields marked by icon at the New Image page.


INSTALLATION
------------

1. Unzip and upload the admin_customfields.php file to your /Addon or /addon directory.

2. Login to the admin area and click on Addons. The database tables will be created 
   automatically when you enable Custom Fields.

3. Add your custom fields as needed. Give each custom field a name, choose to make each field 
   Visible (or not), to Enable it (or not), and whether or not it should have a Default value 
   (optional). Change the settings as you wish.

4. Place <CUSTOM_FIELDS> or/and <CUSTOM_FIELD_NAME_ID=NNN>, <CUSTOM_FIELD_VALUE_ID=NNN>,
<CUSTOM_FIELD_COUNTER>, <CUSTOM_FIELD_VALUES_COUNTER> tags in your template.

5. (Optional) Place <CUSTOM_FIELDS_KEYWORDS_VALUES> into the <head></head> section of your template. 
   For example: <meta http-equiv="content-type" name="keywords"
   content="PhotoBlog,<SITE_TITLE>,<IMAGE_TITLE>,Pixelpost,<CUSTOM_FIELDS_KEYWORDS_VALUES>" />
   You can exclude certain custom fields from the keywords-list by checking the "Not a keyword" 
   checkbox in the addon settings.

6. That's all. Have fun with Custom Fields!


TODO
----
1) AJAX autocomplete (autosuggest)

Any other suggestions?


SPECIAL THANKS
--------------

All Pixelpost Team
Schonhose, GeoS (http://www.pixelpost.org)

Andriy Zolotiy, Witty

-- 
Klimin Andrew (http://photoblog.birsk.info)
2006-2009