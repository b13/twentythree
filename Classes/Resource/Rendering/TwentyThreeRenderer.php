<?php

declare(strict_types=1);

namespace B13\TwentyThree\Resource\Rendering;

/*
 * This file is part of TYPO3 CMS-based extension "twentythree" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\TwentyThree\Resolver\ConfigurationResolver;
use B13\TwentyThree\Resource\TwentyThreeMedia;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperInterface;
use TYPO3\CMS\Core\Resource\OnlineMedia\Helpers\OnlineMediaHelperRegistry;
use TYPO3\CMS\Core\Resource\Rendering\FileRendererInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TwentyThreeRenderer implements FileRendererInterface
{
    private const PUBLIC_EMBED_URL_PATTERN = 'https://%s/v.ihtml/player.html?source=embed&%s';

    protected OnlineMediaHelperRegistry $onlineMediaHelperRegistry;
    protected ?OnlineMediaHelperInterface $onlineMediaHelper = null;

    public function __construct(OnlineMediaHelperRegistry $onlineMediaHelperRegistry)
    {
        $this->onlineMediaHelperRegistry = $onlineMediaHelperRegistry;
    }

    public function getPriority(): int
    {
        return 1;
    }

    public function canRender(FileInterface $file): bool
    {
        return (
                $file->getMimeType() === TwentyThreeMedia::MIME_TYPE
                || $file->getExtension() === TwentyThreeMedia::FILE_EXTENSION
            )
            && $this->getOnlineMediaHelper($file);
    }

    public function render(FileInterface $file, $width, $height, array $options = [], $usedPathsRelativeToCurrentScript = false): string
    {
        $options = $this->collectOptions($options, $file);
        $src = $this->createTwentyThreeUrl($options, $file);
        $attributes = $this->collectIframeAttributes((int)$width, (int)$height, $options);

        return sprintf(
            '<iframe src="%s"%s></iframe>',
            $src,
            $attributes === [] ? '' : ' ' . $this->implodeAttributes($attributes)
        );
    }

    protected function getOnlineMediaHelper(FileInterface $file): ?OnlineMediaHelperInterface
    {
        if ($this->onlineMediaHelper === null) {
            $orgFile = $file;
            if ($orgFile instanceof FileReference) {
                $orgFile = $orgFile->getOriginalFile();
            }
            if ($orgFile instanceof File) {
                $this->onlineMediaHelper = $this->onlineMediaHelperRegistry->getOnlineMediaHelper($orgFile) ?: null;
            }
        }
        return $this->onlineMediaHelper;
    }

    protected function createTwentyThreeUrl($options, FileInterface $file): string
    {
        $origFile = $file instanceof FileReference ? $file->getOriginalFile() : $file;

        if (!$origFile instanceof File || ($onlineMediaHelper = $this->getOnlineMediaHelper($file)) === null) {
            return '';
        }

        $media = TwentyThreeMedia::createFromMediaId($onlineMediaHelper->getOnlineMediaId($origFile));

        $queryParameters = [
          'photo_id' => rawurlencode($media->getVideoId())
        ];

        if ($media->hasToken()) {
            $queryParameters['token'] = rawurlencode($media->getToken());
        }

        // Custom parameters: https://www.twentythree.com/help/embedding-your-videos#adding-custom-embed-parameters

        if ($options['autoplay'] ?? false) {
            $queryParameters['autoPlay'] = true;
            // If autoplay is enabled, enforce muted video, see https://developer.chrome.com/blog/autoplay/
            $queryParameters['mutedAutoPlay'] = true;
        }
        if ($options['loop'] ?? false) {
            $queryParameters['loop'] = true;
        }
        if ($options['autoMute'] ?? false) {
            $queryParameters['autoMute'] = true;
        }
        if ($options['start'] ?? false) {
            $queryParameters['start'] = $options['start'];
        }
        if ($options['defaultQuality'] ?? false) {
            $queryParameters['defaultQuality'] = $options['defaultQuality'];
        }
        if ($options['ambient'] ?? false) {
            $queryParameters['ambient'] = true;
        }

        if (isset($options['showDescriptions'])) {
            $queryParameters['showDescriptions'] = (int)(bool)$options['showDescriptions'];
        }
        if (isset($options['showLogo'])) {
            $queryParameters['showLogo'] = (int)(bool)$options['showLogo'];
        }
        if (isset($options['hideBigPlay'])) {
            $queryParameters['hideBigPlay'] = (int)(bool)$options['hideBigPlay'];
        }
        if (isset($options['socialSharing'])) {
            $queryParameters['socialSharing'] = (int)(bool)$options['socialSharing'];
        }
        if (isset($options['showBrowse'])) {
            $queryParameters['showBrowse'] = (int)(bool)$options['showBrowse'];
        }
        if (isset($options['showTray'])) {
            $queryParameters['showTray'] = (int)(bool)$options['showTray'];
        }

        return sprintf(
            self::PUBLIC_EMBED_URL_PATTERN,
            $media->getVideoDomain(),
            http_build_query($queryParameters)
        );
    }

    protected function collectOptions(array $options, FileInterface $file): array
    {

        // Check for an autoplay option at the file reference itself, if not overridden yet.
        if (!isset($options['autoplay']) && $file instanceof FileReference) {
            $autoplay = $file->getProperty('autoplay');
            if ($autoplay !== null) {
                $options['autoplay'] = $autoplay;
            }
        }

        if (!isset($options['allow'])) {
            $options['allow'] = 'fullscreen';
            if (!empty($options['autoplay'])) {
                $options['allow'] = 'autoplay; fullscreen';
            }
        }
        return $options;
    }

    protected function collectIframeAttributes($width, $height, array $options): array
    {
        $attributes = [];
        $attributes['allowfullscreen'] = true;

        if (is_array($options['additionalAttributes'] ?? false)) {
            $attributes = array_merge($attributes, $options['additionalAttributes']);
        }
        if (is_array($options['data'] ?? false)) {
            array_walk($options['data'], static function ($value, $key) use (&$attributes) {
                $attributes['data-' . $key] = $value;
            });
        }
        if ((int)$width > 0) {
            $attributes['width'] = (int)$width;
        }
        if ((int)$height > 0) {
            $attributes['height'] = (int)$height;
        }
        if ($this->shouldIncludeFrameBorderAttribute()) {
            $attributes['frameborder'] = 0;
        }
        foreach (['class', 'dir', 'id', 'lang', 'style', 'title', 'accesskey', 'tabindex', 'onclick', 'poster', 'preload', 'allow'] as $key) {
            if ($options[$key] ?? false) {
                $attributes[$key] = $options[$key];
            }
        }

        return $attributes;
    }

    /**
     * @internal
     */
    protected function implodeAttributes(array $attributes): string
    {
        $list = [];
        foreach ($attributes as $name => $value) {
            $name = preg_replace('/[^\p{L}0-9_.-]/u', '', $name);
            if ($value === true) {
                $list[] = $name;
            } else {
                $list[] = $name . '="' . htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5) . '"';
            }
        }
        return implode(' ', $list);
    }

    /**
     * HTML5 deprecated the "frameborder" attribute as everything should be done via styling.
     */
    protected function shouldIncludeFrameBorderAttribute(): bool
    {
        if ((new Typo3Version())->getMajorVersion() >= 12) {
            /** @phpstan-ignore-next-line */
            return GeneralUtility::makeInstance(PageRenderer::class)->getDocType()->shouldIncludeFrameBorderAttribute();
        }

        return is_object($GLOBALS['TSFE'] ?? null)
            && ($GLOBALS['TSFE']->config['config']['doctype'] ?? '') !== 'html5';
    }
}
