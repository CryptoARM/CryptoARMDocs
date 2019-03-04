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
		$eventEmailSent = Option::get("trusted.cryptoarmdocs", "EVENT_EMAIL_SENT", "");

		$MAIL_EVENT_ID = Option::get("trusted.cryptoarmdocs", $event, "");
		$MAIL_TEMPLATE_ID = Option::get("trusted.cryptoarmdocs", $message_id, "");

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
            $docLinks[] = urldecode($_SERVER['DOCUMENT_ROOT'] . $doc->getHtmlPath());
            $docs[] = $doc;
            $docNames[] = $doc->getName();
        }

        // Add default fields
        $arEventFields = array_merge(
            array(
                'SITE_URL' => TR_CA_HOST,
                'FILE_NAMES' => implode(", ", $docNames),
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
		    if ($MAIL_TEMPLATE_ID == Option::get("trusted.cryptoarmdocs", "MAIL_TEMPLATE_ID", "")) {
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