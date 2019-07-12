<?

/**
 * Aero module
 *
 * @category    Aeroidea
 * @link        http://aero.ru
 */
Class aero_hlelemprop extends CModule
{
    /**
     * ID модуля
     * @var string
     */
    public $MODULE_ID = 'aero.hlelemprop';

    /**
     * Версия модуля
     * @var string
     */
    public $MODULE_VERSION = '';

    /**
     * Дата выхода версии
     * @var string
     */
    public $MODULE_VERSION_DATE = '';

    /**
     * Название модуля
     * @var string
     */
    public $MODULE_NAME = 'Пользоватеский тип свойства "Привязка к элементу справочника"';

    /**
     * Описание модуля
     * @var string
     */
    public $MODULE_DESCRIPTION = 'Пользоватеский тип свойства "Привязка к элементу справочника"';

    /**
     * Имя партнера
     * @var string
     */
    public $PARTNER_NAME = "AERO";

    /**
     * Ссылка на сайт партнера
     * @var string
     */
    public $PARTNER_URI = "http://aeroidea.ru";

    /**
     * Обработчики событий
     * @var array
     */
    public $eventHandlers = [];

    /**
     * Конструктор модуля
     */
    public function __construct()
    {
        $version = include __DIR__ . '/version.php';

        $this->MODULE_VERSION = $version['VERSION'];
        $this->MODULE_VERSION_DATE = $version['VERSION_DATE'];

        $this->eventHandlers = [
            [
                'main',
                'OnPageStart',
                '\Aero\Hlelemprop\Module',
                'onPageStart',
            ]
        ];
    }

    /**
     * Устанавливает модуль
     *
     * @return void
     */
    public function DoInstall()
    {
        if ($this->installEvents()) {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        }
    }

    /**
     * Устанавливает события модуля
     *
     * @return boolean
     */
    public function installEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        foreach ($this->eventHandlers as $handler) {
            $eventManager->registerEventHandler($handler[0], $handler[1], $this->MODULE_ID, $handler[2], $handler[3]);
        }

        return true;
    }

    /**
     * Удаляет модуль
     *
     * @return void
     */
    public function DoUninstall()
    {
        if ($this->unInstallEvents()) {
            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        }
    }

    /**
     * Удаляет события модуля
     *
     * @return boolean
     */
    public function unInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();

        foreach ($this->eventHandlers as $handler) {
            $eventManager->unRegisterEventHandler($handler[0], $handler[1], $this->MODULE_ID, $handler[2], $handler[3]);
        }

        return true;
    }
}