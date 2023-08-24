<?php

use B13\TwentyThree\Exception\ConfigurationException;
use B13\TwentyThree\Resolver\ConfigurationResolver;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Directive;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Mutation;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationCollection;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\MutationMode;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\Scope;
use TYPO3\CMS\Core\Security\ContentSecurityPolicy\UriValue;
use TYPO3\CMS\Core\Type\Map;

try {
    $twentyThreeVideoDomain = new UriValue(ConfigurationResolver::resolveVideoDomain());
    $twentyThreeConfiguration = new MutationCollection(
        new Mutation(
            MutationMode::Extend,
            Directive::FrameSrc,
            $twentyThreeVideoDomain,
        ),
    );
    return Map::fromEntries(
        [Scope::backend(), $twentyThreeConfiguration],
        [Scope::frontend(), $twentyThreeConfiguration],
    );
} catch (ConfigurationException $e) {
    return [];
}
