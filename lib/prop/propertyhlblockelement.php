<?php

namespace Aero\Hlelemprop\Prop;

use Bitrix\Highloadblock as HL;
use Bitrix\Highloadblock\HighloadBlockTable;


class PropertyHLblockElement
{
    const AJAX_PATH_ELEMENTS_LIST = "/getelements.php";

    const AJAX_PATH_FIELDS_LIST = "/getfields.php";

    public function OnIBlockPropertyBuildList()
    {
        \CJSCore::Init(["jquery"]);

        return [
            "PROPERTY_TYPE" => "S", // тип поля
            "USER_TYPE" => "iblock_hlelem_customfield", // код
            "DESCRIPTION" => "Привязка к элементу справочника", // название
            "GetPropertyFieldHtml" => [__CLASS__, 'GetPropertyFieldHtml'],
            "GetAdminListViewHTML" => [__CLASS__, 'GetAdminListViewHTML'],
            "GetSettingsHTML" => [__CLASS__, 'GetSettingsHTML'],
            "ConvertToDB" => [__CLASS__, "ConvertToDB"],
            "CheckFields" => [__CLASS__, "CheckFields"],
            "PrepareSettings" => [__CLASS__, "PrepareSettings"]
        ];
    }

    /**
     * Получаем настройки
     * @param $arUserField
     * @return array
     */
    public function PrepareSettings($arUserField)
    {
        $hlIblockId = intval($arUserField["USER_TYPE_SETTINGS"]["HL_IBLOCK_ID"]);
        if ($hlIblockId <= 0) {
            $hlIblockId = "";
        }
        $field = intval($arUserField["USER_TYPE_SETTINGS"]["FIELD"]);
        if (!$field) {
            $field = "";
        }

        return [
            "HL_IBLOCK_ID" => $hlIblockId,
            'TABLE_NAME' => call_user_func(function () use ($hlIblockId) {
                if ($hlIblockId == null) return '';

                $hlBlockData = HighloadBlockTable::getList(
                    [
                        'select' => ['TABLE_NAME', 'NAME'],
                        'filter' => ['=ID' => $hlIblockId],
                        'limit' => 1
                    ])->fetch();

                return $hlBlockData['TABLE_NAME'];
            }),
            "FIELD" => $field
        ];
    }

    /**
     * Сохраняем поле
     * @param $arProperty
     * @param $value
     * @return mixed
     */
    public function ConvertToDB($arProperty, $value)
    {
        if (!array_key_exists('VALUE', $value)) {
            foreach ($value as &$valueItem) {
                $valueItem = $valueItem['VALUE'];
            }
            unset($valueItem);
        }
        return $value;
    }

    /**
     * Проверка перед сохранением
     * @param $arProperty
     * @param $value
     * @return array
     */
    public function CheckFields($arProperty, $value)
    {
        $arResult = [];
        return $arResult;
    }

