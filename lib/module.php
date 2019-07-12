<?php
/**
 * Aero.main module
 *
 * @category    Aeroidea
 * @link        http://aeroidea.ru
 * @revision    $Revision$
 * @date        $Date$
 */

namespace Aero\Hlelemprop;

use \Bitrix\Main\EventManager;
use \Aero\Hlelemprop;

/**
 * Основной класс модуля
 */
class Module
{
    /**
     * Название модуля
     */
    const MODULE_ID = 'aero.hlelemprop';

    public static function onPageStart()
    {
        self::setupEventHandlers();
    }

    protected static function setupEventHandlers()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList',
            ['\Aero\Hlelemprop\Prop\PropertyHLblockElement', 'OnIBlockPropertyBuildList']);
    }
}