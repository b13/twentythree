<?php

use B13\TwentyThree\Resource\OnlineMedia\Helpers\TwentyThreeHelper;
use B13\TwentyThree\Resource\Rendering\TwentyThreeRenderer;
use B13\TwentyThree\Resource\TwentyThreeMedia;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Resource\Rendering\RendererRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') or die();

if ($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['twentythree']['videoDomain'] ?? false) {
    // Only register media helper / renderer if videoDomain is set
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['mediafile_ext'] .= ',' . TwentyThreeMedia::FILE_EXTENSION;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['FileInfo']['fileExtensionToMimeType'][TwentyThreeMedia::FILE_EXTENSION] = TwentyThreeMedia::MIME_TYPE;
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['fal']['onlineMediaHelpers'][TwentyThreeMedia::FILE_EXTENSION] = TwentyThreeHelper::class;
    GeneralUtility::makeInstance(RendererRegistry::class)->registerRendererClass(TwentyThreeRenderer::class);
}

$iconRegistry = GeneralUtility::makeInstance(IconRegistry::class);
$iconRegistry->registerMimeTypeIcon(TwentyThreeMedia::MIME_TYPE, 'mimetypes-media-video-twentythree');
$iconRegistry->registerFileExtension(TwentyThreeMedia::FILE_EXTENSION, 'mimetypes-media-video-twentythree');
