<?php
namespace Migx\Model\mysql;

use xPDO\xPDO;

class migxFormtab extends \Migx\Model\migxFormtab
{

    public static $metaMap = array (
        'package' => 'Migx\\Model\\',
        'version' => '3.0',
        'table' => 'migx_formtabs',
        'extends' => 'xPDO\\Om\\xPDOSimpleObject',
        'tableMeta' => 
        array (
            'engine' => 'InnoDB',
        ),
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
                'class' => 'Migx\\Model\\migxFormtabField',
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
                'class' => 'Migx\\Model\\migxConfig',
                'local' => 'config_id',
                'foreign' => 'id',
                'cardinality' => 'one',
                'owner' => 'foreign',
            ),
        ),
    );

}
