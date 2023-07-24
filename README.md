# TwentyThree - Media provider for the TwentyThree Video Marketing Platform

Adds an online media provider for [TwentyThree](https://www.twentythree.com/) .

## Installation

Install this extension via `composer req b13/twentythree`.

You can also download the extension from the
[TYPO3 Extension Repository](https://extensions.typo3.org/extension/twentythree/)
and  activate it in the Extension Manager of your TYPO3 installation.

Note: This extension is compatible with TYPO3 v11 and v12.

### Configuration

You have to specify the video domain to be used for building the URLs. This
is especially important for TYPO3 v12, because the domain needs to be known
for CSP. The video domain can be configured in the extension configuration.
It's also possible to reference environment variables.

## Credits

This extension was created by Oliver Bartsch in 2023 for [b13 GmbH, Stuttgart](https://b13.com).

[Find more TYPO3 extensions we have developed](https://b13.com/useful-typo3-extensions-from-b13-to-you)
that help us deliver value in client projects. As part of the way we work,
we focus on testing and best practices to ensure long-term performance,
reliability, and results in all our code.
