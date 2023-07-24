<?php

declare(strict_types=1);

namespace B13\TwentyThree\Resource\Resolver;

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
    public static function resolveVideoDomain(): string
    {
        $videoDomain = (string)self::resolveByExtensionConfigutration('videoDomain');

        if (preg_match('/\%env\("(.*)"\)\%/', $videoDomain, $matches)) {
            $videoDomain = self::resolveByEnvVar($matches[1]);
        }

        if ($videoDomain === '') {
            throw new ConfigurationException('You need to configure a video domain in the extension configuration.', 1690198819);
        }

        return rtrim(preg_replace('/^(https|http):\/\//','', $videoDomain,1), '/');
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
