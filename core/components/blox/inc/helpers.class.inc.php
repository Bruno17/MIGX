<?php

class bloxhelpers
{

    function bloxhelpers()
    {

    }

    function getSiteMap($items, $level = 0)
    {
        /* $start = array (array ('id'=>0));
        * $map = getSiteMap($start);
        * print_r($map);
        */

        global $modx;
        $pages = array();
        foreach ($items as $item)
        {
            $page = $item;
            $page['id'] = $item['id'];
            $page['level'] = $level;
            $page['_haschildren'] = '0';
            //$page['URL'] = $modx->makeUrl($item['id']);
            //$children = $modx->getAllChildren($item['id'], 'menuindex ASC, pagetitle', 'ASC', 'id,isfolder,pagetitle,description,parent,alias,longtitle,published,deleted,hidemenu');

            $c = $modx->newQuery('modResource', array('parent' => $item['id']));
            $childs = $modx->getCollection('modResource', $c);
            $children = array();
            foreach ($childs as $child)
            {
                $children[] = $child->toArray();
            }

            if (count($children) > 0)
            {
                $nextlevel = $level + 1;
                $children = $this->getSiteMap($children, $nextlevel);
                $page['innerrows']['level_' . $nextlevel] = $children;
                $page['innerrows']['children'] = $children;
                $page['_haschildren'] = '1';
            }
            $pages[] = $page;
        }

        return $pages;
    }

    //////////////////////////////////////////////////////////////////////////
    //Member Check
    //////////////////////////////////////////////////////////////////////////
    function isMemberOf($groups)
    {
        global $modx;
        if ($groups == 'all')
        {
            return true;
        } else
        {
            $webgroups = explode(',', $groups);
            if ($modx->user->isMember($webgroups))
            {
                return true;
            } else
            {
                return false;
            }
        }
    }

    /////////////////////////////////////////////////////////////////////////////
    //function to check for permission
    /////////////////////////////////////////////////////////////////////////////

    function checkpermission($permission)
    {
        $groupnames = $this->getwebusergroupnames();
        $perms = '';
        foreach ($groupnames as $groupname)
        {
            $perms .= $this->bloxconfig['permissions'][$groupname] . ',';
        }
        $perms = explode(',', $perms);
        return in_array($permission, $perms);
    }

    /**
     * Sort DB result
     *
     * @param array $data Result of sql query as associative array
     *
     * Rest of parameters are optional
     * [, string $name  [, mixed $name or $order  [, mixed $name or $mode]]]
     * $name string - column name i database table
     * $order integer - sorting direction ascending (SORT_ASC) or descending (SORT_DESC)
     * $mode integer - sorting mode (SORT_REGULAR, SORT_STRING, SORT_NUMERIC)
     *
     * <code>
     *
     * // You can sort data by several columns e.g.
     * $data = array();
     * for ($i = 1; $i <= 10; $i++) {
     *     $data[] = array( 'id' => $i,
     *                      'first_name' => sprintf('first_name_%s', rand(1, 9)),
     *                      'last_name' => sprintf('last_name_%s', rand(1, 9)),
     *                      'date' => date('Y-m-d', rand(0, time()))
     *                  );
     * }
     * $data = sortDbResult($data, 'date', SORT_DESC, SORT_NUMERIC, 'id');
     * printf('<pre>%s</pre>', print_r($data, true));
     * $data = sortDbResult($data, 'last_name', SORT_ASC, SORT_STRING, 'first_name', SORT_ASC, SORT_STRING);
     * printf('<pre>%s</pre>', print_r($data, true));
     *
     * </code>
     *
     * @return array $data - Sorted data
     */

    function sortDbResult($_data)
    {


        $_argList = func_get_args();
        $_data = array_shift($_argList);
        if (empty($_data))
        {
            return $_data;
        }
        $_max = count($_argList);
        $_params = array();
        $_cols = array();
        $_rules = array();
        for ($_i = 0; $_i < $_max; $_i += 3)
        {
            $_name = (string )$_argList[$_i];
            if (!in_array($_name, array_keys(current($_data))))
            {
                continue;
            }
            if (!isset($_argList[($_i + 1)]) || is_string($_argList[($_i + 1)]))
            {
                $_order = SORT_ASC;
                $_mode = SORT_REGULAR;
                $_i -= 2;
            } else
                if (3 > $_argList[($_i + 1)])
                {
                    $_order = SORT_ASC;
                    $_mode = $_argList[($_i + 1)];
                    $_i--;
                } else
                {
                    $_order = $_argList[($_i + 1)] == SORT_ASC ? SORT_ASC : SORT_DESC;
                    if (!isset($_argList[($_i + 2)]) || is_string($_argList[($_i + 2)]))
                    {
                        $_mode = SORT_REGULAR;
                        $_i--;
                    } else
                    {
                        $_mode = $_argList[($_i + 2)];
                    }
                }
                $_mode = $_mode != SORT_NUMERIC ? $_argList[($_i + 2)] != SORT_STRING ? SORT_REGULAR : SORT_STRING : SORT_NUMERIC;
            $_rules[] = array(
                'name' => $_name,
                'order' => $_order,
                'mode' => $_mode);
        }
        foreach ($_data as $_k => $_row)
        {
            foreach ($_rules as $_rule)
            {
                if (!isset($_cols[$_rule['name']]))
                {
                    $_cols[$_rule['name']] = array();
                    $_params[] = &$_cols[$_rule['name']];
                    $_params[] = $_rule['order'];
                    $_params[] = $_rule['mode'];
                }
                $_cols[$_rule['name']][$_k] = $_row[$_rule['name']];
            }
        }
        $_params[] = &$_data;
        call_user_func_array('array_multisort', $_params);
        return $_data;
    }


    //////////////////////////////////////////////////////////////////////
    // Ditto - Functions
    // Author:
    // 		Mark Kaplan for MODx CMF
    //////////////////////////////////////////////////////////////////////

    // ---------------------------------------------------
    // Function: cleanIDs
    // Clean the IDs of any dangerous characters
    // ---------------------------------------------------

    function cleanIDs($IDs)
    {
        //Define the pattern to search for
        $pattern = array(
            '`(,)+`', //Multiple commas
            '`^(,)`', //Comma on first position
            '`(,)$`' //Comma on last position
                );

        //Define replacement parameters
        $replace = array(
            ',',
            '',
            '');

        //Clean startID (all chars except commas and numbers are removed)
        $IDs = preg_replace($pattern, $replace, $IDs);

        return $IDs;
    }

}

?>