<?php

$resource_id = $_REQUEST['resource_id'];


$this->customconfigs['joinaliases'] = '
[
{"alias":"ResourceRelation","classname":"rrResourceRelation","on":"ResourceRelation.source_id = modResource.id"}
]';

$this->customconfigs['where'] = '
{"ResourceRelation.target_id":"'.$resource_id.'","ResourceRelation.active":"1"}
';

$this->customconfigs['sourceortarget'] = 'target';