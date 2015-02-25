<?php
/**
 * Created by PhpStorm.
 * User: jan.majek
 * Date: 25/02/2015
 * Time: 11:46
 */

namespace FTBlogs;

use FTLabs\Logger;
use Psr\Log\LogLevel;


class Profiler
{
	/**
	 * @var array
	 */
	protected $logData;

	/**
	 * @var Logger
	 */
	protected $logger;

	/**
	 * @var string
	 */
	protected $logLevel;

	/**
	 * @var bool
	 */
	protected $enabled;

	/**
	 * @var float microtime(true) when the profiler was started
	 */
	protected $startTime;

	/**
	 * @var float microtime(true) when the profiler logged last event
	 */
	protected $lastEventTime;

	/**
	 * @var int
	 */
	protected $eventId;

	/**
	 * @var string md5 hash of profile start time, log data
	 */
	protected $profileId;

	public function __construct( Logger $logger, array $logData = array(), string $logLevel = LogLevel::INFO, $enabled = true )
	{
		$this->logger   = $logger;
		$this->logData  = $logData;
		$this->logLevel = $logLevel;

		$this->enable($enabled);
		$this->start();
	}

	public function start()
	{
		$this->eventId = 0;
		$this->startTime = $this->lastEventTime = microtime(true);
		$this->profileId = md5(serialize(array_merge($this->logData, array('profile_start' => $this->startTime))));

		$this->logEvent('profile-start');
	}

	public function end()
	{
		$this->logEvent('profile-end');
	}

	public function logEvent($event)
	{
		if (!$this->isEnabled()) return;

		$eventTime = microtime(true);
		$logData = $this->logData;
		$logData['event'] = $event;
		$logData['event_id'] = $this->eventId++;
		$logData['event_time'] = $eventTime;
		$logData['last_event_duration'] = $eventTime - $this->lastEventTime;
		$logData['profile_start'] = $this->startTime;
		$logData['profile_duration'] = $eventTime - $this->startTime;
		$this->lastEventTime = $eventTime;

		$this->logger->log($this->logLevel, '', $logData);
		unset ($logData);
	}

	/**
	 * @param bool $enable
	 * @return $this
	 */
	public function enable($enable = true)
	{
		$this->enabled = (bool)$enable;
		return $this;
	}

	public function isEnabled()
	{
		return $this->enabled;
	}
}