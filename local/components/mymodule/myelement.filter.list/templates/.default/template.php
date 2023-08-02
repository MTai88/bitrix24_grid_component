<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;

Extension::load(['ui.tooltip', 'ui.label', 'ui.dialogs.messagebox']);

Toolbar::addFilter([
    'FILTER_ID' => $arResult['GridId'],
    'GRID_ID' => $arResult['GridId'],
    'FILTER' => $arResult['GridFilter'],
    'ENABLE_LIVE_SEARCH' => true,
    'ENABLE_LABEL' => true
]);

$addButton = \Bitrix\UI\Buttons\CreateButton::create([
    'text' => Loc::getMessage("MTH_BTN_ADD"),
    'color' => \Bitrix\UI\Buttons\Color::PRIMARY,
    'dataset' => [
        'toolbar-collapsed-icon' => \Bitrix\UI\Buttons\Icon::ADD,
    ],
    'click' => new \Bitrix\UI\Buttons\JsCode(
        "alert('Clicked');",
    ),
    'link' => '/myelement/add/',
]);

\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($addButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_FILTER);

$formatAuthorByCell = function($row)
{
	$format = \CSite::getNameFormat();
	$name = \CUser::FormatName($format, [
		'NAME' => $row['CREATED_BY_NAME'],
		'SECOND_NAME' => $row['CREATED_BY_SECOND_NAME'],
		'LAST_NAME' => $row['CREATED_BY_LAST_NAME'],
		'LOGIN' => $row['CREATED_BY_LOGIN'],
	],
		false,
		false
	);
	$url = "/company/personal/user/{$row['CREATED_BY']}/";

	return sprintf(
		'<a href="%s" bx-tooltip-user-id="%s" bx-tooltip-classname="intrantet-user-selector-tooltip">%s</a>',
		$url,
		$row['CREATED_BY'],
		htmlspecialcharsbx($name)
	);
};

$formatStatusCell = function ($row)
{
	$status = \MyModule\Entity\MyElement\Status::getById($row['STATUS_VALUE']);
	$color = 'ui-label-warning';
	if ($status['XML_ID'] == \MyModule\Entity\MyElement\Status::COMPLETED)
	{
		$color = 'ui-label-success';
	}

	if (
        $status['XML_ID'] == \MyModule\Entity\MyElement\Status::TERMINATED
		||
        $status['XML_ID'] == \MyModule\Entity\MyElement\Status::FAULT
	)
	{
		$color = 'ui-label-danger';
	}

	return sprintf(
		'<div class="ui-label ui-label-fill %s"><span class="ui-label-inner">%s</span></div>',
		$color,
		htmlspecialcharsbx($status['VALUE'])
	);
};

foreach ($arResult['GridRows'] as $index => $gridRow)
{
	$arResult['GridRows'][$index]['data']['CREATED_BY'] = $formatAuthorByCell($gridRow['data']);
	//$arResult['GridRows'][$index]['data']['STATUS_VALUE'] = $formatStatusCell($gridRow['data']);
}

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => $arResult['GridId'],
		'COLUMNS' => $arResult['GridColumns'],
		'ROWS' => $arResult['GridRows'],
		'SHOW_ROW_CHECKBOXES' => false,
		'NAV_OBJECT' => $arResult['PageNavigation'],
		'AJAX_MODE' => 'Y',
		'AJAX_ID' => \CAjax::getComponentID('mymodule:myelement.filter.list', '.default', ''),
		'PAGE_SIZES' => $arResult['PageSizes'],
		'AJAX_OPTION_JUMP' => 'N',
		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		'TOTAL_ROWS_COUNT' => $arResult['PageNavigation']->getRecordCount(),
		'SHOW_PAGESIZE' => true,
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_SORT' => true,
		'ALLOW_PIN_HEADER' => true,
		'AJAX_OPTION_HISTORY' => 'N'
	]
);
?>
<script>
	BX.ready(function(){
		const messages = <?=Json::encode(Loc::loadLanguageFile(__FILE__))?>;
        const gridId = '<?=CUtil::JSEscape($arResult['GridId'])?>';

		BX.message(messages);
        BX.MyElementList.Instance = new BX.MyElementList({gridId: gridId});
	});
</script>
