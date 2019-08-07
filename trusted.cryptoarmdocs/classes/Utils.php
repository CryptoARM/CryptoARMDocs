<?php
namespace Trusted\CryptoARM\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

/**
 * Various utility functions
 */
class Utils {
    /**
     * Creates new document
     *
     * @param string $file Path to file
     * @param PropertyCollection $properties User-set properties
     * @return object Document
     */
    public static function createDocument($file, $properties = null) {
        $name = Utils::mb_basename($file);
        $doc = new Document();
        $doc->setPath(str_replace($name, rawurlencode($name), $file));
        $doc->setName($name);
        $doc->setHash(hash_file('md5', $_SERVER['DOCUMENT_ROOT'] . $file));
        if ($properties) {
            $doc->setProperties($properties);
        }
        $doc->save();
        // TODO: log document properties
        Utils::log(array(
            'action' => 'created',
            'docs' => $doc,
        ));
        return $doc;
    }

    /**
     * Runs document ids through filters to find problems before accessing them.
     *
     * @param array[int] $ids Document ids
     * @param string $level Required access level
     * @param bool $allowBlocked Can operation be performed of blocked docs?
     * @return array [docsNotFound]: array of ids that were not found in document database
     *               [docsNoAccess]: documents for which current user has no access
     *               [docsFileNotFound]: documents for which associated file was not found on disk
     *               [docsBlocked]: documents blocked by previous operation
     *               [docsUnsigned]: documents not signed
     *               [docsOk]: documents that passed all checks
     */
    public static function checkDocuments(
        $ids,
        $level = DOC_SHARE_READ,
        $allowBlocked = true,
        $allowSigned = true
    ) {
        $res = array(
            'docsNotFound' => array(),
            'docsNoAccess' => array(),
            'docsFileNotFound' => new DocumentCollection(),
            'docsBlocked' => new DocumentCollection(),
            'docsUnsigned' => new DocumentCollection(),
            'docsOk' => new DocumentCollection(),
        );

        foreach ($ids as $id) {
            $doc = Database::getDocumentById($id);
            if (!$doc) {
                // No doc with that id is found
                $res['docsNotFound'][] = $id;
                continue;
            }
            $doc = $doc->getLastDocument();
            $id = $doc->getId();
            if (!$doc->accessCheck(Utils::currUserId(), $level)) {
                // Current user has no access to the doc
                $res['docsNoAccess'][] = $id;
            } elseif (!$allowBlocked && $doc->getStatus() === DOC_STATUS_BLOCKED) {
                // Doc is blocked by previous operation
                $res['docsBlocked']->add($doc);
            } elseif (!$doc->checkFile()) {
                // Associated file was not found on the disk
                $res['docsFileNotFound']->add($doc);
            } elseif (!$allowSigned && $doc->getType() === DOC_TYPE_FILE) {
                // Document is not signed
                $res['docsUnsigned']->add($doc);
            } else {
                // Document is ready to be processed
                $res['docsOk']->add($doc);
            }
        }

        return $res;
    }

