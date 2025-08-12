<?php

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'sys_file_reference',
    [
        'start' => [
            'exclude' => true,
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
            'label' => 'LLL:EXT:twentythree/Resources/Private/Language/locallang.xlf:sys_file_reference.start.label',
            'config' => [
                'type' => 'number',
                'size' => 10,
                'nullable' => true,
                'placeholder' => '__row|uid_local|metadata|start',
                'mode' => 'useOrOverridePlaceholder',
                'default' => null,
            ],
        ],
    ]
);

$GLOBALS['TCA']['sys_file_reference']['palettes']['videoOverlayPalette']['showitem'] .= ',start';
