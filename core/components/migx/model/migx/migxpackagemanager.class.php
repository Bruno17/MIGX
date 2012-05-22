<?php
class MigxPackageManager extends xPDOGenerator_mysql
{
    function __construct(modX & $modx, array $config = array())
    {
        $this->modx = &$modx;
        $this->maps = array();

        $defaultconfig = array();
        $this->config = array_merge($defaultconfig, $config);

        $this->manager = $this->modx->getManager();

    }

    public function compile($path = '')
    {
        $this->packageClasses = $this->classes;
        return true;
    }

    public function createTables()
    {
        if (count($this->packageClasses) > 0) {
            foreach ($this->packageClasses as $class => $value) {
                $this->manager->createObjectContainer($class);           
            }
        }
    }

    public function checkClassesFields($options)
    {
        $addmissing = $this->modx->getOption('addmissing',$options,false);
        $removedeleted = $this->modx->getOption('removedeleted',$options,false);
        $modfields = array();
        if (count($this->packageClasses) > 0) {
            foreach ($this->packageClasses as $class => $value) {
                if ($addmissing){
                    $this->addMissingFields($class,$modfields);
                }
                if ($removedeleted){
                    $this->removeDeletedFields($class,$modfields);
                }                
            }
        }
        return $modfields;
    }

    public function addMissingFields($class, & $modfields)
    {
        $table = $this->modx->getTableName($class);
        $fieldsStmt = $this->modx->query('SHOW COLUMNS FROM ' . $table);
        if ($fieldsStmt) {
            $fields = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (count($fields) > 0) {
            foreach ($fields as $field) {
                $tablefields[] = $field['Field'];
            }
        }

        $classfields = $this->modx->getFields($class);
        if (count($classfields) > 0) {
            foreach ($classfields as $field => $value) {
                if (!in_array($field,$tablefields)){
                    $this->manager->addField($class,$field);
                    $modfields['added'][] = $class.':'.$field;
                }
            }
        }

        //$meta = $this->modx->getFieldMeta($class);
        //echo '<psre>' . print_r($fields, 1) . print_r($classfields, 1)
            //.print_r($meta,1)
            //. '</psre>';
    }
    public function removeDeletedFields($class, & $modfields)
    {
        $table = $this->modx->getTableName($class);
        $fieldsStmt = $this->modx->query('SHOW COLUMNS FROM ' . $table);
        if ($fieldsStmt) {
            $fields = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (count($fields) > 0) {
            foreach ($fields as $field) {
                $tablefields[] = $field['Field'];
            }
        }

        $classfields = $this->modx->getFields($class);
        if (count($tablefields) > 0) {
            foreach ($tablefields as $field) {
                if (!array_key_exists($field,$classfields)){
                    $modfields['deleted'][] = $class.':'.$field;
                    $this->manager->removeField($class,$field);
                }
            }
        }
      
        //$meta = $this->modx->getFieldMeta($class);
        //echo '<psre>' . print_r($fields, 1) . print_r($classfields, 1)
            //.print_r($meta,1)
            //. '</psre>';
    } 
      

}
