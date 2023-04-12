<?php
/**
 * BIM CMD Line Tool for license key validation.
 *
 * @copyright Copyright (c) Kyrylo Kostiukov 2023 - All Rights Reserved
 * @license MIT
 * @project cmd-license-key-cmd
 */

namespace CmdLicenseKey\Command;


use CmdLicenseKey\CommandInterface;
use LicenseKey\Exception\LicenseDomainException;
use LicenseKey\Exception\LicenseExpiredException;
use LicenseKey\Exception\LicenseKeyInvalidException;
use LicenseKey\Exception\LicenseKeyMalformedException;
use LicenseKey\Exception\LicenseKeyVersionInvalidException;
use LicenseKey\Exception\LicenseKeyWarningException;
use LicenseKey\Exception\MissingComponentException;
use LicenseKey\LicenseKey;

class VerifyKey implements CommandInterface
{
    const COMMAND_NAME = 'verify-key';
    const COMMAND_DESCRIPTION = 'Verification license.';

    /**
     * @param \Commando\Command $cmd
     * @return string
     * @throws LicenseDomainException
     * @throws LicenseExpiredException
     * @throws LicenseKeyInvalidException
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     * @throws LicenseKeyWarningException
     * @throws MissingComponentException
     */
    public function run($cmd)
    {
        $offlineKeyPath = $cmd['offline-key'];
        $onlineKey = $cmd['online-key'];
        $onlineServerUrl = $cmd['online-key-server-url'];

        if ($offlineKeyPath === null && $onlineKey === null) {
            throw new \Exception(sprintf(
                "The %s command uses one of the options [--offline-key ''|--online-key '']." .
                " Please, specify one of the options or use argument --help.", self::COMMAND_NAME
            ));
        }

        $licenseText = '';

        if ($offlineKeyPath !== null && $onlineKey === null) {
            if (!file_exists($offlineKeyPath)) {
                throw new \Exception(sprintf('File %s has not found.', $cmd['offline-key']));
            }

            $licenseText = file_get_contents($offlineKeyPath);
        }

        if ($offlineKeyPath === null && $onlineKey !== null) {
            if ($onlineKey === '') {
                throw new \Exception("Option --online-key should not contain empty string.");
            }

            if ($onlineServerUrl === '') {
                throw new \Exception("Option --online-key-server-url should not contain empty string.");
            }

            if ($onlineServerUrl === null) {
                throw new \Exception("Please, specify --online-key-server-url parameter.");
            }

            $urlOnlineKeys = $onlineServerUrl . '/online-license-keys/';

            $headers = get_headers($urlOnlineKeys);
            if (!strpos($headers[0], '200')) {
                throw new \Exception("The path of --online-key-server-url not found.");
            }

            $url = $onlineServerUrl . '/online-license-keys/' . $onlineKey . '.lic';
            $headers = get_headers($url);
            if (!strpos($headers[0], '200')) {
                throw new \Exception("The key {$onlineKey} not found.");
            }

            $remoteLicenseKey = file_get_contents($url);
            $licenseText = $remoteLicenseKey;
        }

        $domain = $cmd['domain'];
        $platform = $cmd['platform'];
        $software = $cmd['software'];
        $software_version = $cmd['software-version'];

        if ($domain === '' || $domain === null) {
            throw new \Exception("Option --domain should not contain empty string.");
        }

        if ($platform === '' || $platform === null) {
            throw new \Exception("Option --platform should not contain empty string.");
        }

        if ($software === '' || $software === null) {
            throw new \Exception("Option --software should not contain empty string.");
        }

        if ($software_version === '' || $software_version === null) {
            throw new \Exception("Option --software-version should not contain empty string.");
        }

        $licenseKey = new LicenseKey();
        $licenseKey->verifyKey($licenseText, $domain, $platform, $software, $software_version);

        return "License key is valid.\n";
    }

    /**
     * @param $cmd
     * @return \Commando\Command|void
     */
    public function help($cmd)
    {
        if ($cmd === '--help') {
            $description = "\n";
            $description .= "\033[0;33mUsage\033[0m: ./bin/bim_license " . self::COMMAND_NAME . " [--offline-key ''|--online-key ''  --online-key-server-url ''] --domain '' --platform '' --software '' --software-version ''";
            $description .= "\n";
            $description .= "\033[0;33mHelp\033[0m: " . self::COMMAND_DESCRIPTION . "\n";
            $description .= "\n";

            echo $description;
        }
    }
}