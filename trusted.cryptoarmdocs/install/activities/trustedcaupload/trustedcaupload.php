<?
use Trusted\CryptoARM\Docs;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!Loader::includeModule('bizproc')) {
    return;
}

// Prevent recursive module include
global $TR_CA_DOCS_MODULE_IS_LOADING;
if (!$TR_CA_DOCS_MODULE_IS_LOADING) {
    Loader::includeModule('trusted.cryptoarmdocs');
}

if (class_exists("CBPTrustedCAUpload")) {
    return;
}

class CBPTrustedCAUpload
	extends CBPActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	private $taskId = 0;
    private $taskStatus = false;

	private $isInEventActivityMode = false;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Name" => null,
			'docId' => '',
			'Responsible' => null,
		);

		$this->SetPropertiesTypes(
			array(
				'docId' => array(
					'Type' => 'string,'
				),
				'Responsible' => array (
					'Type' => 'user',
				)
			)
		);
	}

	public function Execute()
    {
        if ($this->isInEventActivityMode)
			return CBPActivityExecutionStatus::Closed;

        $this->Subscribe($this);

        $this->isInEventActivityMode = false;
        return CBPActivityExecutionStatus::Executing;
    }

	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
    {
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$this->isInEventActivityMode = true;

		$rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->GetDocumentId();
        $runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");

		$arUsersTmp = $this->Responsible;
        if (!is_array($arUsersTmp)) {
            $arUsersTmp = array($arUsersTmp);
        }

		$arUsers = CBPHelper::ExtractUsers($arUsersTmp, $documentId, false);
		$arParameters = array(
            'DOCUMENT_ID' => $documentId,
        );

		 /** @var CBPTaskService $taskService */
		 $taskService = $this->workflow->GetService('TaskService');
		 $this->taskId = $taskService->CreateTask(
			 array(
				 'USERS' => $arUsers,
				 'WORKFLOW_ID' => $this->GetWorkflowInstanceId(),
				 'ACTIVITY' => 'TrustedCAUpload',
				 'ACTIVITY_NAME' => $this->name,
				 'NAME' => $this->Name,
				 'PARAMETERS' => $arParameters,
				 'IS_INLINE' => 'N',
				 'DOCUMENT_NAME' => $documentService->GetDocumentName($documentId)
			 )
		 );

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}

	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
    {
        if ($eventHandler == null)
            throw new Exception("eventHandler");

        /** @var CBPTaskService $taskService */
        $taskService = $this->workflow->GetService('TaskService');

        if ($this->taskStatus === false)
        {
            $taskService->DeleteTask($this->taskId);
        }
        else
        {
            $taskService->Update($this->taskId, array(
                'STATUS' => $this->taskStatus
            ));
        }

        $this->workflow->RemoveEventHandler($this->name, $eventHandler);

        $this->taskId = 0;
		$this->taskStatus = false;
    }

	public function OnExternalEvent($arEventParameters = array())
    {

        if ($this->executionStatus == CBPActivityExecutionStatus::Closed) {
            return;
        }

        if (!array_key_exists('USER_ID', $arEventParameters) || intval($arEventParameters['USER_ID']) <= 0) {
            return;
        }

        if (empty($arEventParameters['REAL_USER_ID'])) {
            $arEventParameters["REAL_USER_ID"] = $arEventParameters["USER_ID"];
        }

        $arUsers = CBPHelper::ExtractUsers($this->Responsible, $this->GetDocumentId(), false);

        $arEventParameters['USER_ID'] = intval($arEventParameters['USER_ID']);
        $arEventParameters['REAL_USER_ID'] = intval($arEventParameters['REAL_USER_ID']);
        if (!in_array($arEventParameters["USER_ID"], $arUsers)) {
            return;
		}

		CBPActivity::SetVariable($this->arProperties['docId'], $arEventParameters['DOC_ID']);

		$taskService = $this->workflow->GetService('TaskService');
		$taskService->MarkCompleted($this->taskId, $arEventParameters['REAL_USER_ID'], CBPTaskUserStatus::Ok);

		$this->WriteToTrackingService(Loc::getMessage('FINISHED', array('#ACTIVITY#' => $this->Name)));

		$this->taskStatus = CBPTaskStatus::CompleteOk;
		$this->Unsubscribe($this);
		$this->workflow->CloseActivity($this);

	}

	public static function ShowTaskForm($arTask, $userId, $userName = "")
    {
		$maxSize  = Docs\Utils::maxUploadFileSize();
		echo '<script>';
		echo 'trustedBPcheckFile = function(file, maxSize) {';
		echo '	onFailure = () => {';
		echo '		$("#trca-BP-uploadFile-input").val(null);';
		echo '		$("#trca-BP-uploadFile").attr("class","ui-btn");';
		echo ' 		$("#trca-BP-uploadFile").prop("disabled", true);';
		echo '		$("#trca-BP-uploadFile-addFile")[0].lastChild.nodeValue = "' . GetMessage('BPAA_ACT_ADD_FILE') . '";';
		echo ' 	};';
		echo ' onSuccess = () => {trustedCA.checkAccessFile(file, trustedBPremakeSign(file), onFailure)};';
		echo ' trustedCA.checkFileSize(file, maxSize, onSuccess, onFailure );';
		echo ' };';
		echo 'trustedBPremakeSign = function(file) {';
		echo '	$("#trca-BP-uploadFile").attr("class","ui-btn ui-btn-success");';
		echo '	$("#trca-BP-uploadFile").prop("disabled", false);';
		echo '	$("#trca-BP-uploadFile-addFile")[0].lastChild.nodeValue = file.name;';
		echo '};';
		echo '</script>';

		$form = '<tr><td colspan="2">';
		$form .= '<div class="ui-btn ui-btn-primary" id="trca-BP-uploadFile-addFile" style="border: none; height: 34px; line-height: 32px;">';
		$form .= '<input id="trca-BP-uploadFile-input" name="tr_ca_bp_upload_file" type="file"';
		$form .= 'style="position: absolute; opacity: 0; height: 34px; width: 164px; left: 0; cursor: pointer;"';
		$form .= ' onchange="trustedBPcheckFile(this.files[0], ' . $maxSize . ')">';
		$form .= GetMessage('BPAA_ACT_ADD_FILE');
		$form .= '</div></td></tr>';

		$buttons = '<input class="ui-btn" type="submit"  id="trca-BP-uploadFile" style="border: none; height: 34px; line-height: 0;"';
		$buttons .= 'name="finish" value="' . GetMessage('BPAA_ACT_UPLOAD_FILE') . '" disabled>';

		return array($form, $buttons);
	}

	public static function PostTaskForm($arTask, $userId, $arRequest, &$arErrors, $userName = '', $realUserId = null)
    {

		global $USER;
		$arErrors = array();

		try {
			if (!Docs\Utils::checkAuthorization()) {
				return;
			}

			$DOCUMENTS_DIR = Option::get(TR_CA_DOCS_MODULE_ID, 'DOCUMENTS_DIR', '/docs/');

			foreach ($_FILES as $key => $value) {
				$fileName = $_FILES[$key]["name"];
				if ($fileName) {
					$uniqid = (string)uniqid();
					$newDocDir = $_SERVER['DOCUMENT_ROOT'] . '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/';
					mkdir($newDocDir);

					$newDocFilename = Docs\Utils::mb_basename($fileName);
					$absolutePath = $newDocDir . $newDocFilename;
					$relativePath = '/' . $DOCUMENTS_DIR . '/' . $uniqid . '/' . $newDocFilename;

					if (move_uploaded_file($_FILES[$key]["tmp_name"], $absolutePath)) {
						$props = new Docs\PropertyCollection();
						$props->add(new Docs\Property("USER", (string)$USER->GetID()));

						$doc = Docs\Utils::createDocument($relativePath, $props);
						$fileId = $doc->getId();
					}
				}
			}

			if (!$fileId) {
				$arErrors[] = array(
					'code' => 0,
					'message' => GetMessage('BPAA_ACT_UPLOAD_ERROR'),
				);
				return false;
			} else {
				$arEventParameters = array(
					"USER_ID" => $userId,
					"REAL_USER_ID" => $realUserId,
					"USER_NAME" => $userName,
					"DOC_ID" => $fileId,
				);

				CBPRuntime::SendExternalEvent($arTask["WORKFLOW_ID"], $arTask["ACTIVITY_NAME"], $arEventParameters);

				return true;
			}
		} catch (Exception $e) {
            $arErrors[] = array(
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'file' => $e->getFile().' ['.$e->getLine().']',
            );
        }

        return false;

	}

	public function HandleFault(Exception $exception)
    {
        if ($exception == null)
            throw new Exception("exception");

        $status = $this->Cancel();
        if ($status == CBPActivityExecutionStatus::Canceling)
            return CBPActivityExecutionStatus::Faulting;

        return $status;
    }

    public function Cancel()
    {
        if (!$this->isInEventActivityMode && $this->taskId > 0)
            $this->Unsubscribe($this);

        return CBPActivityExecutionStatus::Closed;
    }


	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		if (! is_array($arCurrentValues)) {
			$arCurrentValues = array(
				'docId' => '',
				'Responsible' => '',
				'Name' => '',
			);

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName(
				$arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity['Properties'])) {
				$arCurrentValues = array_merge($arCurrentValues,$arCurrentActivity['Properties']);
				$arCurrentValues['Responsible'] = CBPHelper::UsersArrayToString(
                    $arCurrentValues['Responsible'],
                    $arWorkflowTemplate,
					$documentType
				);
			}
		}

		$runtime = CBPRuntime::GetRuntime();
		$documentService = $runtime->GetService("DocumentService");
		$arDocumentFields = $documentService->GetDocumentFields($documentType);

		return $runtime->ExecuteResourceFile(
            __FILE__,
            "properties_dialog.php",
            array(
                "arCurrentValues" => $arCurrentValues,
                "arDocumentFields" => $arDocumentFields,
                "formName" => $formName,
            )
		);
	}

	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
    {
		$arErrors = array();

		$deleteInVrible = array('{=Variable:','}');
		$varible = str_replace($deleteInVrible, '', $arCurrentValues['docId']);
		if (array_key_exists($varible , $arWorkflowVariables)) {
			$varibleID = $varible;
		}

		$arProperties = array(
			"docId" => $varibleID,
			'Responsible' => CBPHelper::UsersStringToArray(
                $arCurrentValues['Responsible'],
                $documentType,
                $arErrors
            ),
			"Name" => $arCurrentValues["Name"],
		);

		$arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

        return true;
    }

	public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
    {
        $arErrors = array();
		if (empty($arTestProperties['Name'])){
			$arErrors[] = array(
				'code' => 'NotExist',
				'message' => GetMessage("BPAR_ACT_PROP_EMPTY3"),
			);
		}
		if (empty($arTestProperties['Responsible'])){
			$arErrors[] = array(
				'code' => 'NotExist',
				'message' => GetMessage("BPAR_ACT_PROP_EMPTY2"),
			);
		}

		if (empty($arTestProperties['docId'])){
			$arErrors[] = array(
				'code' => 'NotExist',
				'message' => GetMessage("BPAR_ACT_PROP_EMPTY1"),
			);
		}

        return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
    }

}
