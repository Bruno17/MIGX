<?php

class MigxPackageManager extends xPDOGenerator_mysql {
    function __construct(modX & $modx, array $config = array()) {
        $this->modx = &$modx;
        $this->maps = array();

        $defaultconfig = array();
        $this->config = array_merge($defaultconfig, $config);

        $this->manager = $this->modx->getManager();

    }

    public function compile($path = '') {
        $this->packageClasses = $this->classes;
        return true;
    }

    public function createTables() {
        if (count($this->packageClasses) > 0) {
            foreach ($this->packageClasses as $class => $value) {
                $this->manager->createObjectContainer($class);
            }
        }
    }

    public function checkClassesFields($options) {
        $addmissing = $this->modx->getOption('addmissing', $options, false);
        $removedeleted = $this->modx->getOption('removedeleted', $options, false);
        $checkindexes = $this->modx->getOption('checkindexes', $options, false);
        $alterfields = $this->modx->getOption('alterfields', $options, false);
        $modfields = array();
        if (count($this->packageClasses) > 0) {
            foreach ($this->packageClasses as $class => $value) {
                if ($checkindexes) {
                    $this->checkIndexes($class, $modfields);
                }
                if ($addmissing) {
                    $this->addMissingFields($class, $modfields);
                }
                if ($removedeleted) {
                    $this->removeDeletedFields($class, $modfields);
                }
                if ($alterfields) {
                    $this->alterFields($class, $modfields);
                }                
            }
        }
        return $modfields;
    }
    
    public function alterFields($class, $modfields){
        $table = $this->modx->getTableName($class);
        $fieldsStmt = $this->modx->query('SHOW COLUMNS FROM ' . $table);
        if ($fieldsStmt) {
            $fields = $fieldsStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (count($fields) > 0) {
            foreach ($fields as $field) {
                $this->manager->alterField($class, $field['Field']);
            }
        }
    }

    public function checkIndexes($class, &$modfields) {

        //get current indexes from table
        $table = $this->modx->getTableName($class);
        $indexStmt = $this->modx->query('SHOW INDEX FROM ' . $table);
        if ($indexStmt) {
            $indexes = $indexStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        if (count($indexes) > 0) {
            foreach ($indexes as $index) {
                $tableindexes[] = $index['Key_name'];
            }
        }

        //get fieldmeta from schema
        $meta = $this->modx->getFieldMeta($class);
        if (is_array($meta) && count($meta) > 0) {
            //check for new indexes in fielddefinitions
            foreach ($meta as $field => $value) {
                if (isset($value['index']) && !in_array($field, $tableindexes)) {

                    switch ($value['index']) {
                        case 'pk':
                            break;
                        default:
                            $indexmeta = array();
                            $indexmeta['type'] = strtoupper($value['index']);
                            $column = array();
                            $columns = array();
                            $columns[$field] = $column;
                            $indexmeta['columns'] = $columns;
                            //add field-indexmeta to xpdo-index-map, otherwise addIndex does not work
                            $this->modx->map[$class]['indexes'][$field] = $indexmeta;
                            
                            $this->manager->addIndex($class, $field);
                            $modfields['index_added'][] = $class . ':' . $field;
                            break;
                    }
                }
            }
        }


    }

    public function addIndex($class, $name, array $options = array()) {
        $result = false;
        if ($this->xpdo->getConnection(array(xPDO::OPT_CONN_MUTABLE => true))) {
            $className = $this->xpdo->loadClass($class);
            if ($className) {
                $meta = $this->xpdo->getIndexMeta($className);
                if (is_array($meta) && array_key_exists($name, $meta)) {
                    $idxDef = $this->getIndexDef($className, $name, $meta[$name]);
                    if (!empty($idxDef)) {
                        $sql = "ALTER TABLE {$this->xpdo->getTableName($className)} ADD {$idxDef}";
                        if ($this->xpdo->exec($sql) !== false) {
                            $result = true;
                        } else {
                            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Error adding index {$name} to {$class}: " . print_r($this->xpdo->errorInfo(), true), '', __method__, __file__, __line__);
                        }
                    } else {
                        $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Error adding index {$name} to {$class}: Could not get index definition");
                    }
                } else {
                    $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Error adding index {$name} to {$class}: No metadata defined");
                }
            }
        } else {
            $this->xpdo->log(xPDO::LOG_LEVEL_ERROR, "Could not get writable connection", '', __method__, __file__, __line__);
        }
        return $result;
    }

    public function addMissingFields($class, &$modfields) {
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
                if (!in_array($field, $tablefields)) {
                    $this->manager->addField($class, $field);
                    $modfields['added'][] = $class . ':' . $field;
                }
            }
        }

        //$meta = $this->modx->getFieldMeta($class);
        //echo '<psre>' . print_r($fields, 1) . print_r($classfields, 1)
        //.print_r($meta,1)
        //. '</psre>';
    }
    public function removeDeletedFields($class, &$modfields) {
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
                if (!array_key_exists($field, $classfields)) {
                    $modfields['deleted'][] = $class . ':' . $field;
                    $this->manager->removeField($class, $field);
                }
            }
        }

        //$meta = $this->modx->getFieldMeta($class);
        //echo '<psre>' . print_r($fields, 1) . print_r($classfields, 1)
        //.print_r($meta,1)
        //. '</psre>';
    }


}
