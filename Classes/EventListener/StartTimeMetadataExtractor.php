<?php

declare(strict_types=1);

namespace B13\TwentyThree\EventListener;

/*
 * This file is part of TYPO3 CMS-based extension "twentythree" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\TwentyThree\Resource\TwentyThreeMedia;
use TYPO3\CMS\Core\Resource\Event\AfterFileAddedEvent;
use TYPO3\CMS\Core\Resource\File;

/**
 * Extracts start time parameter from TwentyThree media files and updates metadata.
 * 
 * This event listener processes files added via the AfterFileAddedEvent to:
 * - Extract "start=XXX" parameters from the file content
 * - Update the file's metadata with the extracted start time
 * - Remove the start parameter from the file content after processing
 */
final class StartTimeMetadataExtractor
{
    public function __invoke(AfterFileAddedEvent $event): void
    {
        $file = $event->getFile();

        // Only process TwentyThree files
        if ($file instanceof File === false || $file->getExtension() !== TwentyThreeMedia::FILE_EXTENSION) {
            return;
        }

        $fileContent = $file->getContents();

        // Check if the file content contains a start parameter
        if (preg_match('/\?start=(\d+)/', $fileContent, $matches)) {
            // Update file metadata with start time
            $file->getMetaData()->add(['start' => (int)$matches[1]])->save();
            // Update file with clean content (without start parameter)
            $cleanContent = preg_replace('/\?start=\d+/', '', $fileContent);
            $file->getStorage()->setFileContents($file, $cleanContent);
        }
    }
}
