<?php
namespace TrustedNet\Docs;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Sale;

Loc::loadMessages(__FILE__);

Loader::includeModule("sale");

/**
 * Helper functions and event handlers for documents by order functionality.
 */
class DocumentsByOrder
{
    /**
     * Called when document by order gets signed and uploaded.
     *
     * @param Document &$doc
     * @param array $extra
     * @return void
     */
    public static function upload(&$doc, $extra)
    {
        $role = $extra["role"];
        $props = $doc->getProperties();
        $roleProp = $props->getPropByType("ROLES");
        if ($roleProp){
            if ($role == "CLIENT") {
                DocumentsByOrder::signedByClient($doc, $roleProp);
            }
            if ($role == "SELLER") {
                DocumentsByOrder::signedBySeller($doc, $roleProp);
            }
        }
    }

    /**
     * Called when the document gets signed by client.
     *
     * @param Document &$doc
     * @param Property &$roleProp
     * @return void
     */
    private function signedByClient(&$doc, &$roleProp)
    {
        $event= Option::get(TN_DOCS_MODULE_ID, "EVENT_SIGNED_BY_CLIENT", "");
        $waitForAllDocs = Option::get(TN_DOCS_MODULE_ID, "EVENT_SIGNED_BY_CLIENT_ALL_DOCS", "");
        if ($event) {
            $allDocsSigned = DocumentsByOrder::allDocsSigned($doc, "CLIENT");
            if (!$waitForAllDocs || ($waitForAllDocs && $allDocsSigned)) {
                DocumentsByOrder::changeOrderStatus($doc, $event);
            }
        }
        $rolePropValue = $roleProp->getValue();
        if ($rolePropValue == "NONE") {
            $roleProp->setValue("CLIENT");
        }
        if ($rolePropValue == "SELLER") {
            DocumentsByOrder::signedByBoth($doc, $roleProp);
        }
    }

    /**
     * Called when the document gets signed by seller.
     *
     * @param Document &$doc
     * @param Property &$roleProp
     * @return void
     */
    private function signedBySeller(&$doc, &$roleProp)
    {
        $event= Option::get(TN_DOCS_MODULE_ID, "EVENT_SIGNED_BY_SELLER", "");
        $waitForAllDocs = Option::get(TN_DOCS_MODULE_ID, "EVENT_SIGNED_BY_SELLER_ALL_DOCS", "");
        if ($event) {
            $allDocsSigned = DocumentsByOrder::allDocsSigned($doc, "SELLER");
            if (!$waitForAllDocs || ($waitForAllDocs && $allDocsSigned)) {
                DocumentsByOrder::changeOrderStatus($doc, $event);
            }
        }
        $rolePropValue = $roleProp->getValue();
        if ($rolePropValue == "NONE") {
            $roleProp->setValue("SELLER");
        }
        if ($rolePropValue == "CLIENT") {
            DocumentsByOrder::signedByBoth($doc, $roleProp);
        }
    }

    /**
     * Called when the document gets signed by both client and seller.
     *
     * @param Document &$doc
     * @param Property &$roleProp
     * @return void
     */
    private function signedByBoth(&$doc, &$roleProp)
    {
        $event= Option::get(TN_DOCS_MODULE_ID, "EVENT_SIGNED_BY_BOTH", "");
        $waitForAllDocs = Option::get(TN_DOCS_MODULE_ID, "EVENT_SIGNED_BY_BOTH_ALL_DOCS", "");
        if ($event) {
            $allDocsSigned = DocumentsByOrder::allDocsSigned($doc, "BOTH");
            if (!$waitForAllDocs || ($waitForAllDocs && $allDocsSigned)) {
                DocumentsByOrder::changeOrderStatus($doc, $event);
            }
        }
        $roleProp->setValue("BOTH");
    }

    /**
     * Changes state of order associated with the document.
     *
     * @param Document $doc
     * @param string $status
     * @return void
     */
    public static function changeOrderStatus($doc, $status)
    {
        $props = $doc->getProperties();
        $orderId = $props->getPropByType("ORDER")->getValue();
        $order = Sale\Order::load($orderId);
        if ($order) {
            $order->setField("STATUS_ID", $status);
            $order->save();
        }
    }

    /**
     * Receives document, finds associated order and checks if all
     * documents in that order were signed by particular role.
     *
     * @param Document $doc
     * @param Property $docRole
     * @return boolean
     */
    private function allDocsSigned($doc, $docRole)
    {
        $res = true;
        $orderId = $doc->getProperties()->getPropByType("ORDER")->getValue();
        $orderDocs = Database::getDocumentsByOrder($orderId);
        foreach ($orderDocs->getList() as $orderDoc) {
            // Don't check the doc against itself
            if ($doc->getParentId() !== $orderDoc->getId()) {
                $orderDocRole = $orderDoc->getProperties()->getPropByType("ROLES")->getValue();
                if ($docRole == "BOTH") {
                    if ($orderDocRole !== "BOTH") {
                        $res = false;
                        break;
                    }
                } else {
                    if ($docRole !== $orderDocRole && $orderDocRole !== "BOTH") {
                        $res = false;
                        break;
                    }
                }
            }
        }
        return $res;
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
        $status = $doc->getProperties()->getPropByType("ROLES");
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
        $state = $doc->getProperties()->getPropByType("ROLES");
        $str = "";
        if ($state) {
            $state_value = $state->getValue();
            switch ($state_value) {
                case "CLIENT":
                    $str = Loc::getMessage("TN_DOCS_ROLES_CLIENT");
                    break;
                case "SELLER":
                    $str = Loc::getMessage("TN_DOCS_ROLES_SELLER");
                    break;
                case "BOTH":
                    $str = Loc::getMessage("TN_DOCS_ROLES_BOTH");
                    break;
                case "NONE":
                    $str = Loc::getMessage("TN_DOCS_ROLES_NONE");
                    break;
                default:
            }
        } else {
            $str = Loc::getMessage("TN_DOCS_ROLES_NONE");
        }
        return $str;
    }

}

