<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Geocoder\Logging;

use Geocoder;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\AbstractProvider;
use Geocoder\Provider\Provider;
use Ivory;
use Kdyby;
use Psr\Log\LoggerInterface;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class LoggingProviderDecorator extends AbstractProvider implements Provider
{

	/**
	 * @var \Geocoder\Provider\Provider
	 */
	private $provider;

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;



	public function __construct(Provider $provider, LoggerInterface $logger)
	{
		parent::__construct();
		$this->provider = $provider;
		$this->logger = $logger;
	}



	/**
	 * {@inheritDoc}
	 */
	public function geocode($value)
	{
		try {
			return $this->provider->limit($this->getLimit())->geocode($value);

		} catch (Geocoder\Exception\NoResult $e) {

		} catch (Geocoder\Exception\QuotaExceeded $e) {
			$this->logger->warning(sprintf('QuotaExceeded(%s): %s', $this->provider->getName(), $e->getMessage()));

		} catch (Ivory\HttpAdapter\HttpAdapterException $e) {
			$this->logger->warning(sprintf('%s(%s): %s', get_class($e), $this->provider->getName(), $e->getMessage()));

		} catch (Geocoder\Exception\Exception $e) {
			$this->logger->warning(sprintf('%s(%s): %s', get_class($e), $this->provider->getName(), $e->getMessage()));
		}

		return new AddressCollection([]);
	}



	/**
	 * {@inheritDoc}
	 */
	public function reverse($latitude, $longitude)
	{
		try {
			return $this->provider->limit($this->getLimit())->reverse($latitude, $longitude);

		} catch (Geocoder\Exception\NoResult $e) {

		} catch (Geocoder\Exception\QuotaExceeded $e) {
			$this->logger->warning(sprintf('QuotaExceeded(%s): %s', $this->provider->getName(), $e->getMessage()));

		} catch (Ivory\HttpAdapter\HttpAdapterException $e) {
			$this->logger->warning(sprintf('%s(%s): %s', get_class($e), $this->provider->getName(), $e->getMessage()));

		} catch (Geocoder\Exception\Exception $e) {
			$this->logger->warning(sprintf('%s(%s): %s', get_class($e), $this->provider->getName(), $e->getMessage()));
		}

		return new AddressCollection([]);
	}



	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->provider->getName() . '_silenced';
	}

}
