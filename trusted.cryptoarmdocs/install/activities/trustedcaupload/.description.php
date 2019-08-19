<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arActivityDescription = array(
	"NAME" => GetMessage("BPAR_DESCR_NAME"),
	"DESCRIPTION" => GetMessage("BPAR_DESCR_DESCR"),
	"TYPE" => "activity",
	"CLASS" => "TrustedCAUpload",
	"JSCLASS" => "BizProcActivity",
	"CATEGORY" => array(
		"ID" => "document",
		'OWN_ID' => 'task',
	),
);