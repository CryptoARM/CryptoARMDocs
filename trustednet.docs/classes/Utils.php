<?php
namespace TrustedNet\Docs;

/**
 * Various utility functions
 */
class Utils
{

    /**
     * Creates new document
     *
     * @param string $file Path to file
     * @param string $propertyType User-set property (ORDER)
     * @param string $propertyValue User-set property value (order number)
     * @param boolean $copy If true copies the document into module folder
     * @return object Document
     */
    public static function createDocument($file, $propertyType = null, $propertyValue = null, $copy = false)
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
        $doc->setType(DOC_TYPE_FILE);
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
        Utils::log(array(
            "action" => "created",
            "docs" => $doc,
        ));
        return $doc;
    }

    /**
     * Handles CLIENT and SELLER signing roles.
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
     * Filter for documents that don't need to be signed,
     * based on their ROLES property and EXTRA argument to the sign function.
     *
     * @param object Document $doc
     * @param string JSON $extra
     * @return boolean
     */
    public static function checkDocByRole($doc, $extra)
    {
        // TODO: rename function
        $status = $doc->getProperties()->getItemByType("ROLES");
        if (!$status) {
            return true;
        }
        $statusValue = $status->getValue();
        if ($extra == "CLIENT") {
            if ($statusValue == "SELLER" || $statusValue == "NONE") {
                return true;
            } else {
                return false;
            }
        } elseif ($extra == "SELLER") {
            if ($statusValue == "CLIENT" || $statusValue == "NONE") {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns textual representation of role property
     *
     * @param object Document $doc
     * @return string
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
     * Initiates file download
     *
     * @param string $filepath
     * @param string $filename
     */
    public static function download($filepath, $filename)
    {
        if (file_exists($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            ob_clean();
            flush();
            readfile($filepath);
            exit;
        }
    }

    /**
     * Validation for user-set Property Type field.
     *
     * Length between 1 and 50.
     * Only latin and cyrillic, numbers and three symbols "- _ .".
     *
     * @param string $type
     * @return boolean
     */
    public static function propertyTypeValidation($type)
    {
        $res = true;
        $len = mb_strlen($type, "UTF-8");
        if ($len = 0 || $len > 50)
            $res = false;
        $cyr = GetMessage("TN_DOCS_CYR");
        $pattern = "/^[A-Za-z" . $cyr . "0-9\-\_\.]*$/u";
        if (!preg_match($pattern, $type))
            $res = false;
        return $res;
    }

    /**
     * Validation for user-set Property Value field.
     *
     * Length between 1 and 255.
     * Whitespaces not allowed.
     * Checks for space, tab, line feed, carriage return, NUL-byte and vertical tab.
     *
     * @param string $value
     * @return boolean
     */
    public static function propertyValueValidation($value)
    {
        $res = true;
        $len = mb_strlen($value, "UTF-8");
        if ($len = 0 || $len > 255)
            $res = false;
        if (preg_match("[\ \t\n\r\0\x0B]", $value))
            $res = false;
        return $res;
    }

    /**
     * Reads n last lines from the file.
     *
     * @author Torleif Berger, Lorenzo Stanco
     * @link http://stackoverflow.com/a/15025877/995958
     * @license http://creativecommons.org/licenses/by/3.0/
     * @param string $path Path to the file
     * @param int $lines Number of lines to read
     * @param mixed $adaptive
     * @return string
     */
    public static function tail($filepath, $lines = 1, $adaptive = true)
    {
        $f = @fopen($filepath, "rb");
        if ($f === false) {
            return false;
        }
        // Sets buffer size, according to the number of lines to retrieve.
        // This gives a performance boost when reading a few lines from the file.
        if (!$adaptive) {
            $buffer = 4096;
        } else {
            $buffer = ($lines < 2 ? 64 : ($lines < 10 ? 512 : 4096));
        }
        // Jump to last character
        fseek($f, -1, SEEK_END);
        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") {
            $lines -= 1;
        }
        // Start reading
        $output = "";
        $chunk = "";
        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);
            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);
            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;
            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, "8bit"), SEEK_CUR);
            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }
        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }
        // Close file and return
        fclose($f);
        return trim($output);
    }

    /**
     * Logs operation details into module log file
     *
     * @param array $logArray [action]: usually function name
     *                        [docs]: Document or DocumentCollection subjected to action
     *                        [extra]: extra info associated with action
     * @return void
     */
    public static function log($logArray)
    {
        $logFile = fopen(TN_DOCS_LOG_FILE, "a");
        $logTime = date("Y-m-d H:i:s", time());
        $logAction = $logArray["action"];
        $logDocs = $logArray["docs"];
        $logExtra = $logArray["extra"];
        if ($logDocs) {
            $logDocsParsed = array();
            if (get_class($logDocs) === "TrustedNet\Docs\Document") {
                $logDocsParsed[] = $logDocs->getId() . "(" . $logDocs->getName() . ")";
            }
            if (get_class($logDocs) === "TrustedNet\Docs\DocumentCollection") {
                foreach ($logDocs->getList() as $doc) {
                    $logDocsParsed[] = $doc->getId() . "(" .$doc->getName() . ")";
                }
            }
        }
        fwrite($logFile, $logTime);
        if ($logAction) {
            fwrite($logFile, " action:" . print_r($logAction, true));
        }
        if ($logDocsParsed) {
            fwrite($logFile, " docs:" . implode(",", $logDocsParsed));
        }
        if ($logExtra && $logExtra != "null") {
            fwrite($logFile, " extra:" . print_r($logExtra, true));
        }
        fwrite($logFile, "\n");
        fclose($logFile);
    }

    /**
     * Print debug info to log.txt in site root
     *
     * @param mixed $var
     * @param string $name
     * @return void
     */
    public static function debug($var, $name = "VAR")
    {
        $logFile = fopen($_SERVER["DOCUMENT_ROOT"] . "/log.txt", "a");
        $logTime = date("Y-m-d H:i:s", time());
        fwrite($logFile, "##################################\n");
        fwrite($logFile, $logTime . " - " . $name . "\n");
        fwrite($logFile, "----------------------------------\n");
        fwrite($logFile, print_r($var, true) . "\n\n\n");
        fclose($logFile);
    }

    /**
     * Display error in browser window
     *
     * @param string|array|number $msg
     * @return void
     */
    public static function throwError($msg)
    {
        header("HTTP/1.1 500 Internal Server Error");
        echo json_encode(array("success" => false, "message" => $msg));
        die();
    }

}