    /**
     * Возвращаем HTML выбора элемента
     * @param $arProperty
     * @param $value
     * @param $strHTMLControlName
     * @return string
     */
    public function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $form = "";
        $form .= '
                <tr>
                    <td>
                        ' . self::getElementsListHtml($arProperty["USER_TYPE_SETTINGS"]["HL_IBLOCK_ID"],
                $arProperty["USER_TYPE_SETTINGS"]["FIELD"], $strHTMLControlName["VALUE"], $value["VALUE"]) . '
                    </td>
                ';

        return $form;
    }

    /**
     * HTML списка элементов справочника
     * @param $hlblockId
     * @param $name
     * @param $xmlId
     * @return string
     */
    protected function getElementsListHtml($hlblockId, $fieldId, $name, $xmlId)
    {
        $name = self::normalizeFieldName($name);
        $pathToAjax = self::getPathToAjax() . self::AJAX_PATH_ELEMENTS_LIST;
        $listHtml = "";
        if ($hlblockId) {
            //поле справочника для вывода
            $rsData = \CUserTypeEntity::GetList([], ["ID" => $fieldId]);
            if ($hlElem = $rsData->fetch()) {
                $fieldToShow = $hlElem["FIELD_NAME"];
            }

            //
            $hlblock = HL\HighloadBlockTable::getById($hlblockId)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();

            $select = [];
            if($fieldToShow) {
                $select[] = $fieldToShow;
            }
            $select[] = "UF_XML_ID";
            $select[] = "ID";
            $rsData = $entity_data_class::getList([
                "select" => $select,
                "order" => ["ID" => "ASC"],
                "filter" => ["UF_XML_ID" => $xmlId]
            ]);
            $visibility = $xmlId ? "" : "hidden";
            $listHtml = '';
            $listHtml .= '<select ' . $visibility . ' id="' . $name . '" name="' . $name . '">';
            while ($hlElem = $rsData->fetch()) {
                $listHtml .= '<option value="' . $hlElem['UF_XML_ID'] . '"';
                if ($xmlId == $hlElem['UF_XML_ID']) {
                    $listHtml .= ' selected="selected"';
                }
                $str = $hlElem[$fieldToShow] . " [" . $hlElem["ID"] . "]";
                $listHtml .= '>' . $str . '</option>';
            }
            $listHtml .= '</select>';
            $listHtml .= '<input type="button" onclick="update(this)" hlblockId="' . $hlblockId . '" tag="' . $name . '"
                            fieldCode="' . $fieldToShow . '"  
                            value="Выбрать элемент справочника">';
        } else {
            $listHtml .= '<input type="text" tag="' . $name . '" value="Не выбран справочник">';
        }
        $listHtml .= '
            <script>
            function sortSelectOptions(selectElement, name) {
	var options = $(selectElement + " option");
	var selectedVal = $(selectElement).find(":selected").text();
	options.sort(function(a,b) {
		if (a.text.toUpperCase() > b.text.toUpperCase()) return 1;
		else if (a.text.toUpperCase() < b.text.toUpperCase()) return -1;
		else return 0;
	});
	console.log([options, selectedVal]);

	$(selectElement).empty().append( options );
	$(selectElement).prepend($(\'<option>\').text("не установлено").attr(\'value\', "").attr(\'name\', name));
	$(selectElement).find("option:contains(\'"+selectedVal+"\')").attr("selected","selected");
}
            function update(selectObject) {
                var name = $(selectObject).attr(\'tag\');
                var hlblockId = $(selectObject).attr(\'hlblockId\');
                var fieldToShow = $(selectObject).attr(\'fieldCode\');
    BX.ajax({   
                url: "' . $pathToAjax . '?hlblockId=" + hlblockId + "&name=" + name + "&selected=' . $xmlId . '&field=" + fieldToShow,
                data: { 
                    "hlblockId" : "' . strval($hlblockId) . '",
                    "name" : name,
                    "selected" : "' . $xmlId . '",
                    "field" : fieldToShow
                }, 
                method: "GET",
                dataType: "json",
                onsuccess: function(data){
                    $(data.name).show();
                    $(data.name).children().not("[selected]").remove(); 
                    $.each(data.elements, function(i, value) {
                        if (data.ids[i] != $(data.name).find(":selected").val()) {
                        var val = data.ids[i];
            $(data.name).append($(\'<option>\').text(value).attr(\'value\', val).attr(\'name\', name));
            }
        });
            sortSelectOptions(data.name, name);},
                onfailure: function(){
                    console.log("fail"); 
                }
                
            });
            }</script>
            ';
        return $listHtml;
    }

    /**
     * Получаем путь до файла, который принимает запрос
     * @return string
     */
    protected function getPathToAjax()
    {
        $pathToAjax = str_replace($_SERVER['DOCUMENT_ROOT'], "", __DIR__);
        return $pathToAjax;
    }

    /**
     * Отображение в админразделе в списке объектов
     *
     * @param $arProperty           Описание типа свойства
     * @param $value                Значение свойства
     * @param $strHTMLControlName   UI элемент
     *
     * @return mixed|string
     * @throws \Indi\Main\Exception
     */
    public function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        $result = "";
        $hlblockId = $arProperty["USER_TYPE_SETTINGS"]["HL_IBLOCK_ID"];
        $fieldId = $arProperty["USER_TYPE_SETTINGS"]["FIELD"];
        $xmlId = $value["VALUE"];

        if ($hlblockId && $fieldId && $xmlId) {
            $rsData = \CUserTypeEntity::GetList([], ["ID" => $fieldId]);
            if ($hlElem = $rsData->fetch()) {
                $fieldToShow = $hlElem["FIELD_NAME"];
            }

            //
            $hlblock = HL\HighloadBlockTable::getById($hlblockId)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();

            $rsData = $entity_data_class::getList([
                "select" => [$fieldToShow, "UF_XML_ID", "ID"],
                "order" => ["ID" => "ASC"],
                "filter" => ["UF_XML_ID" => $xmlId]
            ]);
            if ($hlElem = $rsData->fetch()) {
                $result = $hlElem[$fieldToShow] . " [" . $hlElem["ID"] . "]";
            }
        }
        return $result;
    }

    /**
     * Возвращаем HTML настроек
     * @param bool $arUserField
     * @param $arHtmlControl
     * @param $bVarsFromForm
     * @return string
     */
    function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
    {
        $result = '';
        if ($bVarsFromForm) {
            $hlIblockId = $GLOBALS[$arHtmlControl["NAME"]]["HL_IBLOCK_ID"];
        } elseif (is_array($arUserField)) {
            $hlIblockId = $arUserField["USER_TYPE_SETTINGS"]["HL_IBLOCK_ID"];
        } else {
            $hlIblockId = "";
        }

        if ($bVarsFromForm) {
            $selectedField = $GLOBALS[$arHtmlControl["NAME"]]["FIELD"];
        } elseif (is_array($arUserField)) {
            $selectedField = $arUserField["USER_TYPE_SETTINGS"]["FIELD"];
        } else {
            $selectedField = "";
        }

        $result .= '
                <tr>
                    <td>Выберите справочник:</td>
                    <td>
                        ' . self::getHighloadListHtml($arHtmlControl["NAME"] . '[HL_IBLOCK_ID]', $hlIblockId,
                $arHtmlControl["NAME"] . '[FIELD]') . '
                    </td>
                </tr>
                <tr>
                    <td>Выберите поле, которое нужно выводить:</td>
                    <td>
                        ' . self::getFieldsListHtml($arHtmlControl["NAME"] . '[FIELD]', $hlIblockId, $selectedField) . '
                    </td>
                </tr>
                ';

        return $result;
    }

    /**
     * HTML списка справочников для настроек
     * @param $name
     * @param $selected_value
     * @return string
     */
    function getHighloadListHtml($name, $selected_value, $fieldsListName)
    {
        $name = self::normalizeFieldName($name);
        $listHtml = '<select onchange="update(this)" tag="' . $fieldsListName . '" name="' . $name . '">';
        $hllistObj = HighloadBlockTable::getList();
        while ($hlElem = $hllistObj->fetch()) {
            $listHtml .= '<option name="' . $name . '" value="' . $hlElem['ID'] . '"';
            if ($selected_value == $hlElem['ID']) {
                $listHtml .= ' selected="selected"';
            }
            $listHtml .= '>' . $hlElem['NAME'] . '</option>';
        }
        $listHtml .= '<select>';

        return $listHtml;
    }

    /**
     * HTML списка полей справочника
     * @param $name
     * @param $hlblockId
     * @param $fieldId
     * @return string
     */
    function getFieldsListHtml($name, $hlblockId, $fieldId)
    {
        $name = self::normalizeFieldName($name);
        $pathToAjax = self::getPathToAjax() . self::AJAX_PATH_FIELDS_LIST;
        $rsData = \CUserTypeEntity::GetList([], ["ENTITY_ID" => "HLBLOCK_" . $hlblockId]);

        $listHtml = '
            <script>
            function update(selectObject) {
                var name = $(selectObject).attr(\'tag\');
    BX.ajax({   
                url: "' . $pathToAjax . '?hlblockId="+selectObject.value+"&name="+name' . ',
                data: { 
                    "hlblockId" : "' . strval($hlblockId) . '",
                    "name" : name
                }, 
                method: "GET",
                dataType: "json",
                onsuccess: function(data){
                    $(data.name).empty(); 
                    $.each(data.elements, function(i, value) {
                        var val = data.ids[i];
                        $(data.name).show();
            $(data.name).append($(\'<option>\').text(value).attr(\'value\', val).attr(\'name\', name));
        });                    
                    },
                onfailure: function(){
                    console.log("fail"); 
                }
            });
    }</script>
            ';
        $listHtml .= '<select id="' . $name . '" name="' . $name . '">';
        while ($hlElem = $rsData->fetch()) {
            $listHtml .= '<option name="' . $name . '" value="' . $hlElem['ID'] . '"';
            if ($fieldId == $hlElem['ID']) {
                $listHtml .= ' selected="selected"';
            }
            $listHtml .= '>' . $hlElem['FIELD_NAME'] . '</option>';
        }
        $listHtml .= '<select>';

        return $listHtml;
    }

    /**
     * @param $name
     * @return string
     */
    protected static function normalizeFieldName($name): string
    {
        $normalizedName = $name ? str_replace(':', '_', $name) : '';

        return $normalizedName;
    }
}
