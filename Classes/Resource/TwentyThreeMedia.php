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

use TYPO3\CMS\Core\Utility\GeneralUtility;

class TwentyThreeMedia
{
    public const MIME_TYPE = 'video/twentythree';
    public const FILE_EXTENSION = 'twentythree';

    protected string $videoId;
    protected string $token;

    public function __construct(string $videoId, string $token = '')
    {
        $this->videoId = $videoId;
        $this->token = $token;
    }

    public static function createFromMediaId(string $mediaId): self
    {
        return new self(
            ...GeneralUtility::trimExplode('_', $mediaId, true, 2)
        );
    }

    public function getVideoId(): string
    {
        return $this->videoId;
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
        $parts = array_filter([$this->videoId, $this->token]);
        return $callback !== null ? array_map($callback, $parts) : $parts;
    }
}
