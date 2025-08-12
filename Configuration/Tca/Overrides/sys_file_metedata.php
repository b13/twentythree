<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'sys_file_metadata',
    [
        'start' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:twentythree/Resources/Private/Language/locallang.xlf:sys_file_metadata.start.label',
            'config' => [
                'type' => 'number',
                'size' => 10,
                'default' => 0,
            ],
        ],
    ]
);

 \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
     'sys_file_metadata',
     'start',
     TYPO3\CMS\Core\Resource\FileType::VIDEO->value,
     'after:duration'
 );
