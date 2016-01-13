##Tab Settings

###Name

###"Add Item" Replacement

###Disable Add Items

###Add Items directly

###Form Caption

###Window Title

###unique MIGX ID

###max MIGX records

###Add new MIGX records at

##Tab Formtabs

###Fields

This is the MIGX-grid, where you create the formlayout.
You can:

- Create Tabs with 'Add Formtab'
- Add Fields to the Tab with 'Add Field'
- Create Layout-Rows for Column-Groups with 'Add Layout'
- Add Columns to Layout-Rows with 'Add Column'

####Options for Formtabs

Field | Purpose
------|--------
Caption | The Caption for the tab
Display above Tabs | Display its fields above the tab-panel

####Options for Fields

Tab | Field | Purpose
----|-------|--------
Field | Fieldname | the name of the Field
      | Caption   | The Form - caption of the Field
      | Description | A description for that Field
      | Description is Code | if yes, The code of the description-field is displayed in the form, you can use placeholders and other MODX-tags here.
      | Input TV | you can create a helper-TV, which is used for the input, when you put its name here. Never use a TV as helper-TV, which is/was ever assigned to a template!
      | Input TV type | if you set a Input TV type, you don't need a helper-TV. This is used for the input and Input TV is ignored, then. Most Input-TV-types are supported, also custom-TV-types. Find the default TV-types <a href="https://github.com/modxcms/revolution/tree/2.x/core/model/modx/processors/element/tv/renders/mgr/input">here</a>. Use the name without '.class.php'  
      | Validation | Add validations for that field. The default update-processor supports only 'required'. Starting with version 2.10 you can have a validate-hook-snippet. <a href="https://github.com/Bruno17/MIGX/blob/master/core/components/migx/elements/snippets/migx_example_validate.snippet.php">Example</a>
      | Configs | If the input TV type is migx or migxdb, put here the name of its MIGX-configuration. For other TV-types you can add here type-specific input-properties as json.
      | Restrictive Condition (MODX tags) | This can be used to hide fields depending on conditions. Use snippets or outputfilters. If it returns an empty string, the field is displayed.  
      | Display | If 'no' the field isn't displayed at all
Mediasources | source From | <ul><li>config - uses the mediasources of this config added with 'Add item'</li><li>tv - uses the mediasource of the helper-TV (input TV)</li><li>migx - if the configuration is for a MIGX-TV, uses the mediasources of that MIGX-TV</li></ul>   
             | Sources | Add the mediasource-ids here. Example: Context:mgr,Source:3; Context:web,Source:3
Input Options | <a href="https://rtfm.modx.com/revolution/2.x/making-sites-with-modx/customizing-content/template-variables/template-variable-input-types#TemplateVariableInputTypes-Listbox%28SingleSelect%29">Input Option Values</a> | Add input-option-values for listbox, checkbox and option-type-TVs. <a href="https://rtfm.modx.com/revolution/2.x/making-sites-with-modx/customizing-content/template-variables/bindings">Bindings</a> like @CHUNK, @EVAL, @SELECT can be used here for dynamical created input-options.             
              | Default Value | The default value of that field.

####Options for Layouts

Field | Purpose
------|--------
Caption | Optional Caption for the Layout Row
Style | Additional CSS-Style for that Layout Row

####Options for Columns

Field | Purpose
------|--------
Column width | The width for that column. default: 100% - If you have for example two columns with same witdh try calc(50% - 10px)
Column min-width | if you have inputTVtypes with hardcoded width, for example listbox, try to set a min-with for that column
Caption | Optional Caption for that Column
Style | Additional CSS-Style for that Column

###Multiple Formtabs

###Multiple Formtabs Label

###Multiple Formtabs Label

###Multiple Formtabs Optionstext

###Multiple Formtabs Optionsvalue

##Tab Columns

Define all the grid-columns here.
If this is for a MIGXdb - grid, dont't forget to add the id-field. 
Otherwise MIGX doesn't know, which record to edit. 
This column can also be hidden, if you don't want to show it in the grid.

####Options for Columns

Tab |Field | Purpose
----|------|--------
Column | Header | The Caption for that column
       | Field | the fieldname of that column
       | Column width | the proportional with of columns to each other
       | Sortable | if the column should be sortable
       | Show in Grid | if the column shoulc be shown of hidden from grid. In some situations, the column is needed to get its value, but you don't want to show it in the grid.
Renderer | Custom Renderer | If you want to use a custom-renderer, which isn't in the list. Example: ImagePlus.MIGX_Renderer
         | Renderer | Select a renderer for that column. See the next table for Renderer-descriptions.
Cell Editor | Editor | For ingrid-editing, select an Editor for this column. <ul><li>this.textEditor - simple Text-input</li><li>this.listboxEditor - shows a listbox with the input-options of that field</li></ul>

####Renderers

Renderer | Purpose
---------|--------
this.renderimage | 
this.renderImageFromHtml |
this.renderPlaceholder |
this.renderFirst |
this.renderLimited |
this.renderCrossTick |
this.renderClickCrossTick |
this.renderSwitchStatusOptions |
this.renderPositionSelector |
this.renderRowActions |
this.renderChunk |
ImagePlus.MIGX_Renderer |
this.renderDate |
this.renderOptionSelector |

##Tab Contextmenues

##Tab Columnbuttons

##Tab Actionbuttons

##Tab Window Buttons

##Tab Handlers

##Tab Db-Filters

##Tab MIGXdb-Settings

###Package

###Classname

###Processors Path

###getlist defaultsort

###getlist defaultsortdir

###Sort Config

multifield-sortconfig - json-format

###Items per Page Default

###Prefix

Option   | Purpose
---------|--------
default Prefix | uses the default MODX-prefix
Custom Prefix | uses the prefix, set with 'Custom Prefix'

###Custom Prefix

if you have a custom table-prefix, select 'Custom Prefix' above and put your prefix here. 
This can also be empty.

Option   | Purpose
---------|--------
by Button | if a MIGXdb - TV or a nested grid, this will show a Button to load the grid
auto | this does allways autoload the grid. Doesn't work correctly with MIGXdb - TVs. Use 'by Button' for that case.

###Check Resource

Option   | Purpose
---------|--------
yes | if this is a config for a nested grid, this tries to show only connected records
no | this will show all records in nested grids.

###Check Resource TV

set the value above by a TV of the current Resource, if this config is for a MIGXdb-TV

###Join Alias

if this is the config for a nested grid, the join-alias of the parent-object.

###Has Extra Connection Table

###Where
a where-clause for the getlist-processor as json, to list only specific records

###Joins

Example: `[{"alias":"Profile"}]`

you will have access in the CMP to fields and columns for example with: `Profile_fullname`
The alias + `_` will be the prefix for your fields in the CMP

###Hook Snippets
Example: `{"aftersave":"myaftersave_snippet"}`

Hook-snippets, currently supported by default-processors:

Hook | Purpose | Example
---- | ------- | ------
aftersave | fires after the object was saved | [examples](tipsandtricks.md#working-with-joined-tables)  
validate | fires on field-validation | [example](https://github.com/Bruno17/MIGX/blob/master/core/components/migx/elements/snippets/migx_example_validate.snippet.php)

##Tab CMP-Settings






        
       


