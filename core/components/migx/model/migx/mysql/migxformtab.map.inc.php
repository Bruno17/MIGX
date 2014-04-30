<?php
$xpdo_meta_map['migxFormtab']= array (
  'package' => 'migx',
  'version' => '1.1',
  'table' => 'migx_formtabs',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'config_id' => 0,
    'caption' => '',
    'pos' => 0,
    'print_before_tabs' => 0,
    'extended' => '',
  ),
  'fieldMeta' => 
  array (
    'config_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'caption' => 
    array (
      'dbtype' => 'varchar',
      'phptype' => 'string',
      'precision' => '255',
      'null' => false,
      'default' => '',
    ),
    'pos' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'print_before_tabs' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'extended' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'json',
      'null' => false,
      'default' => '',
    ),
  ),
  'composites' => 
  array (
    'Fields' => 
    array (
      'class' => 'migxFormtabField',
      'local' => 'id',
      'foreign' => 'formtab_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
    'Config' => 
    array (
      'class' => 'migxConfig',
      'local' => 'config_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);
