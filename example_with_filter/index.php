<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("My list with filtration");
?>
<?php
$APPLICATION->IncludeComponent("mymodule:myelement.filter.list", ".default", []);
?>
<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');