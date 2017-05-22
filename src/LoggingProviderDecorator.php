<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Geocoder\Logging;

use Geocoder\Exception\NoResult as GeocoderNoResultException;
use Geocoder\Exception\QuotaExceeded as GeocoderQuotaExceededException;
use Geocoder\Model\AddressCollection;
use Geocoder\Provider\Provider;
use Psr\Log\LoggerInterface;

class LoggingProviderDecorator extends \Geocoder\Provider\AbstractProvider implements \Geocoder\Provider\Provider
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

		} catch (GeocoderNoResultException $e) {
			// ignore

		} catch (GeocoderQuotaExceededException $e) {
			$this->logger->warning(sprintf('QuotaExceeded(%s): %s', $this->provider->getName(), $e->getMessage()));

		} catch (\Ivory\HttpAdapter\HttpAdapterException $e) {
			$this->logger->warning(sprintf('%s(%s): %s', get_class($e), $this->provider->getName(), $e->getMessage()));

		} catch (\Geocoder\Exception\Exception $e) {
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

		} catch (GeocoderNoResultException $e) {
			// ignore

		} catch (GeocoderQuotaExceededException $e) {
			$this->logger->warning(sprintf('QuotaExceeded(%s): %s', $this->provider->getName(), $e->getMessage()));

		} catch (\Ivory\HttpAdapter\HttpAdapterException $e) {
			$this->logger->warning(sprintf('%s(%s): %s', get_class($e), $this->provider->getName(), $e->getMessage()));

		} catch (\Geocoder\Exception\Exception $e) {
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
