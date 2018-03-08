<?php
namespace TrustedNet\Docs;

/**
 * Various utility functions
 */
class Utils
{

    /**
     * Creates new document
     * @param string $file Path to file
     * @param boolean $copy If true copies the document into module folder
     * @param string $propertyType User-set property (ORDER)
     * @param string $propertyValue User-set property value (order number)
     * @param string $type File type. By default DOC_TYPE_FILE
     * @return object Document
     */
    public static function createDocument($file, $copy, $propertyType = null, $propertyValue = null, $type = DOC_TYPE_FILE)
    {
        $name = Directory::getFileName($file);
        if ($copy && Directory::exists($file) && !is_dir($file)) {
            $order_folder = TRUSTED_PROJECT_UPLOAD . '/' . $propertyType;
            $order_local_folder = $_SERVER['DOCUMENT_ROOT']. '/' . $order_folder;
            if (!Directory::exists($order_local_folder)) {
                Directory::create($order_folder);
            }
            $new_path = $order_local_folder . '/' . $name;

            move_uploaded_file($file, $new_path);
            $file = $new_path;
        }
        $doc = new Document();
        $doc->setPath(str_replace($name, rawurlencode($name), $file));
        $doc->setName($name);
        $doc->setType($type);
        $docId = $doc->getId();
        $props = $doc->getProperties();
        if ($propertyType) {
            $props->add(new Property($docId, $propertyType, $propertyValue));
            // Documents by order need an additional parameter
            if ($propertyType == "ORDER") {
                $props->add(new Property($docId, "ROLES", "NONE"));
            }
        }
        $doc->save();

        return $doc;
    }

    /**
     *
     * @param object Document $doc
     * @param string JSON $extra
     * @return void
     */
    public static function roleHandler($doc, $extra = null)
    {
        $role = $extra["role"];
        $props = $doc->getProperties();
        $roleProp = $props->getItemByType("ROLES");
        if ($roleProp) {
            if ($roleProp->getValue() == "CLIENT" && $role == "SELLER") {
                $roleProp->setValue("BOTH");
            }
            if ($roleProp->getValue() == "SELLER" && $role == "CLIENT") {
                $roleProp->setValue("BOTH");
            }
            if ($roleProp->getValue() == "NONE") {
                if ($extra) {
                    $roleProp->setValue($role);
                }
            }
        }
    }

    /**
     * Returns textual representation of role property
     *
     * @param object Document $doc
     */
    public static function getRoleString($doc)
    {
        $state = $doc->getProperties()->getItemByType("ROLES");
        $str = "";
        if ($state) {
            $state_value = $state->getValue();
            switch ($state_value) {
            case "CLIENT":
                $str = GetMessage("ROLES_CLIENT");
                break;
            case "SELLER":
                $str = GetMessage("ROLES_SELLER");
                break;
            case "BOTH":
                $str = GetMessage("ROLES_BOTH");
                break;
            case "NONE":
                $str = GetMessage("ROLES_NONE");
                break;
            default:
            }
        } else {
            $str = GetMessage("ROLES_NONE");
        }
        return $str;
    }

    /**
     * Print debug info to log.txt in site root
     *
     * @param mixed $var
     * @param string $name
     * @return void
     */
    public static function debug($var, $name = "VAR") {
        $myfile = fopen($_SERVER["DOCUMENT_ROOT"] . "/log.txt", "a"); $logtime = date("d-m-Y H:i:s", time());
        fwrite($myfile, "$logtime"."\n" . $name . ":\n_START_\n".print_r($var, true) . "\n_END_\n\n");
        fclose($myfile);
    }

    /**
     * Display error in browser window
     *
     * @param string|array|number $msg
     * @return void
     */
    public static function throwError($msg) {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(array("success" => false, "message" => $msg));
        die();
    }

}

