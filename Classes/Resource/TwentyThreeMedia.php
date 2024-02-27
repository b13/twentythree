<?php

declare(strict_types=1);

namespace B13\TwentyThree\Resource;

/*
 * This file is part of TYPO3 CMS-based extension "twentythree" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\TwentyThree\Resolver\ConfigurationResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TwentyThreeMedia
{
    public const MIME_TYPE = 'video/twentythree';
    public const FILE_EXTENSION = 'twentythree';

    protected string $videoId;
    protected string $videoDomain;
    protected string $token;

    public function __construct(string $videoId, string $videoDomain = '', string $token = '')
    {
        $this->videoId = $videoId;
        $this->videoDomain = $videoDomain;
        $this->token = $token;
    }

    public static function createFromMediaId(string $mediaId): self
    {
        $mediaParts = GeneralUtility::trimExplode('_', $mediaId, true, 2);
        $videoParts = GeneralUtility::trimExplode('|', (string)($mediaParts[0] ?? ''), true, 2);

        $videoId = (string)($videoParts[0] ?? '');
        $videoDomain = (string)($videoParts[1] ?? '');
        $token = (string)($mediaParts[1] ?? '');

        if ($videoDomain === '') {
            $videoDomains = ConfigurationResolver::resolveVideoDomains();
            $videoDomain = reset($videoDomains);
        }

        return new self($videoId, $videoDomain, $token);
    }

    public function getVideoId(): string
    {
        return $this->videoId;
    }

    public function setVideoDomain(string $videoDomain): void
    {
        $this->videoDomain = $videoDomain;
    }

    public function getVideoDomain(): string
    {
        return $this->videoDomain;
    }

    public function hasVideoDomain(): bool
    {
        return $this->videoDomain !== '';
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function hasToken(): bool
    {
        return $this->token !== '';
    }

    public function getMediaIdParts(?string $callback = null): array
    {
        $parts = array_filter([$this->videoDomain, $this->videoId, $this->token]);
        return $callback !== null ? array_map($callback, $parts) : $parts;
    }
}
