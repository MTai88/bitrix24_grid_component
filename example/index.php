<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("My list");
?>
<?php
$APPLICATION->IncludeComponent("mymodule:myelement.list", ".default", []);
?>
<?php require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');