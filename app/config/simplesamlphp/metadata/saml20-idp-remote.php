<?php
/**
 * SAML 2.0 remote IdP metadata for simpleSAMLphp.
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

$metadata['https://engine.surfconext.nl/authentication/idp/metadata/vo:managementvo'] = array(
    'entityid'                  => 'https://engine.surfconext.nl/authentication/idp/metadata/vo:managementvo',
    'description'               =>
        array(
            'en' => 'SURFnet BV',
        ),
    'OrganizationName'          =>
        array(
            'en' => 'SURFnet BV',
        ),
    'name'                      =>
        array(
            'nl' => 'SURFconext | SURFnet',
            'en' => 'SURFconext | SURFnet',
        ),
    'OrganizationDisplayName'   =>
        array(
            'en' => 'SURFnet',
        ),
    'url'                       =>
        array(
            'en' => 'http://www.surfnet.nl',
        ),
    'OrganizationURL'           =>
        array(
            'en' => 'http://www.surfnet.nl',
        ),
    'contacts'                  =>
        array(
            0 =>
                array(
                    'contactType'  => 'administrative',
                    'givenName'    => 'SURFconext support',
                    'emailAddress' =>
                        array(
                            0 => 'support@surfconext.nl',
                        ),
                ),
            1 =>
                array(
                    'contactType'  => 'technical',
                    'givenName'    => 'SURFconext support',
                    'emailAddress' =>
                        array(
                            0 => 'support@surfconext.nl',
                        ),
                ),
            2 =>
                array(
                    'contactType'  => 'support',
                    'givenName'    => 'SURFconext support',
                    'emailAddress' =>
                        array(
                            0 => 'help@surfconext.nl',
                        ),
                ),
        ),
    'metadata-set'              => 'saml20-idp-remote',
    'SingleSignOnService'       =>
        array(
            0 =>
                array(
                    'Binding'  => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
                    'Location' => 'https://engine.surfconext.nl/authentication/idp/single-sign-on/vo:managementvo',
                ),
        ),
    'SingleLogoutService'       =>
        array(),
    'ArtifactResolutionService' =>
        array(),
    'keys'                      =>
        array(
            0 =>
                array(
                    'encryption'      => false,
                    'signing'         => true,
                    'type'            => 'X509Certificate',
                    'X509Certificate' => 'MIID3zCCAsegAwIBAgIJAMVC9xn1ZfsuMA0GCSqGSIb3DQEBCwUAMIGFMQswCQYD
VQQGEwJOTDEQMA4GA1UECAwHVXRyZWNodDEQMA4GA1UEBwwHVXRyZWNodDEVMBMG
A1UECgwMU1VSRm5ldCBCLlYuMRMwEQYDVQQLDApTVVJGY29uZXh0MSYwJAYDVQQD
DB1lbmdpbmUuc3VyZmNvbmV4dC5ubCAyMDE0MDUwNTAeFw0xNDA1MDUxNDIyMzVa
Fw0xOTA1MDUxNDIyMzVaMIGFMQswCQYDVQQGEwJOTDEQMA4GA1UECAwHVXRyZWNo
dDEQMA4GA1UEBwwHVXRyZWNodDEVMBMGA1UECgwMU1VSRm5ldCBCLlYuMRMwEQYD
VQQLDApTVVJGY29uZXh0MSYwJAYDVQQDDB1lbmdpbmUuc3VyZmNvbmV4dC5ubCAy
MDE0MDUwNTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAKthMDbB0jKH
efPzmRu9t2h7iLP4wAXr42bHpjzTEk6gttHFb4l/hFiz1YBI88TjiH6hVjnozo/Y
HA2c51us+Y7g0XoS7653lbUN/EHzvDMuyis4Xi2Ijf1A/OUQfH1iFUWttIgtWK9+
fatXoGUS6tirQvrzVh6ZstEp1xbpo1SF6UoVl+fh7tM81qz+Crr/Kroan0UjpZOF
TwxPoK6fdLgMAieKSCRmBGpbJHbQ2xxbdykBBrBbdfzIX4CDepfjE9h/40ldw5jR
n3e392jrS6htk23N9BWWrpBT5QCk0kH3h/6F1Dm6TkyG9CDtt73/anuRkvXbeygI
4wml9bL3rE8CAwEAAaNQME4wHQYDVR0OBBYEFD+Ac7akFxaMhBQAjVfvgGfY8hNK
MB8GA1UdIwQYMBaAFD+Ac7akFxaMhBQAjVfvgGfY8hNKMAwGA1UdEwQFMAMBAf8w
DQYJKoZIhvcNAQELBQADggEBAC8L9D67CxIhGo5aGVu63WqRHBNOdo/FAGI7LURD
FeRmG5nRw/VXzJLGJksh4FSkx7aPrxNWF1uFiDZ80EuYQuIv7bDLblK31ZEbdg1R
9LgiZCdYSr464I7yXQY9o6FiNtSKZkQO8EsscJPPy/Zp4uHAnADWACkOUHiCbcKi
UUFu66dX0Wr/v53Gekz487GgVRs8HEeT9MU1reBKRgdENR8PNg4rbQfLc3YQKLWK
7yWnn/RenjDpuCiePj8N8/80tGgrNgK/6fzM3zI18sSywnXLswxqDb/J+jgVxnQ6
MrsTf1urM8MnfcxG/82oHIwfMh/sXPCZpo+DTLkhQxctJ3M=
',
                ),
        ),
    'UIInfo'                    =>
        array(
            'DisplayName'         =>
                array(
                    'nl' => 'SURFconext | SURFnet',
                    'en' => 'SURFconext | SURFnet',
                ),
            'Description'         =>
                array(
                    'nl' => 'SURFconext',
                    'en' => 'SURFconext',
                ),
            'InformationURL'      =>
                array(),
            'PrivacyStatementURL' =>
                array(),
            'Keywords'            =>
                array(
                    'nl' =>
                        array(
                            0 => 'SURFconext',
                            1 => 'engine',
                        ),
                    'en' =>
                        array(
                            0 => 'SURFconext',
                            1 => 'engine',
                        ),
                ),
            'Logo'                =>
                array(
                    0 =>
                        array(
                            'url'    => 'https://static.surfconext.nl/media/idp/surfconext.png',
                            'height' => 40,
                            'width'  => 63,
                        ),
                ),
        ),
);

$metadata['https://engine.connect.surfconext.nl/authentication/idp/metadata'] = array(
    'name' => array(
        'en' => 'SURFconext Connect',
        'nl' => 'SURFconext Aansluitomgeving',
    ),
    'SingleSignOnService'  => 'https://engine.connect.surfconext.nl/authentication/idp/single-sign-on',
    'certFingerprint'      => array('25:72:85:66:C9:94:22:98:36:84:11:E1:88:C7:AC:40:98:F9:E7:82'),
);
