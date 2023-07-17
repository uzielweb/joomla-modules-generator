<?php
// author: Uziel Almeida Oliveira
// date: 2023-07-01
// version: 1.0.0
// description: This file is the main file of the module generator.
// license: GPL
// license-url: https://www.gnu.org/licenses/gpl-3.0.html
// GitHub: https://github.com/uzielweb/joomla-modules-generator

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['module_title'])) {
    // Function to generate a sanitized module name
    function generateModuleNameAndHelperName($moduleTitle)
    {
        $moduleName = 'mod_' . strtolower($moduleTitle);
        $moduleName = sanitizeModuleName($moduleName);
        $helperName = removeUnderscoresAndMod($moduleName);
        $helperName = ucwords($helperName);
        $ModuleNameUpperCase = strtoupper($moduleName);
        return [$moduleName, $helperName, $ModuleNameUpperCase];
    }

// Function to sanitize the module name
    function sanitizeModuleName($moduleName)
    {
        $specialChars = ['-', ' ', 'á', 'à', 'ã', 'â', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ü', 'ç', 'Á', 'À', 'Ã', 'Â', 'É', 'Ê', 'Í', 'Ó', 'Ô', 'Õ', 'Ú', 'Ü', 'Ç'];
        $replaceChars = ['_', '_', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'u', 'c', 'A', 'A', 'A', 'A', 'E', 'E', 'I', 'O', 'O', 'O', 'U', 'U', 'C'];

        return str_replace($specialChars, $replaceChars, $moduleName);
    }

// Function to remove underscores and "mod" from the module name
    function removeUnderscoresAndMod($moduleName)
    {
        return str_replace(['_', 'mod'], ['', ''], $moduleName);
    }

    // Function to create the module directory structure
    function createModule($moduleName, $joomlaVersion)
    {
        $baseDir = 'temp';
        $moduleDir = "{$baseDir}/{$moduleName}";

        // Delete existing module directory if it exists
        deleteDirectory($baseDir);

        // Create the necessary directory structure for the module
        mkdir($baseDir, 0755, true);
        mkdir($moduleDir, 0755, true);
        mkdir("{$moduleDir}/tmpl", 0755, true);
        mkdir("{$moduleDir}/language/en-GB", 0755, true);
        mkdir("{$moduleDir}/language/pt-BR", 0755, true);

        if ($joomlaVersion == '3') {
            mkdir("{$moduleDir}/assets/css", 0755, true);
            mkdir("{$moduleDir}/assets/js", 0755, true);
        }

        if ($joomlaVersion == '4') {
            mkdir("{$moduleDir}/media", 0755, true);
            mkdir("{$moduleDir}/media/css", 0755, true);
            mkdir("{$moduleDir}/media/js", 0755, true);
            mkdir("{$moduleDir}/media/images", 0755, true);
            mkdir("{$moduleDir}/src/Helper", 0755, true);
        }
    }

// Function to recursively delete a directory and its contents
    function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $objects = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($objects as $object) {
            if (in_array($object->getBasename(), ['.', '..'])) {
                continue;
            }

            if ($object->isDir()) {
                rmdir($object->getPathname());
            } else {
                unlink($object->getPathname());
            }
        }

        rmdir($dir);
    }

// Assign the main values
    $moduleTitle = $_POST['module_title'];
    $joomlaVersion = $_POST['joomla_version'];
    $client = $_POST['client'];
    [$moduleName, $helperName, $moduleNameUpperCase] = generateModuleNameAndHelperName($moduleTitle);
// Assign values with htmlspecialchars() sanitization
    $moduleDescription = htmlspecialchars($_POST['module_description'], ENT_QUOTES);
    $moduleVersion = htmlspecialchars($_POST['module_version'], ENT_QUOTES);
    $moduleLicense = htmlspecialchars($_POST['module_license'], ENT_QUOTES);
    $moduleAuthor = htmlspecialchars($_POST['module_author'], ENT_QUOTES);
    $moduleAuthorEmail = htmlspecialchars($_POST['module_author_email'], ENT_QUOTES);
    $moduleAuthorUrl = htmlspecialchars($_POST['module_author_url'], ENT_QUOTES);
// Assign other values
    $moduleDate = date('Y-m-d');
    $moduleType = $moduleName;
    $moduleFolder = $moduleName;
    $joomlaVersion = $_POST['joomla_version'];

    createModule($moduleName, $joomlaVersion);
    $moduleClient = ucwords($client);
// \n/**\n * @package     Joomla.{$moduleClient}\n * @subpackage  {$moduleName}\n * Author: {$moduleAuthor}\n * Author Email: {$moduleAuthorEmail}\n * Author URL: {$moduleAuthorUrl}\n * License: {$moduleLicense}\n * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>\n * @license     {$moduleLicense}\n
    $phpHeaders = "\n/**\n * @package Joomla.{$moduleClient}\n * @subpackage {$moduleName}\n * Author: {$moduleAuthor}\n * Author Email: {$moduleAuthorEmail}\n * Author URL: {$moduleAuthorUrl}\n * License: {$moduleLicense}\n * @copyright (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>\n * @license {$moduleLicense}\n */";
    if ($joomlaVersion == '3') {
        $modulePHP = "<?php{$phpHeaders}\ndefined('_JEXEC') or die;\nuse Joomla\CMS\Factory;\nuse Joomla\CMS\Helper\ModuleHelper;\nrequire_once dirname(__FILE__) . '/helper.php';\n\$doc = Factory::getDocument();\n\$doc->addStyleSheet('modules/{$moduleName}/assets/css/style.css');\n\$doc->addScript('modules/{$moduleName}/assets/js/script.js');\n\$moduleclass_sfx = htmlspecialchars(\$params->get('moduleclass_sfx'), ENT_QUOTES);\nrequire ModuleHelper::getLayoutPath('{$moduleName}', \$params->get('layout', 'default'));\nif(\$params->get('backgroundimage')) {\n\$doc->addStyleDeclaration('\n#module-itemslist-id-'.\$module->id.' {\nbackground-image: url('.\$params->get('backgroundimage').');\n}');\n}\n?>";

        $helperPHP =
            "<?php{$phpHeaders}\ndefined('_JEXEC') or die;\nuse Joomla\CMS\Factory;\nclass {$helperName}Helper {\npublic static function getItems() {\n\$items = array();\nreturn \$items;\n/*\n\$db = Factory::getDbo();\n\$query = \$db->getQuery(true)\n->select('*')\n->from('#__{$moduleName}');\n\$db->setQuery(\$query);\n\$items = \$db->loadObjectList();\nreturn \$items;\n*/\n}\n}\n?>";

        $defaultTmplPHP =
            "<?php{$phpHeaders}\ndefined('_JEXEC') or die;\nuse Joomla\CMS\Factory;\nuse Joomla\CMS\HTML\HTMLHelper;\nuse Joomla\CMS\Language\Text;\n\$items = {$helperName}Helper::getItems(\$params) ? {$helperName}Helper::getItems(\$params) : '';\n?>\n
<div id=\"module-itemslist-id-<?php echo \$module->id; ?>\"
    class=\"{$moduleName}_itemslist<?php echo \$moduleclass_sfx; ?>\">\n<?php if (\$params->get('customtext', 0)) : ?>\n
    <div class=\"customtext\">\n<?php echo HTMLHelper::_('content.prepare', \$params->get('customtext')); ?>\n</div>
    \n<?php endif; ?>\n<?php if(\$items) : ?>\n<div class=\"items\">\n<?php foreach (\$items as \$item) : ?>\n<div
            class=\"item\">\n<h2><?php echo \$item->title; ?></h2>\n<p><?php echo \$item->text; ?></p>\n</div>
        \n<?php endforeach; ?>\n</div>\n<?php endif; ?>\n
</div>";

        file_put_contents('temp/' . $moduleName . '/assets/css/style.css', '');
        file_put_contents('temp/' . $moduleName . '/assets/js/script.js', '');
        file_put_contents('temp/' . $moduleName . '/' . $moduleName . '.php', $modulePHP);
        file_put_contents('temp/' . $moduleName . '/helper.php', $helperPHP);
        file_put_contents('temp/' . $moduleName . '/tmpl/default.php', $defaultTmplPHP);
    }
    if ($joomlaVersion == '4') {

        $modulePHP =
            "<?php{$phpHeaders}\ndefined('_JEXEC') or die;\n\nuse Joomla\CMS\Helper\ModuleHelper;\nuse Joomla\Module\\{$helperName}\\{$moduleClient}\Helper\\{$helperName}Helper;\n/**@var  @var Joomla\CMS\WebAsset\WebAssetManager $wa */\n\$wa = \$app->getDocument()->getWebAssetManager();\n\$wa->registerAndUseStyle('{$moduleName}_css', 'media/{$moduleName}/css/style.css');\n\$wa->registerAndUseScript('{$moduleName}_js', 'media/{$moduleName}/js/script.js');\n\n\$moduleclass_sfx = htmlspecialchars(\$params->get('moduleclass_sfx'), ENT_QUOTES);\nrequire ModuleHelper::getLayoutPath('{$moduleName}', \$params->get('layout', 'default'));\nif(\$params->get('backgroundimage')) {\n\$wa->useStyleDeclaration('#module-itemslist-id-'.\$module->id.' {\nbackground-image: url('.\$params->get('backgroundimage').');\n}');\n}\n?>";
        $helperPHP =
            "<?php{$phpHeaders}\nnamespace Joomla\Module\\{$helperName}\\{$moduleClient}\Helper;\n\nuse Joomla\CMS\Association\AssociationServiceInterface;\nuse Joomla\CMS\Factory;\nuse Joomla\CMS\Language\Associations;\nuse Joomla\CMS\Language\LanguageHelper;\nuse Joomla\CMS\Language\Multilanguage;\nuse Joomla\CMS\Router\Route;\nuse Joomla\CMS\Uri\Uri;\nuse Joomla\Component\Menus\Administrator\Helper\MenusHelper;\n\n// phpcs:disable PSR1.Files.SideEffects\n\defined('_JEXEC') or die;\n// phpcs:enable PSR1.Files.SideEffects\n\n/**\n * Helper for {$moduleName}\n *\n * @since  1.6\n */\nabstract class {$helperName}Helper\n{\n    /**\n     * Gets a list of available items\n     *\n     * @param   \\Joomla\\Registry\\Registry  &\$params  module params\n     *\n     * @return  array\n     */\n    public static function getList(&\$params)\n    {\n\n        \$items = false;\n\n        return \$items;\n    }\n}\n";
        $defaultTmplPHP = "<?php{$phpHeaders}\ndefined('_JEXEC') or die;\n\nuse Joomla\CMS\HTML\HTMLHelper;\nuse Joomla\CMS\Language\Text;\n\n\$items = {$helperName}Helper::getList(\$params);\n?>\n
<div id=\"module-itemslist-id-<?php echo \$module->id; ?>\" class=\"{$moduleName}_itemslist\">
    \n<?php if (\$params->get('customtext', 0)) : ?>\n<div class=\"customtext\">
        \n<?php echo HTMLHelper::_('content.prepare', \$params->get('customtext')); ?>\n</div>
    \n<?php endif; ?>\n<?php if(\$items) : ?>\n<div class=\"items\">\n<?php foreach (\$items as \$item) : ?>\n<div
            class=\"item\">\n<h2><?php echo \$item->title; ?></h2>\n<p><?php echo \$item->text; ?></p>\n</div>
        \n<?php endforeach; ?>\n</div>\n<?php endif; ?>\n</div>";
// camera photo svg
        $svgImage = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
    <path
        d="M480 128h-96l-32-64h-160l-32 64h-96c-26.51 0-48 21.49-48 48v256c0 26.51 21.49 48 48 48h384c26.51 0 48-21.49 48-48v-256c0-26.51-21.49-48-48-48zm-240 288c-79.529 0-144-64.471-144-144s64.471-144 144-144 144 64.471 144 144-64.471 144-144 144zm0-240c-53.019 0-96 42.981-96 96s42.981 96 96 96 96-42.981 96-96-42.981-96-96-96zm192 192h-32v-32h32v32zm0-64h-32v-96h32v96z" />
</svg>';
        file_put_contents('temp/' . $moduleName . '/media/images/camera-photo.svg', $svgImage);
        file_put_contents('temp/' . $moduleName . '/media/css/style.css', '');
        file_put_contents('temp/' . $moduleName . '/media/js/script.js', '');
        file_put_contents('temp/' . $moduleName . '/' . $moduleName . '.php', $modulePHP);
        file_put_contents('temp/' . $moduleName . '/src/Helper/' . $helperName . 'Helper.php', $helperPHP);
        file_put_contents('temp/' . $moduleName . '/tmpl/default.php', $defaultTmplPHP);
    }
// Generate the XML
    $xml = new SimpleXMLElement('<extension></extension>');
    $xml->addAttribute('type', 'module');
// client="site" method="upgrade"

    $xml->addAttribute('client', $client);

    $xml->addAttribute('method', 'upgrade');
    $xml->addAttribute('version', $moduleVersion);
    $xml->addChild('name', $moduleName);
    $xml->addChild('author', $moduleAuthor);
    $xml->addChild('creationDate', $moduleDate);
    $xml->addChild('license', $moduleLicense);
    $xml->addChild('authorEmail', $moduleAuthorEmail);
    $xml->addChild('authorUrl', $moduleAuthorUrl);
    $xml->addChild('description', $moduleNameUpperCase . '_DESC');
// namespace for Joomla 4
    if ($joomlaVersion == '4') {
        $xmlNamespace = $xml->addChild('namespace', 'Joomla\Module\\' . $helperName);
        $xmlNamespace->addAttribute('path', 'src');
    }

    $xmlFiles = $xml->addChild('files');
// child folders tmpl, assets, language
    // child filenames $moduleName.php with attribute module, helper.php
    $xmlFile = $xmlFiles->addChild('filename', $moduleName . '.php');
    $xmlFile->addAttribute('module', $moduleName);
    if ($joomlaVersion == '3') {

        $xmlFile = $xmlFiles->addChild('filename', 'helper.php');
        $xmlFile = $xmlFiles->addChild('folder', 'tmpl');
        $xmlFile = $xmlFiles->addChild('folder', 'assets');
    }
    if ($joomlaVersion == '4') {
        $xmlFile = $xmlFiles->addChild('folder', 'src');
        $xmlFile = $xmlFiles->addChild('folder', 'tmpl');
    }

    $xmlLanguages = $xml->addChild('languages');
    if ($joomlaVersion == '3') {
        $xmlLanguage = $xmlLanguages->addChild('language', 'language/en-GB/en-GB.' . $moduleName . '.ini');
        $xmlLanguage->addAttribute('tag', 'en-GB');
        $xmlLanguage = $xmlLanguages->addChild('language', 'language/en-GB/en-GB.' . $moduleName . '.sys.ini');
        $xmlLanguage->addAttribute('tag', 'en-GB');
        $xmlLanguage = $xmlLanguages->addChild('language', 'language/pt-BR/pt-BR.' . $moduleName . '.ini');
        $xmlLanguage->addAttribute('tag', 'pt-BR');
        $xmlLanguage = $xmlLanguages->addChild('language', 'language/pt-BR/pt-BR.' . $moduleName . '.sys.ini');
        $xmlLanguage->addAttribute('tag', 'pt-BR');
    }
    if ($joomlaVersion == '4') {
        $xmlLanguage = $xmlLanguages->addChild('language', 'language/en-GB/' . $moduleName . '.ini');
        $xmlLanguage->addAttribute('tag', 'en-GB');
        $xmlLanguage = $xmlLanguages->addChild('language', 'language/en-GB/' . $moduleName . '.sys.ini');
        $xmlLanguage->addAttribute('tag', 'en-GB');
        $xmlLanguage = $xmlLanguages->addChild('language', 'language/pt-BR/' . $moduleName . '.ini');
        $xmlLanguage->addAttribute('tag', 'pt-BR');
        $xmlLanguage = $xmlLanguages->addChild('language', 'language/pt-BR/' . $moduleName . '.sys.ini');
        $xmlLanguage->addAttribute('tag', 'pt-BR');
    }
    if ($joomlaVersion == '4') {
        $xmlMedia = $xml->addChild('media');
        $xmlMedia->addAttribute('destination', $moduleName);
        $xmlMedia->addAttribute('folder', 'media');
        $xmlMedia->addChild('folder', 'images');
        $xmlMedia->addChild('folder', 'css');
        $xmlMedia->addChild('folder', 'js');
    }
// config
    $xmlParams = $xml->addChild('config');
// add fields with name="params" to fields
    $xmlParam = $xmlParams->addChild('fields');
    $xmlParam->addAttribute('name', 'params');
// add fieldset with name="basic"
    $xmlFieldset = $xmlParam->addChild('fieldset');
    $xmlFieldset->addAttribute('name', 'basic');
    $xmlField = $xmlFieldset->addChild('field');
    $xmlField->addAttribute('name', 'customtext');
    $xmlField->addAttribute('type', 'editor');
    $xmlField->addAttribute('label', $moduleNameUpperCase . '_CUSTOM_LABEL');
    $xmlField->addAttribute('description', $moduleNameUpperCase . '_CUSTOM_DESC');
    $xmlField->addAttribute('filter', 'raw');
    $xmlField->addAttribute('buttons', 'true');
// add fieldset with name="options" to fields params
    $xmlFieldset = $xmlParam->addChild('fieldset');
    $xmlFieldset->addAttribute('name', 'options');
// prepare_content
    // radio
    // joomla.form.field.radio.switcher
    // backgroundimage
    // media
    $xmlField = $xmlFieldset->addChild('field');
    $xmlField->addAttribute('name', 'prepare_content');
    $xmlField->addAttribute('type', 'radio');
    $xmlField->addAttribute('label', $moduleNameUpperCase . '_JFIELD_PREPARE_CONTENT_LABEL');
    $xmlField->addAttribute('description', $moduleNameUpperCase . '_JFIELD_PREPARE_CONTENT_DESC');
    if ($joomlaVersion == '3') {
        $xmlField->addAttribute('layout', 'joomla.form.field.radio.switcher');
    } else {
        $xmlField->addAttribute('class', 'btn-group btn-group-yesno');
// add options to radio
        $xmlOption = $xmlField->addChild('option', 'JYES');
        $xmlOption->addAttribute('value', '1');
        $xmlOption = $xmlField->addChild('option', 'JNO');
        $xmlOption->addAttribute('value', '0');
        $xmlField->addAttribute('filter', 'integer');
    }
    $xmlField->addAttribute('default', '1');
    $xmlField = $xmlFieldset->addChild('field');
    $xmlField->addAttribute('name', 'backgroundimage');
    $xmlField->addAttribute('type', 'media');
    $xmlField->addAttribute('label', $moduleNameUpperCase . '_JFIELD_BACKGROUNDIMAGE_LABEL');
    $xmlField->addAttribute('description', $moduleNameUpperCase . '_JFIELD_BACKGROUNDIMAGE_DESC');
// add fieldset with name="advanced" to fields params
    $xmlFieldset = $xmlParam->addChild('fieldset');
    $xmlFieldset->addAttribute('name', 'advanced');
// add field with name="layout"
    $xmlField = $xmlFieldset->addChild('field');
    $xmlField->addAttribute('name', 'layout');
    $xmlField->addAttribute('type', 'modulelayout');
    $xmlField->addAttribute('label', 'JFIELD_ALT_LAYOUT_LABEL');
    $xmlField->addAttribute('description', 'JFIELD_ALT_MODULE_LAYOUT_DESC');
    $xmlField->addAttribute('validate', 'moduleLayout');
// add field with name="moduleclass_sfx"
    $xmlField = $xmlFieldset->addChild('field');
    $xmlField->addAttribute('name', 'moduleclass_sfx');
    $xmlField->addAttribute('type', 'textarea');
    $xmlField->addAttribute('label', 'COM_MODULES_FIELD_MODULECLASS_SFX_LABEL');
    $xmlField->addAttribute('description', 'COM_MODULES_FIELD_MODULECLASS_SFX_DESC');
    $xmlField->addAttribute('rows', '3');
// add field with name="cache"
    $xmlField = $xmlFieldset->addChild('field');
    $xmlField->addAttribute('name', 'cache');
    $xmlField->addAttribute('type', 'list');
    $xmlField->addAttribute('label', 'COM_MODULES_FIELD_CACHING_LABEL');
    $xmlField->addAttribute('description', 'COM_MODULES_FIELD_CACHING_DESC');
    $xmlField->addAttribute('default', '1');
    $xmlField->addAttribute('filter', 'integer');
// add options to field with name="cache"
    $xmlOption = $xmlField->addChild('option', 'JGLOBAL_USE_GLOBAL');
    $xmlOption->addAttribute('value', '1');
    $xmlOption = $xmlField->addChild('option', 'COM_MODULES_FIELD_VALUE_NOCACHING');
    $xmlOption->addAttribute('value', '0');
// add field with name="cache_time"
    $xmlField = $xmlFieldset->addChild('field');
    $xmlField->addAttribute('name', 'cache_time');
    $xmlField->addAttribute('type', 'number');
    $xmlField->addAttribute('label', 'COM_MODULES_FIELD_CACHE_TIME_LABEL');
    $xmlField->addAttribute('description', 'COM_MODULES_FIELD_CACHE_TIME_DESC');
    $xmlField->addAttribute('default', '900');
    $xmlField->addAttribute('filter', 'integer');
// add field with name="cachemode"
    $xmlField = $xmlFieldset->addChild('field');
    $xmlField->addAttribute('name', 'cachemode');
    $xmlField->addAttribute('type', 'hidden');
    $xmlField->addAttribute('default', 'static');
    $xmlField->addChild('option', 'static');
// format xml
    $dom = new DOMDocument('1.0');
    $dom->preserveWhiteSpace = false;
    $dom->formatOutput = true;
    $dom->loadXML($xml->asXML());
    $xml->asXML();
// Save the XML
    file_put_contents('temp/' . $moduleName . '/' . $moduleName . '.xml', $dom->saveXML());
// en-GB ini file contents for the module
    $enGBModIni = "{$moduleNameUpperCase}=\"{$moduleTitle}\"" . PHP_EOL;
    $enGBModIni .= "{$moduleNameUpperCase}_TITLE=\"{$moduleTitle}\"" . PHP_EOL;
    $enGBModIni .= "{$moduleNameUpperCase}_DESC=\"{$moduleDescription}\"" . PHP_EOL;
    $enGBModIni .= "{$moduleNameUpperCase}_JFIELD_PREPARE_CONTENT_LABEL=\"Prepare Content\"" . PHP_EOL;
    $enGBModIni .= "{$moduleNameUpperCase}_JFIELD_PREPARE_CONTENT_DESC=\"Choose if you want to prepare the content for
plugins\"" . PHP_EOL;
    $enGBModIni .= "{$moduleNameUpperCase}_JFIELD_BACKGROUNDIMAGE_LABEL=\"Background Image\"" . PHP_EOL;
    $enGBModIni .= "{$moduleNameUpperCase}_JFIELD_BACKGROUNDIMAGE_DESC=\"Select an image to use as background\"" . PHP_EOL;
// add {$moduleNameUpperCase}_CUSTOM_LABEL and {$moduleNameUpperCase}_CUSTOM_DESC
    $enGBModIni .= "{$moduleNameUpperCase}_CUSTOM_LABEL=\"Custom Text\"" . PHP_EOL;
    $enGBModIni .= "{$moduleNameUpperCase}_CUSTOM_DESC=\"Enter here your custom text\"" . PHP_EOL;
// en-GB sys ini file contents for the module
    $enGBModSysIni = "{$moduleNameUpperCase}=\"{$moduleTitle}\"" . PHP_EOL;
    $enGBModSysIni .= "{$moduleNameUpperCase}_TITLE=\"{$moduleTitle}\"" . PHP_EOL;
    $enGBModSysIni .= "{$moduleNameUpperCase}_DESC=\"{$moduleDescription}\"" . PHP_EOL;
// PT-BR ini file contents for the module
    $ptBRModIni = "{$moduleNameUpperCase}=\"{$moduleTitle}\"" . PHP_EOL;
    $ptBRModIni .= "{$moduleNameUpperCase}_TITLE=\"{$moduleTitle}\"" . PHP_EOL;
    $ptBRModIni .= "{$moduleNameUpperCase}_DESC=\"{$moduleDescription}\"" . PHP_EOL;
    $ptBRModIni .= "{$moduleNameUpperCase}_JFIELD_PREPARE_CONTENT_LABEL=\"Preparar Conteúdo\"" . PHP_EOL;
    $ptBRModIni .= "{$moduleNameUpperCase}_JFIELD_PREPARE_CONTENT_DESC=\"Escolha se deseja preparar o conteúdo para
plugins\"" . PHP_EOL;
    $ptBRModIni .= "{$moduleNameUpperCase}_JFIELD_BACKGROUNDIMAGE_LABEL=\"Imagem de Fundo\"" . PHP_EOL;
    $ptBRModIni .= "{$moduleNameUpperCase}_JFIELD_BACKGROUNDIMAGE_DESC=\"Selecione uma imagem para usar como
fundo\"" . PHP_EOL;
// add {$moduleNameUpperCase}_CUSTOM_LABEL and {$moduleNameUpperCase}_CUSTOM_DESC
    $ptBRModIni .= "{$moduleNameUpperCase}_CUSTOM_LABEL=\"Texto Personalizado\"" . PHP_EOL;
    $ptBRModIni .= "{$moduleNameUpperCase}_CUSTOM_DESC=\"Digite aqui seu texto personalizado\"" . PHP_EOL;
// PT-BR sys ini file contents for the module
    $ptBRModSysIni = "{$moduleNameUpperCase}=\"{$moduleTitle}\"" . PHP_EOL;
    $ptBRModSysIni .= "{$moduleNameUpperCase}_TITLE=\"{$moduleTitle}\"" . PHP_EOL;
// Create the language files for joomla 3
    if ($joomlaVersion == 3) {
        file_put_contents('temp/' . $moduleName . '/language/en-GB/en-GB.' . $moduleName . '.ini', $enGBModIni);
        file_put_contents('temp/' . $moduleName . '/language/en-GB/en-GB.' . $moduleName . '.sys.ini', $enGBModSysIni);
        file_put_contents('temp/' . $moduleName . '/language/pt-BR/pt-BR.' . $moduleName . '.ini', $ptBRModIni);
        file_put_contents('temp/' . $moduleName . '/language/pt-BR/pt-BR.' . $moduleName . '.sys.ini', $ptBRModSysIni);
    }
// Create the language files for joomla 4
    if ($joomlaVersion == 4) {
        file_put_contents('temp/' . $moduleName . '/language/en-GB/' . $moduleName . '.ini', $enGBModIni);
        file_put_contents('temp/' . $moduleName . '/language/en-GB/' . $moduleName . '.sys.ini', $enGBModSysIni);
        file_put_contents('temp/' . $moduleName . '/language/pt-BR/' . $moduleName . '.ini', $ptBRModIni);
        file_put_contents('temp/' . $moduleName . '/language/pt-BR/' . $moduleName . '.sys.ini', $ptBRModSysIni);
    }
// download the module
    $zip = new ZipArchive();
    $folder = 'temp/' . $moduleName;
// get the files to zip
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($folder),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
// create the zip
    // zip the files and folders inside the temp folder with the correct folder structure
    $zip = new ZipArchive();
    $zip->open("temp/{$moduleName}-J{$joomlaVersion}-{$moduleClient}-V{$moduleVersion}.zip", ZipArchive::CREATE |
        ZipArchive::OVERWRITE);
    if ($joomlaVersion == 3) {

        $files = [
            "{$moduleName}/{$moduleName}.php" => "{$folder}/{$moduleName}.php",
            "{$moduleName}/helper.php" => "{$folder}/helper.php",
            "{$moduleName}/tmpl/default.php" => "{$folder}/tmpl/default.php",
            "{$moduleName}/assets/css/style.css" => "{$folder}/assets/css/style.css",
            "{$moduleName}/assets/js/script.js" => "{$folder}/assets/js/script.js",
            "{$moduleName}/{$moduleName}.xml" => "{$folder}/{$moduleName}.xml",
            "{$moduleName}/language/en-GB/en-GB.{$moduleName}.ini" => "{$folder}/language/en-GB/en-GB.{$moduleName}.ini",
            "{$moduleName}/language/en-GB/en-GB.{$moduleName}.sys.ini" => "{$folder}/language/en-GB/en-GB.{$moduleName}.sys.ini",
            "{$moduleName}/language/pt-BR/pt-BR.{$moduleName}.ini" => "{$folder}/language/pt-BR/pt-BR.{$moduleName}.ini",
            "{$moduleName}/language/pt-BR/pt-BR.{$moduleName}.sys.ini" => "{$folder}/language/pt-BR/pt-BR.{$moduleName}.sys.ini",
        ];
    }
    if ($joomlaVersion == 4) {

        $files = [
            "{$moduleName}/{$moduleName}.php" => "{$folder}/{$moduleName}.php",
            "{$moduleName}/{$moduleName}.xml" => "{$folder}/{$moduleName}.xml",
            "{$moduleName}/src/Helper/{$helperName}Helper.php" => "{$folder}/src/Helper/{$helperName}Helper.php",
            "{$moduleName}/tmpl/default.php" => "{$folder}/tmpl/default.php",
            "{$moduleName}/media/images/camera-photo.svg" => "{$folder}/media/images/camera-photo.svg",
            "{$moduleName}/media/css/style.css" => "{$folder}/media/css/style.css",
            "{$moduleName}/media/js/script.js" => "{$folder}/media/js/script.js",
            "{$moduleName}/language/en-GB/{$moduleName}.ini" => "{$folder}/language/en-GB/{$moduleName}.ini",
            "{$moduleName}/language/en-GB/{$moduleName}.sys.ini" => "{$folder}/language/en-GB/{$moduleName}.sys.ini",
            "{$moduleName}/language/pt-BR/{$moduleName}.ini" => "{$folder}/language/pt-BR/{$moduleName}.ini",
            "{$moduleName}/language/pt-BR/{$moduleName}.sys.ini" => "{$folder}/language/pt-BR/{$moduleName}.sys.ini",
        ];
    }

    foreach ($files as $zipPath => $filePath) {
        $zip->addFile($filePath, $zipPath);
// REVEAL THE MISSNG FILE
        if (!file_exists($filePath)) {
            echo "Missing file: {$filePath}" . PHP_EOL;
        }
    }
    if ($joomlaVersion == 3) {
        $zip->addEmptyDir("{$moduleName}");
        $zip->addEmptyDir("{$moduleName}/assets");
        $zip->addEmptyDir("{$moduleName}/assets/css");
        $zip->addEmptyDir("{$moduleName}/assets/js");
    }
    if ($joomlaVersion == 4) {
        $zip->addEmptyDir("{$moduleName}");
        $zip->addEmptyDir("{$moduleName}/src");
        $zip->addEmptyDir("{$moduleName}/src/Helper");
        $zip->addEmptyDir("{$moduleName}/media");
        $zip->addEmptyDir("{$moduleName}/media/images");
        $zip->addEmptyDir("{$moduleName}/media/css");
        $zip->addEmptyDir("{$moduleName}/media/js");
    }

    $zip->close();
}
// Translations
// check the browser language
$browserLanguage = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
if (file_exists('translations/' . $browserLanguage . '.ini')) {
    $translations = parse_ini_file('translations/' . $browserLanguage . '.ini');
} else {
    $translations = parse_ini_file('translations/en.ini');
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body data-bs-theme="dark">
    <header class="container">
        <h1><?php echo $translations['MODULE_GENERATOR']; ?></h1>
        <?php if (isset($moduleName)): ?>
        <div class="alert alert-success" role="alert">

            <h2><?php
// "Módulo %1s versão %2s para Joomla %3s gerado com sucesso!"
echo sprintf($translations['MODULE_GENERATED'], '<strong>' . $moduleTitle . '</strong>', $moduleVersion, $joomlaVersion); ?>
            </h2>

            <p><a class="btn btn-success"
                    href="<?php echo 'temp/' . $moduleName . '-J' . $joomlaVersion . '-' . $moduleClient . '-V' . $moduleVersion . '.zip'; ?>"
                    role="button"><?php echo $translations['DOWNLOAD_MODULE']; ?></a></p>
        </div>
        <?php endif;?>
    </header>
    <main>
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="inner">
                        <form method="post" action="index.php">
                            <div class="mb-3">
                                <!-- Client -->
                                <label for="client" class="form-label"><?php echo $translations['CLIENT']; ?></label>
                                <select class="form-select" id="client" name="client" required>
                                    <option value="site"><?php echo $translations['SITE']; ?></option>
                                    <option value="administrator"><?php echo $translations['ADMINISTRATOR']; ?></option>
                                </select>
                                <!-- Joomla Version -->
                                <label for="joomla_version"
                                    class="form-label"><?php echo $translations['JOOMLA_VERSION']; ?></label>
                                <select class="form-select" id="joomla_version" name="joomla_version" required>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                                <label for="module_title"
                                    class="form-label"><?php echo $translations['MODULE_TITLE']; ?></label>
                                <input type="text" class="form-control" id="module_title" name="module_title" required
                                    placeholder="<?php echo $translations['MODULE_TITLE_PLACEHOLDER']; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="module_description"
                                    class="form-label"><?php echo $translations['MODULE_DESCRIPTION']; ?></label>
                                <textarea class="form-control" id="module_description" name="module_description"
                                    rows="3" required
                                    placeholder="<?php echo $translations['MODULE_DESCRIPTION_PLACEHOLDER']; ?>"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="module_version"
                                    class="form-label"><?php echo $translations['MODULE_VERSION']; ?></label>
                                <input type="text" class="form-control" id="module_version" name="module_version"
                                    required placeholder="<?php echo $translations['MODULE_VERSION_PLACEHOLDER']; ?>">
                            </div>
                            <!-- license -->
                            <div class="mb-3">
                                <label for="module_license"
                                    class="form-label"><?php echo $translations['MODULE_LICENSE']; ?></label>
                                <select class="form-select" id="module_license" name="module_license" required>
                                    <option value="MIT">MIT</option>
                                    <option value="GPL">GPL</option>
                                    <option value="Apache">Apache</option>
                                    <option value="BSD">BSD</option>
                                    <option value="GNU">GNU</option>
                                    <option value="Mozilla">Mozilla</option>
                                    <option value="Public Domain">Public Domain</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <!-- author -->
                            <div class="mb-3">
                                <label for="module_author"
                                    class="form-label"><?php echo $translations['MODULE_AUTHOR']; ?></label>
                                <input type="text" class="form-control" id="module_author" name="module_author" required
                                    placeholder="<?php echo $translations['MODULE_AUTHOR_PLACEHOLDER']; ?>">
                            </div>
                            <!-- authorEmail -->
                            <div class="mb-3">
                                <label for="module_author_email"
                                    class="form-label"><?php echo $translations['MODULE_AUTHOR_EMAIL']; ?></label>
                                <input type="text" class="form-control" id="module_author_email"
                                    name="module_author_email" required
                                    placeholder="<?php echo $translations['MODULE_AUTHOR_EMAIL_PLACEHOLDER']; ?>">
                            </div>
                            <!-- authorUrl -->
                            <div class="module_author_url" class="form-label">
                                <?php echo $translations['MODULE_AUTHOR_URL']; ?></label>
                                <input type="text" class="form-control" id="module_author_url" name="module_author_url"
                                    required
                                    placeholder="<?php echo $translations['MODULE_AUTHOR_URL_PLACEHOLDER']; ?>">
                            </div>
                            <button type="submit"
                                class="btn btn-primary my-3"><?php echo $translations['GENERATE_MODULE']; ?></button>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>