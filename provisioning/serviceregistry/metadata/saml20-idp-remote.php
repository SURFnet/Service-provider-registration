<?php
/**
 * SAML 2.0 remote IdP metadata for SimpleSAMLphp.
 *
 * Remember to remove the IdPs you don't use from this file.
 *
 * See: https://simplesamlphp.org/docs/stable/simplesamlphp-reference-idp-remote
 */

$metadata['https://serviceregistry.dev.support.surfconext.nl/saml2/idp/metadata.php'] = array (
    'metadata-set' => 'saml20-idp-remote',
    'entityid' => 'https://serviceregistry.dev.support.surfconext.nl/saml2/idp/metadata.php',
    'SingleSignOnService' =>
        array (
            0 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'https://serviceregistry.dev.support.surfconext.nl/saml2/idp/SSOService.php',
                ),
        ),
    'SingleLogoutService' =>
        array (
            0 =>
                array (
                    'Binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'https://serviceregistry.dev.support.surfconext.nl/saml2/idp/SingleLogoutService.php',
                ),
        ),
    'certData' => 'MIIDVzCCAj+gAwIBAgIJAK5kce8CSD3vMA0GCSqGSIb3DQEBBQUAMEIxCzAJBgNVBAYTAlhYMRUwEwYDVQQHDAxEZWZhdWx0IENpdHkxHDAaBgNVBAoME0RlZmF1bHQgQ29tcGFueSBMdGQwHhcNMTYwNzExMTQwMTUwWhcNMTcwNzExMTQwMTUwWjBCMQswCQYDVQQGEwJYWDEVMBMGA1UEBwwMRGVmYXVsdCBDaXR5MRwwGgYDVQQKDBNEZWZhdWx0IENvbXBhbnkgTHRkMIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAv2Tj0+Iyc4SKgiiWLidDwuBWn0jRrc/0J13e1E2cBO9AQKuDFzoMgYXqebLIOHQrE4c8zc3B/Nfx/zlMo9xrEQ2lOr9ykzx++NFDVdc6ESEb8UUc9QOkdbFcNquVlo2ta07+UpgKwxOWqep65QpFR3CaVX1FVe61Meo82q+zRTzt04AZz4DR8jAiCkUJLHhpDL0uXoRWgdIaYYrzUakGrm/Ibdbw8iYi+jw7MDoMdqULFoI40vgewh6D9vWKvgAlN6jEcLfnIyvpkcNoaROUNdtOYLTxKx85AM+uPFHL9Yricac0XDwHaM+tLQ2Z4ZgTWS2g/2t68Frxw3JnCglnxwIDAQABo1AwTjAdBgNVHQ4EFgQU3M8tvytbllY3b88v18TNmhaXh2owHwYDVR0jBBgwFoAU3M8tvytbllY3b88v18TNmhaXh2owDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOCAQEAYBX6Qw4SLCwmvlNiB2tWtd1eKn+1ATjcq+KIEr6+HQcfAENnBV3REvaOOmGDUG1qfx43A5BlKgQV2ayIW6rhoYwiLYtk8Q0zfhnrHqyZ2T3pCYAok8sb/4WXPRikRTsS8J+r+HWlBbW08uJu+wuRvNLuukMGMj0aJOOcKG9Cq9zI5QoQn2mFLAcpQ/BPSX27PHibNlIIQg44xJkzH2mufdXVUxxi+vHFxFDEqv5cAC5ISM571mhvxy62bH4dMRBalHenWRwe9Ymc4VP1JLBNY3Ji1t6jCumYlgq+XUUM/zWMDFjddQIRIF+VVdvmzwopQpGQ7VZjIZrU86PzJoSnUg==',
    'NameIDFormat' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
);
