<?php
error_reporting(-1);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');
require_once('classes/parser.php');
require_once('classes/db.php');

$db = new Db();
$parser = new Parser();
$base_path = './html_files/';
$arFiles = array_diff(scandir($base_path), array('.', '..'));
$arResult = [];
if ($arFiles) {
    foreach ($arFiles as $file) {
        $html = file_get_contents($base_path . $file);

        $arResult['SITES'] = $parser->ParseSite($html);
        // print_r($arResult['SITES']);
        $arResult['CATEGORIES_NAMES'] = $parser->ParseCategoriesNames($html);
        $arResult['CATEGORIES'] = $parser->ParseCategories($html);
        $arResult['DYNAMICS'] = $parser->ParseDynamicToDates($html);

        if ($arResult['SITES']) {
            echo 'l';
            $db->ManageSitesTable($arResult['SITES']);
        }
        if ($arResult['CATEGORIES_NAMES']) {
            $db->ManageCategoryNames($arResult['CATEGORIES_NAMES']);
        }
        if ($arResult['CATEGORIES']) {
            $db->ManageCategories($arResult['CATEGORIES']);
        }
        if ($arResult['DYNAMICS']) {
            $db->ManageDynamics($arResult['DYNAMICS']);
        }

       die;

    }

} else {
    echo 'Не найдено файлов для парсинга';
}


