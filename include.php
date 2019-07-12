<?php

namespace Aero\Hlelemprop;

use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}


/**
 * Базовый каталог модуля
 */

use Bitrix\Main\Event;
const BASE_DIR = __DIR__;

if (\CModule::IncludeModule('highloadblock')) {
    $event = new Event('aero.hlelemprop', 'onModuleInclude');
    $event->send();
}