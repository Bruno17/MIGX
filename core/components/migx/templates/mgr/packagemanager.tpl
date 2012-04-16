<h2>Package Management</h2>

<form method="post">
<input type="hidden" name="processPackageManager" value="1" />

packageName: <input type="text" name="packageName" value="{$request.packageName}" /><br/>
<br/>
<p>
Create new package-directory and an empty schema-file
</p><br/>
<input type="submit" name="createPackage" value="Create package" /><br/>

<hr />
<p>
Write schema from existing tables
</p><br/>
table-prefix: <input type="text" name="prefix"/><br/><br/>
<input type="submit" name="writeSchema" value="Write schema" /><br/>

<hr />
<p>
Create xpdo-classes and maps if new or manipulate existing maps from schema
</p><br/>
<input type="submit" name="parseSchema" value="Parse schema" /><br/>

<hr />
<p>
Create tables from schema
</p><br/>
<input type="submit" name="createTables" value="Create Tables" /><br/>

<hr />
<p>
Add missing fields to package-tables from schema
</p><br/>
<input type="submit" name="autoaddfields" value="Add fields" /><br/>

<hr />
<p>
Remove in schema deleted fields in package-tables
</p><br/>
<input type="submit" name="removefields" value="Remove fields" /><br/>

<hr />
<p>
Load/Edit schema
</p><br/>
<textarea name="schema" style="width:90%;height:500px">
{$schema}
</textarea>
<br/>

<input type="submit" name="loadSchema" value="Load schema" /> <input type="submit" name="saveSchema" value="Save schema" /><br/>

</form>