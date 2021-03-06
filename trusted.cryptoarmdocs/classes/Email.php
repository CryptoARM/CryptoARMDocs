<?php

namespace Trusted\CryptoARM\Docs;
use Bitrix\Main\Config\Option;

class Email {
    /**
     * @param $docsList         array of documents id
     * @param $event            identifier of the mail event
     * @param $arEventFields    mail event settings
     * @param $message_id       identifier of the mail template
     * @return array            [success]: operation result status
     *                          [message]: operation result message
     */
    public static function sendEmail($docsList, $event, $arEventFields, $message_id) {
         $res = array(
            "success" => false,
            "message" => "Unknown error in Email.sendEmail",
        );

        // Only for email by order
        $eventEmailSent = Option::get(TR_CA_DOCS_MODULE_ID, "EVENT_EMAIL_SENT", "");

        $MAIL_EVENT_ID = Option::get(TR_CA_DOCS_MODULE_ID, $event, "");
        $MAIL_TEMPLATE_ID = Option::get(TR_CA_DOCS_MODULE_ID, $message_id, "");

        if (!$MAIL_EVENT_ID || !$MAIL_TEMPLATE_ID) {
            return $res = array(
                "success" => false,
                "message" => "Mail template not configured",
            );
        }

        $docs = array();
        $docLinks = array();
        $docNames = array();

        foreach ($docsList as $docId) {
            $doc = Database::getDocumentById($docId);

            if ($doc->getSignType() === DOC_SIGN_TYPE_DETACHED) {
                $originalDoc = Database::getDocumentById($doc->getOriginalId());
                $originalDocUrl = $_SERVER['DOCUMENT_ROOT'] . $originalDoc->getHtmlPath();
                $signUrl = $_SERVER['DOCUMENT_ROOT'] . $doc->getHtmlPath();
                $docLinks[] = urldecode($signUrl);

            } else {
                $originalDocUrl = $_SERVER['DOCUMENT_ROOT'] . $doc->getHtmlPath();
                $signUrl = null;
            }
            $docLinks[] = urldecode($originalDocUrl);
            $docs[] = $doc;
            $docNames[] = $doc->getName();
        }

        // Add default fields
        if (!$arEventFields) {
            $arEventFields = array();
        }
        $arEventFields = array_merge(
            array(
                'SITE_URL' => TR_CA_HOST,
                'FILE_NAMES' => implode(", ", $docNames),
                'RAND_UID' => Utils::generateUUID(),
            ),
            $arEventFields
        );

        $sites = \CSite::GetList($by = "sort", $order = "asc", array("ACTIVE" => "Y"));
        $siteIds = array();
        while ($site = $sites->Fetch()) {
            $siteIds[] = $site["ID"];
        }

        if (\CEvent::Send($MAIL_EVENT_ID, $siteIds, $arEventFields, "N", $MAIL_TEMPLATE_ID, $docLinks)) {

            // Documents by order can change order status
            if ($MAIL_TEMPLATE_ID == Option::get(TR_CA_DOCS_MODULE_ID, "MAIL_TEMPLATE_ID", "")) {
                foreach ($docs as $doc) {
                    if ($eventEmailSent) {
                        DocumentsByOrder::changeOrderStatus($doc, $eventEmailSent);
                    }
                    // Add email tracking property
                    $docProps = $doc->getProperties();
                    if ($emailProp = $docProps->getPropByType("EMAIL")) {
                        $emailProp->setValue("SENT");
                    } else {
                        $docProps->add(new Property("EMAIL", "SENT"));
                    }
                    $doc->save();
                }
            }

            if ($MAIL_TEMPLATE_ID == Option::get(TR_CA_DOCS_MODULE_ID, "MAIL_TEMPLATE_ID_REQUIRED_SIGN", "")) {
                foreach ($docs as $doc) {
                    // Add email tracking property
                    $require = new RequireSign();
                    $require->setDocId($doc->getId());
                    $require->setUserId($arEventFields["USER_ID"]);
                    $require->setEmailStatus("SENT");
                    $require->setSignStatus(DOC_TYPE_FILE);
                    $require->setSignUUID($arEventFields["TRANSACTION_UUID"]);
                    $require->save();
                }
            }

            $res = array(
                "success" => true,
                "message" => "OK",
            );
        } else {
            $res['message'] = "Error in CEvent::Send";
        }

        return $res;
    }
}
