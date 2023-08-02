<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');

$cmpParams = [
	'SET_TITLE' => 'Y',
];

if ($_REQUEST['IFRAME'] == 'Y' && $_REQUEST['IFRAME_TYPE'] == 'SIDE_SLIDER')
{
	$APPLICATION->IncludeComponent(
		'bitrix:ui.sidepanel.wrapper',
		'',
		array(
			'POPUP_COMPONENT_NAME' => 'mymodule:myelement.filter.list',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' => $cmpParams
		)
	);
}
else
{
	$APPLICATION->IncludeComponent('mymodule:myelement.filter.list', '', $cmpParams);
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');