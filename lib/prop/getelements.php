<?

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Loader;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
Loader::includeModule("highloadblock");


$hlblockId = intval($_GET['hlblockId']);
$name = strval($_GET['name']);
$selected = strval($_GET['selected']);
$field = strval($_GET['field']);
$selector = str_replace("[", strval('\\['), $name);
$selector = '#' . str_replace("]", strval('\\]'), $selector);


$hlblock = HL\HighloadBlockTable::getById($hlblockId)->fetch();

$entity = HL\HighloadBlockTable::compileEntity($hlblock);
$entity_data_class = $entity->getDataClass();
$rsData = $entity_data_class::getList([
    "select" => [$field, "UF_XML_ID", "ID"],
    "order" => ["ID" => "ASC"],
    "filter" => ["!UF_XML_ID" => $selected]
]);
while ($arData = $rsData->Fetch()) {
    $ids[] = $arData["UF_XML_ID"];
    $elements[] = $arData[$field] . " [" . $arData["ID"] . "]";
}


$array = [
    "name" => $selector,
    "elements" => $elements,
    "ids" => $ids
];
print json_encode($array);