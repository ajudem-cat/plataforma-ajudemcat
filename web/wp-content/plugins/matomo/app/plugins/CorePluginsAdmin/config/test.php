<?php

return array(
    'observers.global' => DI\add(array(
        array('Request.dispatchCoreAndPluginUpdatesScreen', function () {
            $pluginName = 'TagManager';
            $unloadTagManager = \Piwik\Container\StaticContainer::get('test.vars.unloadTagManager');
            $tagManagerTeaser = new \Piwik\Plugins\CorePluginsAdmin\Model\TagManagerTeaser(\Piwik\Piwik::getCurrentUserLogin());
            if ($unloadTagManager) {
                $pluginManager = \Piwik\Plugin\Manager::getInstance();
                if ($pluginManager->isPluginActivated($pluginName)
                    && $pluginManager->isPluginLoaded($pluginName)) {
                    $pluginManager->unloadPlugin($pluginName);
                }
                $tagManagerTeaser->reset();
            }
        }),
    ))
);
