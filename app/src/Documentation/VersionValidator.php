<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Learn\Documentation;

use InvalidArgumentException;
use UserFrosting\Config\Config;

/**
 * Validate and retrieve documentation versions.
 */
class VersionValidator
{
    public function __construct(
        protected Config $config,
    ) {
    }

    /**
     * Check if the given version is valid.
     *
     * @param  string|null $version
     * @return bool
     */
    public function isValid(?string $version): bool
    {
        if ($version === null || $version === '') {
            $version = $this->getLatestVersion();
        }

        return array_key_exists($version, $this->getAvailableVersions());
    }

    /**
     * Get the Version object for the given version string.
     *
     * @param  string|null              $version Null or empty string to get the latest version
     * @throws InvalidArgumentException
     * @return Version
     */
    public function getVersion(?string $version): Version
    {
        if ($version === null || $version === '') {
            $version = $this->getLatestVersion();
        }

        $availableVersions = $this->getAvailableVersions();

        if (!array_key_exists($version, $availableVersions)) {
            throw new InvalidArgumentException("Invalid version: {$version}");
        }

        $label = $availableVersions[$version];
        $latest = $this->isLatest($version);

        return new Version($version, $label, $latest);
    }

    /**
     * Check if the given version is the latest.
     *
     * @return bool
     */
    protected function isLatest(string $version): bool
    {
        return $version === $this->getLatestVersion();
    }

    /**
     * Get the available versions from config.
     *
     * @return array<string, string> version => label
     */
    protected function getAvailableVersions(): array
    {
        return $this->config->get('site.versions.available', []);
    }

    /**
     * Get the latest documentation version.
     *
     * @return string Latest version
     */
    protected function getLatestVersion(): string
    {
        return $this->config->get('site.versions.latest');
    }
}
