# MIGX

Multi-Items-Grid for MODX-Revolution.

##What is MIGX

MIGX is a <a href="https://rtfm.modx.com/revolution/2.x/making-sites-with-modx/customizing-content/template-variables/adding-a-custom-tv-input-type" title="Adding a Custom TV Input Type">custom</a> <a href="https://rtfm.modx.com/revolution/2.x/making-sites-with-modx/customizing-content/template-variables" title="Template Variables">Template Variable</a> (TV) input type for aggregating multiple TVs into one TV. This aggregation greatly simplifies the workflow for end users of the manager to add complex data items to the manager. A data item can consist of any number of any other TVs, including text, images, files, checkboxes, etc.

The package is highly customizable and allows the developer to define a custom input window for the MIGX TV. From this input window, items can be added, modified, and reordered.

The package also ships with a snippet (<a href="/extras/revo/migx/migx.frontend-usage" title="MIGX.Frontend-Usage">getImageList</a>) that facilitates the easy retrieval of the complex data items from the custom MIGX TV input type.

##MIGXdb

While MIGX stores its items all in one field as a json-string, MIGXdb handles records of custom-tables.
With a MIGXdb-TV, you can handle resource-related-records of a custom-table or even child-resources of the currently edited resource.

Its also very easy to create CMPs (Custom Manager Pages) to manage your custom-tables with help of MIGXdb.

There is also a snippet (migxLoopCollection) to show records of custom-tables at the frontend. 
