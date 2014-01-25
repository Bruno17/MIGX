<?php

$resource_id = $_REQUEST['resource_id'];

$this->customconfigs['parents'] = '225,1769,691,675,188,2114';

$this->customconfigs['joinaliases'] = '
[
{"alias":"Parent","selectfields":"id,pagetitle"}
,{"alias":"ResourceRelation","classname":"rrResourceRelation","on":"ResourceRelation.target_id = modResource.id AND ResourceRelation.source_id =' . $resource_id . '"}
]';

$this->customconfigs['sort'] = '
[{"Parent.pagetitle","ASC"},{"modResource.pagetitle","ASC"}]
';

$this->customconfigs['sourceortarget'] = 'source';