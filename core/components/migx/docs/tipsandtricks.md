##Working with joined tables

###one-to-one relation

[joins](configcmp.md#joins) db setting example:

```[{"alias":"Profile"}]```

you will have access in the CMP to fields and columns for example with: `Profile_fullname`
The alias + `_` will be the prefix for your fields in the CMP

####updating

If you need to handle joined tables in the update-processor, you can either create a custom-processor in your package-folder or use a [hooksnippet](configcmp.md#hook-snippets).

example snippet:
```
$object = & $modx->getOption('object',$scriptProperties,null);//reference to the saved object
$properties = $modx->getOption('scriptProperties',$scriptProperties,array());//the processors scriptProperties
$postvalues = $modx->getOption('postvalues',$scriptProperties,array());//the posted values
 
$fullname = $modx->getOption('Profile_fullname',$postvalues,'');
$result = array(); 
if ($object){
    $object_id = $object->get('id');
    if ($profile = $modx->getObject('modProfile'){
        
    } 
    else {
        $profile = $modx->newObject('modProfile');
        $profile->set('internalkey',$object_id);
    }
    if ($profile){
        $profile->set('fullname',$fullname);
        if ($profile->save()){
        
        } else {
            $result = array('error' => 'could not save Profile');
        }
    }    
    
}
 
return $modx->toJson($result);
```

###one-to-many relation

##Custom Processors