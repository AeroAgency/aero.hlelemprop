<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<? if (!check_bitrix_sessid()) return; ?>
<?
echo CAdminMessage::ShowNote("Модуль успешно удален из системы");
?>
