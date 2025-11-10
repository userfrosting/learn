<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Learn\Documentation;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Config\Config;
use UserFrosting\Learn\Documentation\VersionValidator;

class VersionValidatorTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Config $configMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configMock = Mockery::mock(Config::class);
        $this->configMock
            ->shouldReceive('get')
            ->with('site.versions.available', Mockery::any())
            ->andReturn(['1.0' => 'Version 1.0', '2.0' => 'Version 2.0']);
        $this->configMock
            ->shouldReceive('get')
            ->with('site.versions.latest')
            ->andReturn('2.0');
    }

    public function testIsValidReturnsTrueForValidVersion(): void
    {
        $validator = new VersionValidator($this->configMock);
        $this->assertTrue($validator->isValid('1.0'));
        $this->assertTrue($validator->isValid('2.0'));
        $this->assertTrue($validator->isValid(null)); // Alias for latest
        $this->assertTrue($validator->isValid('')); // Alias for latest
    }

    public function testIsValidReturnsFalseForInvalidVersion(): void
    {
        $validator = new VersionValidator($this->configMock);
        $this->assertFalse($validator->isValid('3.0'));
    }

    public function testGetVersionReturnsVersionObject(): void
    {
        $validator = new VersionValidator($this->configMock);
        $version = $validator->getVersion('1.0');
        $this->assertEquals('1.0', $version->id);
        $this->assertEquals('Version 1.0', $version->label);
        $this->assertFalse($version->latest);
        $this->assertEquals('1.0', $version->uri());
    }

    public function testGetVersionReturnsVersionObjectForLatest(): void
    {
        $validator = new VersionValidator($this->configMock);
        $version = $validator->getVersion('2.0');
        $this->assertEquals('2.0', $version->id);
        $this->assertEquals('Version 2.0', $version->label);
        $this->assertTrue($version->latest);
        $this->assertEquals('', $version->uri());
    }

    public function testGetVersionWithEmptyStringReturnsLatest(): void
    {
        $validator = new VersionValidator($this->configMock);
        $version = $validator->getVersion('');
        $this->assertEquals('2.0', $version->id);
        $this->assertEquals('Version 2.0', $version->label);
        $this->assertTrue($version->latest);
    }

    public function testGetVersionWithNullReturnsLatest(): void
    {
        $validator = new VersionValidator($this->configMock);
        $version = $validator->getVersion(null);
        $this->assertEquals('2.0', $version->id);
        $this->assertEquals('Version 2.0', $version->label);
        $this->assertTrue($version->latest);
    }

    public function testGetVersionThrowsExceptionForInvalidVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $validator = new VersionValidator($this->configMock);
        $validator->getVersion('3.0');
    }
}
