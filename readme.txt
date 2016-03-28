--------------------
MIGX
--------------------
Version: 2.1.0
Author: Bruno Perner <b.perner@gmx.de>
--------------------

* MIGX (multiItemsGridTv for modx) is a custom-tv-input-type for adding multiple items into one TV-value and a snippet for listing this items on your frontend.
* It has a configurable grid and a configurable tabbed editor-window to add and edit items.
* Each item can have multiple fields. For each field you can use another tv-input-type.

Feel free to suggest ideas/improvements/bugs on GitHub:
http://github.com/Bruno17/multiItemsGridTV/issues

Installation:

install by package-management.
Create a new menu:
System -> Actions 

Actions-tree:
migx -> right-click -> create Acton here
controller: index
namespace: migx
language-topics: migx:default,file

menu-tree:
Components -> right-click -> place action here
lexicon-key: migx
action: migx - index
parameters: &configs=migxconfigs||packagemanager||setup

clear cache
go to components -> migx -> setup-tab -> setup

If you are upgrading from MIGX - versions before 2.0
go to tab upgrade. click upgrade.
This will add a new autoincrementing field MIGX_id to all your MIGX-TV-items
The getImageList-snippet needs this field to work correctly.


Allways after upgrading MIGX of any Version:
go to components -> migx -> setup-tab -> setup

this will upgrade the migx-configs-table and add new fields, if necessary.


