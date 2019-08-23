<?
use Trusted\CryptoARM\Docs;
use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

Loader::includeModule('trusted.cryptoarmdocs');

if (!Loader::includeModule('bizproc')) {
    return;
}

Loc::loadMessages(__FILE__);

if (class_exists("CBPTrustedCAShare")) {
    return;
}

class CBPTrustedCAShare
	extends CBPCompositeActivity

{
	public function __construct ($name) {

		parent::__construct($name);

		$this->arProperties = array(
			'Responsible' => '',
			'rDocID' => '',
		);
		$this->SetPropertiesTypes(array(
			'Responsible' => array('Type' => FieldType::USER),
			'rDocID' => array('Type' => FieldType::INT),
			));
	}

	public function Execute()
    {
		$rootActivity = $this->GetRootActivity();
		$arId=$rootActivity->GetDocumentId();

		if ($this->rDocID){
			$docId =$this->rDocID;
		} else {
			$docId=$arId[2];
		}

		$doc = Docs\Database::getDocumentById($docId)->getLastDocument();
		$access = $doc->accessCheck(Docs\Utils::currUserId());

		$arUsersTmp = $this->Responsible;
        if (!is_array($arUsersTmp)) {
            $arUsersTmp = array($arUsersTmp);
		}

		$userIds = CBPHelper::ExtractUsers($arUsersTmp, $arId, false);

		if ($access === true) {
			if (count($this->arActivities) <= 0) {
				$this->workflow->CloseActivity($this);
				return;
			}

			$activity = $this->arActivities[0];
			$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
			$this->workflow->ExecuteActivity($activity);

			foreach ($userIds as $value) {
				$doc->share($value, DOC_SHARE_SIGN);
				$doc->save();
			}
		}
		else {
			if (count($this->arActivities) <= 1) {
				$this->workflow->CloseActivity($this);
            	return;
        	}

			$activity = $this->arActivities[1];
			$activity->AddStatusChangeHandler(self::ClosedEvent, $this);
			$this->workflow->ExecuteActivity($activity);
		}

        return CBPActivityExecutionStatus::Executing;
	}

	protected function OnEvent(CBPActivity $sender) {
        $sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);
        $this->workflow->CloseActivity($this);
    }

	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		if (!is_array($arCurrentValues))
        {
            $arCurrentValues = array(
				'Responsible' => '',
				'rDocID' => '',
            );

            $arCurrentActivity= &CBPWorkflowTemplateLoader::FindActivityByName(
                $arWorkflowTemplate,
                $activityName
            );
            if (is_array($arCurrentActivity['Properties'])) {
                $arCurrentValues = array_merge($arCurrentValues, $arCurrentActivity['Properties']);
                $arCurrentValues['Responsible'] = CBPHelper::UsersArrayToString($arCurrentValues['Responsible'],$arWorkflowTemplate,$documentType);
			}
		}

		$runtime = CBPRuntime::GetRuntime();
        return $runtime->ExecuteResourceFile(
            __FILE__,
            'properties_dialog.php',
            array(
                'arCurrentValues' => $arCurrentValues,
                'formName' => $formName,
            )
        );
    }

    public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
    {
        $arErrors = array();

        if (empty($arCurrentValues['Responsible'])) {
            $arErrors[] = array(
                'code' => 'Empty',
                'message' => Loc::getMessage('ERROR_NO_RESPONSIBLE')
            );
        }

        if (!empty($arErrors)) {
            return false;
        }

        $arProperties = array(
			'Responsible' => CBPHelper::UsersStringToArray($arCurrentValues['Responsible'],$documentType,$arErrors),
			'rDocID' =>$arCurrentValues['rDocID'],
        );

        $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
            $arWorkflowTemplate,
            $activityName
        );
        $arCurrentActivity['Properties'] = $arProperties;

        return true;
    }
}
