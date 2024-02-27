<?php

declare(strict_types=1);

namespace B13\TwentyThree\Resolver;

/*
 * This file is part of TYPO3 CMS-based extension "twentythree" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\TwentyThree\Exception\ConfigurationException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationResolver
{
    public static function resolveVideoDomains(): array
    {
        $videoDomains = (string)self::resolveByExtensionConfigutration('videoDomains');

        if ($videoDomains === '') {
            $videoDomains = (string)self::resolveByExtensionConfigutration('videoDomain');
        }

        if (preg_match('/\%env\("(.*)"\)\%/', $videoDomains, $matches)) {
            $videoDomains = self::resolveByEnvVar($matches[1]);
        }

        $videoDomains = GeneralUtility::trimExplode(',', $videoDomains, true);

        if ($videoDomains === []) {
            throw new ConfigurationException('You need to configure at least one video domain in the extension configuration.', 1690198819);
        }

        return array_map(
            static fn ($videoDomain) => rtrim(preg_replace('/^(https|http):\/\//','', $videoDomain, 1), '/'),
            $videoDomains
        );
    }

    /**
     * @return mixed
     */
    public static function resolveByExtensionConfigutration(string $name)
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('twentythree', $name);
    }

    public static function resolveByEnvVar(string $name): string
    {
        if (self::hasEnvVar($name)) {
            return $_ENV[$name] ?? getenv($name) ?: '';
        }

        return '';
    }

    protected static function hasEnvVar(string $name, bool $allowEmpty = false): bool
    {
        $value = $_ENV[$name] ?? getenv($name);

        return $allowEmpty ? is_string($value) : (bool)$value;
    }
}
