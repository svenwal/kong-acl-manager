<?php
    /* This file hosts all classes and functions about Kong ACL Groups */
    require_once("load_config.php");
    require_once("kong/kong_admin_api.php");

    class AclGroup
    {
        public $name;
        public $consumersIds;
        public $consumers;
    }

    function fetch_acl_groups($workspace, $consumers) {
        $acls = kong_admin_api_call("/acls", $workspace);
        $groups = array();

        foreach ($acls["json"]->data as $group) {
            $groupObject = new AclGroup();
            $groupObject->name = $group->group;
            $groupObject->consumersIds[] = $group->consumer->id;
            if (isset($groups[$groupObject->name])) {
                $groups[$groupObject->name]->consumersIds[] = $group->consumer->id;
                $groups[$groupObject->name]->consumers[] = $consumers[$group->consumer->id];
            } else {
                $groups[$groupObject->name] = $groupObject;
                $groups[$groupObject->name]->consumers[] = $consumers[$group->consumer->id];
            }
        }
        return $groups;
    }


?>