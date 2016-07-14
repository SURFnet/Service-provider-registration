<?php
/**
 * SAML 2.0 remote SP metadata for SimpleSAMLphp.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-sp-remote
 */
$metadata['https://serviceregistry.dev.support.surfconext.nl/module.php/saml/sp/metadata.php/default-sp'] = array (
    'SingleLogoutService' =>
        array (
            0 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'https://serviceregistry.dev.support.surfconext.nl/module.php/saml/sp/saml2-logout.php/default-sp',
                ),
        ),
    'AssertionConsumerService' =>
        array (
            0 =>
                array (
                    'index' => 0,
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'Location' => 'https://serviceregistry.dev.support.surfconext.nl/module.php/saml/sp/saml2-acs.php/default-sp',
                ),
            1 =>
                array (
                    'index' => 1,
                    'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post',
                    'Location' => 'https://serviceregistry.dev.support.surfconext.nl/module.php/saml/sp/saml1-acs.php/default-sp',
                ),
            2 =>
                array (
                    'index' => 2,
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                    'Location' => 'https://serviceregistry.dev.support.surfconext.nl/module.php/saml/sp/saml2-acs.php/default-sp',
                ),
            3 =>
                array (
                    'index' => 3,
                    'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01',
                    'Location' => 'https://serviceregistry.dev.support.surfconext.nl/module.php/saml/sp/saml1-acs.php/default-sp/artifact',
                ),
        ),
);

$metadata['https://dev.support.surfconext.nl/registration/simplesaml/module.php/saml/sp/metadata.php/default-sp'] = array (
    'SingleLogoutService' =>
        array (
            0 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'https://dev.support.surfconext.nl/registration/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
                ),
            1 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP',
                    'Location' => 'https://dev.support.surfconext.nl/registration/simplesaml/module.php/saml/sp/saml2-logout.php/default-sp',
                ),
        ),
    'AssertionConsumerService' =>
        array (
            0 =>
                array (
                    'index' => 0,
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'Location' => 'https://dev.support.surfconext.nl/registration/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
                ),
            1 =>
                array (
                    'index' => 1,
                    'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:browser-post',
                    'Location' => 'https://dev.support.surfconext.nl/registration/simplesaml/module.php/saml/sp/saml1-acs.php/default-sp',
                ),
            2 =>
                array (
                    'index' => 2,
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact',
                    'Location' => 'https://dev.support.surfconext.nl/registration/simplesaml/module.php/saml/sp/saml2-acs.php/default-sp',
                ),
            3 =>
                array (
                    'index' => 3,
                    'Binding' => 'urn:oasis:names:tc:SAML:1.0:profiles:artifact-01',
                    'Location' => 'https://dev.support.surfconext.nl/registration/simplesaml/module.php/saml/sp/saml1-acs.php/default-sp/artifact',
                ),
        ),
);
