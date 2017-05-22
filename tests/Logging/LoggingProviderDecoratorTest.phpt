<?php

/**
 * Test: Kdyby\Geocoder\Logging\LoggingProviderDecorator.
 *
 * @testCase
 */

namespace KdybyTests\Geocoder\Logging;

use Geocoder\Exception\InvalidArgument;
use Geocoder\Exception\NoResult;
use Geocoder\Exception\QuotaExceeded;
use Geocoder\Model\AddressCollection;
use Geocoder\Model\AddressFactory;
use Geocoder\Provider\Provider;
use Kdyby\Geocoder\Logging\LoggingProviderDecorator;
use Mockery;
use Psr\Log\LoggerInterface;
use Tester\Assert;

require_once __DIR__ . '/bootstrap.php';

class LoggingProviderDecoratorTest extends \Tester\TestCase
{

	public function testGeocode()
	{
		$a = self::createAddress('Brno', 'Soukenická', 5);

		$inner = $this->mockProvider();
		$inner->shouldReceive('geocode')->once()->andReturn(new AddressCollection([$a]));
		$inner->shouldReceive('limit')->once()->andReturn($inner);

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->never();

		$provider = new LoggingProviderDecorator($inner, $logger);
		$result = $provider->geocode('Soukenická 5');

		Assert::same($a, $result->first());
	}

	public function testGeocodeNoResult()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('geocode')->once()->andThrow($e = new NoResult('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->never();

		$provider = new LoggingProviderDecorator($inner, $logger);
		Assert::count(0, $provider->geocode('Soukenická 5'));
	}

	public function testGeocodeQuotaExceeded()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('geocode')->once()->andThrow($e = new QuotaExceeded('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);
		$inner->shouldReceive('getName')->andReturn('inner');

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->once()->with('QuotaExceeded(inner): message');

		$provider = new LoggingProviderDecorator($inner, $logger);
		Assert::count(0, $provider->geocode('Soukenická 5'));
	}

	public function testGeocodeException()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('geocode')->once()->andThrow($e = new InvalidArgument('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);
		$inner->shouldReceive('getName')->andReturn('inner');

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->once()->with('Geocoder\Exception\InvalidArgument(inner): message');

		$provider = new LoggingProviderDecorator($inner, $logger);
		Assert::count(0, $provider->geocode('Soukenická 5'));
	}

	public function testReverse()
	{
		$a = self::createAddress('Brno', 'Soukenická', 5);

		$inner = $this->mockProvider();
		$inner->shouldReceive('reverse')->once()->andReturn(new AddressCollection([$a]));
		$inner->shouldReceive('limit')->once()->andReturn($inner);

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->never();

		$provider = new LoggingProviderDecorator($inner, $logger);
		$result = $provider->reverse(49.1881713867, 16.6049518585);

		Assert::same($a, $result->first());
	}

	public function testReverseNoResult()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('reverse')->once()->andThrow($e = new NoResult('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->never();

		$provider = new LoggingProviderDecorator($inner, $logger);
		Assert::count(0, $provider->reverse(49.1881713867, 16.6049518585));
	}

	public function testReverseQuotaExceeded()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('reverse')->once()->andThrow($e = new QuotaExceeded('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);
		$inner->shouldReceive('getName')->andReturn('inner');

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->once()->with('QuotaExceeded(inner): message');

		$provider = new LoggingProviderDecorator($inner, $logger);
		Assert::count(0, $provider->reverse(49.1881713867, 16.6049518585));
	}

	public function testReverseException()
	{
		$inner = $this->mockProvider();
		$inner->shouldReceive('reverse')->once()->andThrow($e = new InvalidArgument('message'));
		$inner->shouldReceive('limit')->once()->andReturn($inner);
		$inner->shouldReceive('getName')->andReturn('inner');

		$logger = $this->mockLogger();
		$logger->shouldReceive('warning')->once()->with('Geocoder\Exception\InvalidArgument(inner): message');

		$provider = new LoggingProviderDecorator($inner, $logger);
		Assert::count(0, $provider->reverse(49.1881713867, 16.6049518585));
	}

	protected function tearDown()
	{
		Mockery::close();
	}

	/**
	 * @return \Geocoder\Provider\Provider|\Mockery\MockInterface
	 */
	private function mockProvider()
	{
		return Mockery::mock(Provider::class);
	}

	/**
	 * @return \Psr\Log\LoggerInterface|\Mockery\MockInterface
	 */
	private function mockLogger()
	{
		return Mockery::mock(LoggerInterface::class);
	}

	/**
	 * @return \Geocoder\Model\Address
	 */
	public static function createAddress($city, $street, $houseNumber = NULL, $orientationNumber = NULL, $postalCode = NULL)
	{
		$factory = new AddressFactory();
		return $factory->createFromArray([
			[
				'locality' => $city,
				'streetName' => $street,
				'streetNumber' => implode('/', array_filter([$houseNumber, $orientationNumber])),
				'postalCode' => $postalCode,
			],
		])->first();
	}

}

(new LoggingProviderDecoratorTest())->run();
