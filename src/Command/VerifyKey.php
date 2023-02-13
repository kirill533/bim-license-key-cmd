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
use CmdLicenseKey\Key;

class VerifyKey implements CommandInterface
{
    const COMMAND_NAME = 'verify-key';
    const COMMAND_DESCRIPTION = 'Verification license.';

    /**
     * @param \Commando\Command $cmd
     * @return string
     * @throws \CmdLicenseKey\Exception\LicenseDomainException
     * @throws \CmdLicenseKey\Exception\LicenseExpiredException
     * @throws \CmdLicenseKey\Exception\LicenseKeyInvalidException
     * @throws \CmdLicenseKey\Exception\LicenseKeyMalformedException
     * @throws \CmdLicenseKey\Exception\LicenseKeyVersionInvalidException
     * @throws \CmdLicenseKey\Exception\LicenseKeyWarningException
     * @throws \CmdLicenseKey\Exception\MissingComponentException
     */
    public function run($cmd)
    {
        $offlineKeyPath = $cmd['offline-key'];
        $onlineKey = $cmd['online-key'];

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

            $licenseText = $onlineKey;
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

        $key = new Key($software, $licenseText);

        $key->validate($domain, $platform, $software_version);

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
            $description .= "\033[0;33mUsage\033[0m: ./bin/bim_license " . self::COMMAND_NAME . " [--offline-key ''|--online-key ''] --domain '' --platform '' --software '' --software-version ''";
            $description .= "\n";
            $description .= "\033[0;33mHelp\033[0m: " . self::COMMAND_DESCRIPTION . "\n";
            $description .= "\n";

            echo $description;
        }
    }
}