    /**
     * Version of basename which correctly handles cyrillic letters and spaces.
     *
     * @param string $path
     */
    public static function mb_basename($path) {
        if (preg_match('@^.*[\\\\/]([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        } elseif (preg_match('@^([^\\\\/]+)$@s', $path, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Returns textual representation of document type
     *
     * @param object Document $doc
     * @return string
     */
    public static function getTypeString($doc) {
        $docType = $doc->getType();
        return Loc::getMessage('TR_CA_DOCS_TYPE_' . $docType);
    }

    /**
     * Returns textual representation of document status
     *
     * @param object Document $doc
     * @return string
     */
    public static function getStatusString($doc) {
        $docStatus = $doc->getStatus();
        return Loc::getMessage('TR_CA_DOCS_STATUS_' . $docStatus);
    }

    /**
     * Initiates file download
     *
     * @param string $filepath
     * @param string $filename
     */
    public static function download($filepath, $filename) {
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
            exit();
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
    public static function propertyTypeValidation($type) {
        $res = true;
        $len = mb_strlen($type, 'UTF-8');
        if ($len = 0 || $len > 50) {
            $res = false;
        }
        $cyr = Loc::getMessage('TR_CA_DOCS_CYR');
        $pattern = '/^[A-Za-z' . $cyr . "0-9\-\_\.]*$/u";
        if (!preg_match($pattern, $type)) {
            $res = false;
        }
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
    public static function propertyValueValidation($value) {
        $res = true;
        $len = mb_strlen($value, 'UTF-8');
        if ($len = 0 || $len > 255) {
            $res = false;
        }
        if (preg_match("[\ \t\n\r\0\x0B]", $value)) {
            $res = false;
        }
        return $res;
    }

    /**
     * Validation for user-set property value field.
     *
     * Length between 1 and 255.
     * Numbers only.
     *
     * @param string $value
     * @return boolean
     */
    public static function propertyNumericalIdValidation($value) {
        $res = true;
        $len = mb_strlen($value, 'UTF-8');
        if ($len = 0 || $len > 255) {
            $res = false;
        }
        if (!preg_match("/^\d+$/", $value)) {
            $res = false;
        }
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
    public static function tail($filepath, $lines = 1, $adaptive = true) {
        $f = @fopen($filepath, 'rb');
        if ($f === false) {
            return false;
        }
        // Sets buffer size, according to the number of lines to retrieve.
        // This gives a performance boost when reading a few lines from the file.
        if (!$adaptive) {
            $buffer = 4096;
        } else {
            $buffer = $lines < 2 ? 64 : ($lines < 10 ? 512 : 4096);
        }
        // Jump to last character
        fseek($f, -1, SEEK_END);
        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n") {
            $lines -= 1;
        }
        // Start reading
        $output = '';
        $chunk = '';
        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);
            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);
            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;
            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
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
    public static function log($logArray) {
        $logFile = fopen(TR_CA_DOCS_LOG_FILE, 'a');
        $logTime = date('Y-m-d H:i:s', time());
        $logAction = $logArray['action'];
        $logDocs = $logArray['docs'];
        $logExtra = $logArray['extra'];
        if ($logDocs) {
            $logDocsParsed = array();
            if (get_class($logDocs) === 'Trusted\CryptoARM\Docs\Document') {
                $logDocsParsed[] = $logDocs->getId() . '(' . $logDocs->getName() . ')';
            }
            if (get_class($logDocs) === 'Trusted\CryptoARM\Docs\DocumentCollection') {
                foreach ($logDocs->getList() as $doc) {
                    $logDocsParsed[] = $doc->getId() . '(' . $doc->getName() . ')';
                }
            }
        }
        fwrite($logFile, $logTime);
        if ($logAction) {
            fwrite($logFile, ' action:' . print_r($logAction, true));
        }
        if ($logDocsParsed) {
            fwrite($logFile, ' docs:' . implode(',', $logDocsParsed));
        }
        if ($logExtra && $logExtra != 'null') {
            fwrite($logFile, ' extra:' . print_r($logExtra, true));
        }
        fwrite($logFile, "\n");
        fclose($logFile);
    }

    /**
     * Print debug info to log.txt in site root
     *
     * @param ... $vars
     * @return void
     */
    public static function dump(...$vars) {
        $file = fopen($_SERVER['DOCUMENT_ROOT'] . '/log.txt', 'a');
        $time = date('H:i:s ');
        fwrite($file, $time . str_repeat('=', 30) . "\n");
        foreach ($vars as $var) {
            fwrite($file, print_r($var, true) . "\n\n");
        }
        fclose($file);
    }

    public static function dumpCallStack($full = false) {
        $stacktrace = debug_backtrace();
        unset($stacktrace[0]); // hide dumpCallStack call
        if ($full) {
            Utils::dump($stacktrace);
            return;
        }
        $res = '';
        foreach ($stacktrace as $n => $node) {
            $res .=
                "$n. " .
                basename($node['file']) .
                ': ' .
                $node['function'] .
                ' (' .
                $node['line'] .
                ")\n";
        }
        Utils::dump($res);
    }

    /**
     * Display variable in browser and stop execution
     *
     * @param mixed $msg
     * @return void
     */
    public static function printAndDie($var) {
        header('HTTP/1.1 500 Internal Server Error');
        echo '<pre>' . print_r($var, true) . '</pre>';
        die();
    }

    /**
     * Checks whether or not site runs on https.
     *
     * @return bool
     */
    public static function isSecure() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
            $_SERVER['SERVER_PORT'] == 443;
    }

    /**
     * @param $dir Document directory
     * @return bool Return Y/N
     */
    public static function CheckDocumentsDir($dir) {
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        $fullPath = $docRoot . $dir;
        // Expand extra /../
        $fullPath = realpath($fullPath);

        // Check if we are in bitrix root
        $len = strlen($docRoot);
        if (strncmp($fullPath, $docRoot, $len) < 0 || strcmp($fullPath, $docRoot) == 0) {
            return Loc::getMessage('TR_CA_DOCS_DOCS_DIR_CANNOT_USE_SYSTEM_DIRECTORY');
        }

        // Check for entering bitrix system directory
        if (preg_match('/^\/bitrix\/.*/', $dir)) {
            return Loc::getMessage('TR_CA_DOCS_DOCS_DIR_CANNOT_USE_SYSTEM_DIRECTORY');
        }

        // Check for permissions
        if (!is_readable($fullPath) && !is_writable($fullPath)) {
            return Loc::getMessage('TR_CA_DOCS_DOCS_DIR_NO_ACCESS_TO_DIRECTORY');
        }
        return true;
    }

    public static function checkAuthorization() {
        global $USER;
        return $USER->IsAuthorized();
    }

    /**
     * Genereates UUID v.4
     */
    public static function generateUUID() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    /**
     * Gets size as string and converts it to bytes
     *
     * @param string $input String like "10mb" or "1G"
     * @return int|null Number of bytes
     */
    public static function byteconvert($input) {
        preg_match('/(\d+)(\w*)/', $input, $matches);
        $type = strtolower($matches[2]);
        $amount = (int) $matches[1];
        switch ($type) {
            case '':
            case 'b':
                $output = $amount;
                break;
            case 'k':
            case 'kb':
                $output = $amount * 1024;
                break;
            case 'm':
            case 'mb':
                $output = $amount * 1024 * 1024;
                break;
            case 'g':
            case 'gb':
                $output = $amount * 1024 * 1024 * 1024;
                break;
            case 't':
            case 'tb':
                $output = $amount * 1024 * 1024 * 1024 * 1024;
                break;
            default:
                $output = null;
                break;
        }
        return $output;
    }

    /**
     * Get the maximum size of the file that could be uploaded to the server
     */
    public static function maxUploadFileSize() {
        $minMaxSize = array(
            'uploadMax' => Utils::byteconvert(ini_get('upload_max_filesize')),
            'postMax' => Utils::byteconvert(ini_get('post_max_size')),
            'diskMax' => (int) Option::get('main', 'disk_space') * 1024 * 1024,
        );

        // Like min(), but casts to int and ignores 0
        $minNotNull = min(array_diff(array_map('intval', $minMaxSize), array(0)));

        return round($minNotNull / 1024 / 1024, 2);
    }

    public static function isNotEmpty($val) {
        return $val || $val === 0 || $val === 0.0 || $val === '0';
    }

    public static function validateEmailAddress($emailAddress) {
        return filter_var($emailAddress, FILTER_VALIDATE_EMAIL);
    }

    public static function currUserId() {
        global $USER;
        return $USER->GetID();
    }

    public static function isAdmin($userId) {
        return in_array(1, \CUser::getUserGroup($userId));
    }

    public static function getUser($userId = null) {
        if (!$userId) {
            $userId = self::currUserId();
        }
        $user = \CUser::GetByID($userId)->Fetch();
        return $user;
    }

    public static function getUserName($userId = null) {
        $user = Utils::getUser($userId);
        $userName = $user['NAME'] . ' ' . $user['LAST_NAME'];
        if (!trim($userName)) {
            $userName = $user['LOGIN'];
        }
        return $userName;
    }

    public static function getUserEmail($userId = null) {
        $user = Utils::getUser($userId);
        return $user ? $user['EMAIL'] : null;
    }

    public static function getUserLogin($userId = null) {
        $user = Utils::getUser($userId);
        return $user ? $user['LOGIN'] : null;
    }

    public static function getUserIdByEmail($email) {
        $filter = array('EMAIL' => $email);
        $user = \CUser::GetList($by = 'id', $order = 'desc', $filter)->Fetch();
        return $user ? $user['ID'] : null;
    }
}
