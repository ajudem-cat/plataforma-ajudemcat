<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link     http://piwik.org
 * @license  http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\LanguagesManager\Commands;

use Piwik\Cache;
use Piwik\Plugin\Manager;
use Piwik\Plugins\LanguagesManager\API;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 */
class Update extends TranslationBase
{
    protected function configure()
    {
        $this->setName('translations:update')
            ->setDescription('Updates translation files')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force update of all language files')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Transifex username')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Transifex password')
            ->addOption('slug', 's', InputOption::VALUE_OPTIONAL, 'Transifex project slug')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Force to update all plugins (even non core). Can not be used with plugin option')
            ->addOption('plugin', 'P', InputOption::VALUE_OPTIONAL, 'optional name of plugin to update translations for');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setDecorated(true);

        $start = microtime(true);

        /** @var DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        $languages = API::getInstance()->getAvailableLanguageNames();

        $languageCodes = array();
        foreach ($languages as $languageInfo) {
            $languageCodes[] = $languageInfo['code'];
        }

        $plugin = $input->getOption('plugin');
        $forceAllPlugins = $input->getOption('all');

        if (!$input->isInteractive()) {
            $output->writeln("(!) Non interactive mode: New languages will be skipped");
        }

        $pluginList = array($plugin);
        if (empty($plugin)) {
            $pluginList = $forceAllPlugins ? self::getAllPlugins() : self::getPluginsInCore();
            array_unshift($pluginList, '');
        } else {
            $input->setOption('force', true); // force plugin only updates
        }

        foreach ($pluginList as $plugin) {

            $output->writeln("");

            // fetch base or specific plugin
            $this->fetchTranslations($input, $output, $plugin);

            $files = _glob(FetchTranslations::getDownloadPath() . DIRECTORY_SEPARATOR . '*.json');

            if (count($files) == 0) {
                $output->writeln("No translation updates available! Skipped.");
                continue;
            }

            $output->writeln("Starting to import new language files");

            /** @var ProgressBar $progress */
            $progress = new ProgressBar($output, count($files));

            $progress->start();

            foreach ($files as $filename) {

                $progress->advance();

                $code = basename($filename, '.json');

                if (!in_array($code, $languageCodes)) {

                    if (!empty($plugin)) {
                        continue; # never create a new language for plugin only
                    }

                    $createNewFile = false;
                    if ($input->isInteractive()) {
                        $createNewFile = $dialog->askConfirmation($output, "\nLanguage $code does not exist. Should it be added? ", false);
                    }

                    if (!$createNewFile) {
                        continue; # do not create a new file for the language
                    }

                    @touch(PIWIK_DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $code . '.json');
                    API::unsetAllInstances(); // unset language manager instance, so valid names are refetched

                    $command = $this->getApplication()->find('translations:generate-intl-data');
                    $arguments = array(
                        'command' => 'translations:generate-intl-data',
                        '--language' => $code,
                    );
                    $inputObject = new ArrayInput($arguments);
                    $inputObject->setInteractive($input->isInteractive());
                    $command->run($inputObject, $output->isVeryVerbose() ? $output : new NullOutput());

                    API::unsetAllInstances(); // unset language manager instance, so valid names are refetched
                    Cache::flushAll();

                    $languageCodes[] = $code;
                }

                $command = $this->getApplication()->find('translations:set');
                $arguments = array(
                    'command' => 'translations:set',
                    '--code' => $code,
                    '--file' => $filename,
                    '--plugin' => $plugin
                );
                $inputObject = new ArrayInput($arguments);
                $inputObject->setInteractive($input->isInteractive());
                $command->run($inputObject, $output->isVeryVerbose() ? $output : new NullOutput());
            }

            $progress->finish();
            $output->writeln('');
        }

        $output->writeln("Finished in " . round(microtime(true)-$start, 3) . "s");
    }

    /**
     * Returns all plugins having their own translations that are bundled in core
     * @return array
     */
    public static function getAllPlugins()
    {
        static $pluginsWithTranslations;

        if (!empty($pluginsWithTranslations)) {
            return $pluginsWithTranslations;
        }

        $pluginsWithTranslations = array();
        foreach (Manager::getPluginsDirectories() as $pluginsDir) {
            $pluginsWithTranslations = array_merge($pluginsWithTranslations, glob(sprintf('%s*/lang/en.json', $pluginsDir)));
        }
        $pluginsWithTranslations = array_map(function ($elem) {
            $replace = Manager::getPluginsDirectories();
            $replace[] = '/lang/en.json';
            return str_replace($replace, '', $elem);
        }, $pluginsWithTranslations);

        return $pluginsWithTranslations;
    }

    /**
     * Returns all plugins having their own translations that are bundled in core
     * @return array
     */
    public static function getPluginsInCore()
    {
        static $pluginsInCore;

        if (!empty($pluginsInCore)) {
            return $pluginsInCore;
        }

        $submodules = shell_exec('git submodule status');
        preg_match_all('/plugins\/([a-zA-z]+) /', $submodules, $matches);
        $submodulePlugins = $matches[1];

        // ignore complete new plugins as well
        $changes = shell_exec('git status');
        preg_match_all('/plugins\/([a-zA-z]+)\/\n/', $changes, $matches);
        $newPlugins = $matches[1];

        $pluginsNotInCore = array_merge($submodulePlugins, $newPlugins);
        $pluginsWithTranslations = array();
        foreach (Manager::getPluginsDirectories() as $pluginsDir) {
            $pluginsWithTranslations = array_merge($pluginsWithTranslations, glob(sprintf('%s*/lang/en.json', $pluginsDir)));
        }
        $pluginsWithTranslations = array_map(function ($elem) {
            $replace = Manager::getPluginsDirectories();
            $replace[] = '/lang/en.json';
            return str_replace($replace, '', $elem);
        }, $pluginsWithTranslations);

        $pluginsInCore = array_diff($pluginsWithTranslations, $pluginsNotInCore);

        return $pluginsInCore;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $plugin
     * @throws \Exception
     */
    protected function fetchTranslations(InputInterface $input, OutputInterface $output, $plugin)
    {

        $command = $this->getApplication()->find('translations:fetch');
        $arguments = array(
            'command' => 'translations:fetch',
            '--username' => $input->getOption('username'),
            '--password' => $input->getOption('password'),
            '--slug'     => $input->getOption('slug'),
            '--plugin'   => $plugin
        );

        if ($input->getOption('force')) {
            $arguments['--lastupdate'] = 1;
        } else {
            $lastModDate = strtotime('2015-01-04 00:00:00'); // date of initial transifex setup
            try {
                // try to find the language file (of given plugin) with the newest modification date in git log
                $path = ($plugin ? 'plugins/' . $plugin . '/' : '') . 'lang';
                $files = explode("\n", trim(shell_exec('git ls-tree -r --name-only HEAD ' . $path)));

                foreach ($files as $file) {
                    $fileModDate = shell_exec('git log -1 --format="%at" -- ' . $file);
                    if (basename($file) != 'en.json' && $fileModDate > $lastModDate) {
                        $lastModDate = $fileModDate;
                    }
                }
            } catch (\Exception $e) {
            }

            if ($lastModDate != 0) {
                $arguments['--lastupdate'] = $lastModDate;
            }
        }
        $inputObject = new ArrayInput($arguments);
        $inputObject->setInteractive($input->isInteractive());
        $command->run($inputObject, $output);
    }
}
