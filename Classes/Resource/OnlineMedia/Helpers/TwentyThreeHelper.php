<?php

declare(strict_types=1);

namespace B13\TwentyThree\Resource\OnlineMedia\Helpers;

/*
 * This file is part of TYPO3 CMS-based extension "twentythree" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\TwentyThree\Resolver\ConfigurationResolver;
use B13\TwentyThree\Resource\TwentyThreeMedia;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\Folder;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\AbstractOEmbedHelper;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TwentyThreeHelper extends AbstractOEmbedHelper
{
    private const PUBLIC_URL_PATTERN = 'https://%s/video/%s';
    private const PUBLIC_TOKEN_URL_PATTERN = 'https://%s/secret/%s/%s';

    protected string $videoDomain;

    public function __construct($extension)
    {
        parent::__construct($extension);
        $this->videoDomain = ConfigurationResolver::resolveVideoDomain();
    }

    protected function getOEmbedUrl($mediaId, $format = 'json'): string
    {
        $media = TwentyThreeMedia::createFromMediaId($mediaId);

        return sprintf(
            'https://%s/oembed?url=%s&format=%s&maxwidth=2048&maxheight=2048',
            $this->videoDomain,
            rawurlencode(
                sprintf(
                    $media->hasToken() ? self::PUBLIC_TOKEN_URL_PATTERN : self::PUBLIC_URL_PATTERN,
                    $this->videoDomain,
                    ...$media->getMediaIdParts('rawurlencode'))
            ),
            rawurlencode($format)
        );
    }

    public function transformUrlToFile($url, Folder $targetFolder): ?File
    {
        $videoId = $token = null;
        $videoDomain = preg_quote($this->videoDomain, '/');

        // Try to get the TwentyThree video id and a possible token from given url.
        // Following formats are supported with and without http(s)://
        // - <videoDomain>/video/<id>
        // - <videoDomain>/secret/<id>/<token>
        // - <videoDomain>/v.ihtml/player.html?photo_id=<id>&token=<token>
        if (preg_match("#" . $videoDomain . "/video/(\d+)#", $url, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match("#" . $videoDomain . "/secret/(\d+)/([a-z0-9]+)#", $url, $matches)) {
            [,$videoId, $token] = $matches;
        } elseif (preg_match("#" . $videoDomain . "/v\.ihtml/player\.html.*(?:\?|&)photo_id=(\d+)#", $url, $matches)) {
            $videoId = $matches[1];
            if (preg_match("#" . $videoDomain . "/v\.ihtml/player\.html.*(?:\?|&)token=([a-z0-9]+)#", $url, $matches)) {
                $token = $matches[1];
            }
        }

        if (!is_string($videoId) || $videoId === '') {
            return null;
        }

        $mediaId = $videoId;

        if (is_string($token) && $token !== '') {
            $mediaId .= '_' . $token;
        }

        return $this->transformMediaIdToFile($mediaId, $targetFolder, $this->extension);
    }

    public function getPublicUrl(File $file, $relativeToCurrentScript = false): ?string
    {
        $media = TwentyThreeMedia::createFromMediaId($this->getOnlineMediaId($file));
        return sprintf(
            $media->hasToken() ? self::PUBLIC_TOKEN_URL_PATTERN : self::PUBLIC_URL_PATTERN,
            $this->videoDomain,
            ...$media->getMediaIdParts()
        );
    }

    public function getPreviewImage(File $file): string
    {
        $mediaId = $this->getOnlineMediaId($file);
        $temporaryFileName = $this->getTempFolderPath() . 'twentythree_' . md5($mediaId) . '.jpg';

        if (!file_exists($temporaryFileName)
            && $thumbnailUrl = ($this->getOEmbedData($mediaId)['thumbnail_url'] ?? false)
        ) {
            $previewImage = GeneralUtility::getUrl($thumbnailUrl);
            if (is_string($previewImage) && $previewImage !== '') {
                file_put_contents($temporaryFileName, $previewImage);
                GeneralUtility::fixPermissions($temporaryFileName);
            }
        }
        return $temporaryFileName;
    }
}
