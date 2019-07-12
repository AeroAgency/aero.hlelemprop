<?

use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
Loader::includeModule("highloadblock");

$hlblockId = intval($_GET['hlblockId']);
$name = strval($_GET['name']);
$selector = str_replace("[", strval('\\['), $name);
$selector = '#' . str_replace("]", strval('\\]'), $selector);

$rsData = \CUserTypeEntity::GetList([], ["ENTITY_ID" => "HLBLOCK_" . $hlblockId]);
while ($arData = $rsData->Fetch()) {
    $ids[] = $arData["ID"];
    $elements[] = $arData["FIELD_NAME"];
}


$array = [
    "name" => $selector,
    "elements" => $elements,
    "ids" => $ids
];
print json_encode($array);