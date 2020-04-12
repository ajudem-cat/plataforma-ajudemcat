<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\WordPress;

use Monolog\Processor\PsrLogMessageProcessor;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Log;
use Piwik\Notification;
use Piwik\Notification\Manager;
use Piwik\Plugins\Monolog\Processor\ClassNameProcessor;
use Piwik\Plugins\Monolog\Processor\ExceptionToTextProcessor;
use Piwik\Plugins\Monolog\Processor\RequestIdProcessor;
use Piwik\Plugins\Monolog\Processor\SprintfProcessor;
use Piwik\Plugins\Monolog\Processor\TokenProcessor;
use Piwik\SettingsServer;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use WpMatomo\Bootstrap;

if (!defined( 'ABSPATH')) {
    exit; // if accessed directly
}

class Logger extends AbstractLogger implements LoggerInterface
{
	const DEBUG = 100;
	const INFO = 200;
	const NOTICE = 250;
	const WARNING = 300;
	const ERROR = 400;
	const CRITICAL = 500;
	const ALERT = 550;
	const EMERGENCY = 600;

	private $levels = array(
        self::DEBUG     => 'DEBUG',
        self::INFO      => 'INFO',
        self::NOTICE    => 'NOTICE',
        self::WARNING   => 'WARNING',
        self::ERROR     => 'ERROR',
        self::CRITICAL  => 'CRITICAL',
        self::ALERT     => 'ALERT',
        self::EMERGENCY => 'EMERGENCY',
    );

	private $writers = array();

	private $is_tracker = false;

	private $level = self::WARNING;

	/**
	 * @var \WpMatomo\Logger
	 */
	private $logger;

	public function __construct() {
		$this->logger = new \WpMatomo\Logger();
		$logConfig = Config::getInstance()->log;

		if (!empty($logConfig['log_writers'])) {
			$this->writers = $logConfig['log_writers'];
		}

		$this->is_tracker = SettingsServer::isTrackerApiRequest();

		$level = null;
		if (!empty($logConfig['log_level'])) {
			$level = strtoupper($logConfig['log_level']);

			if (defined('Piwik\Log::'.strtoupper($level))) {
				$this->level = Log::getMonologLevel(constant('Piwik\Log::'.strtoupper($level)));
			}
		}
	}

	private function make_numeric_level($level)
	{
		if (is_string($level) && defined(__CLASS__.'::'.strtoupper($level))) {
			return constant(__CLASS__.'::'.strtoupper($level));
		}

		return $level;
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function log( $level, $message, array $context = array() ) {

		if ( !defined( 'WP_DEBUG' ) || WP_DEBUG !== true ) {
			return;
		}

		$level = $this->make_numeric_level($level);

		if ($level < $this->level) {
			return;
		}

		$title = '';
		if ($this->levels[$level]) {
			$title = $this->levels[$level] . ': ';
		}

		$record = array('message' => $message, 'context' => $context);
		$processors = [
			new SprintfProcessor(),
			new ClassNameProcessor(),
			new RequestIdProcessor(),
			new ExceptionToTextProcessor(),
			new PsrLogMessageProcessor(),
			new TokenProcessor()
		];
		foreach ($processors as $processor) {
			$record = call_user_func($processor, $record);
		}
		$message = $record['message'];

		if (!Common::isPhpCliMode()
		    && !Bootstrap::was_bootstrapped_by_wordpress() // we don't want to show log messages while being in wordpress
		    && in_array('screen', $this->writers)
		    && !empty($message)) {
			if ($this->is_tracker) {
				if (StaticContainer::get("ini.Tracker.debug")
				    || !empty($GLOBALS['PIWIK_TRACKER_DEBUG'])) {
					echo $message;
				}
			} else {

				switch ($level) {
					case Logger::EMERGENCY:
					case Logger::ALERT:
					case Logger::CRITICAL:
					case Logger::ERROR:
						$contextNotification = Notification::CONTEXT_ERROR;
						break;
					case Logger::WARNING:
						$contextNotification = Notification::CONTEXT_WARNING;
						break;
					default:
						$contextNotification = Notification::CONTEXT_INFO;
						break;
				}
				$message = $title . htmlentities($message, ENT_COMPAT | ENT_HTML401, 'UTF-8');

				$notification = new Notification($message);
				$notification->context = $contextNotification;
				$notification->flags = 0;
				try {
					Manager::notify(Common::getRandomString(), $notification);
				} catch (\Zend_Session_Exception $e) {
					// Can happen if this handler is enabled in CLI
					// Silently ignore the error.
				}
			}
		}

		$this->logger->log($message);
	}
}
