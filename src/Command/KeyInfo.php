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
use Commando\Command;
use LicenseKey\Exception\LicenseKeyMalformedException;
use LicenseKey\Exception\LicenseKeyVersionInvalidException;
use LicenseKey\LicenseKey;

class KeyInfo implements CommandInterface
{
    const COMMAND_NAME = 'key-info';
    const COMMAND_DESCRIPTION = 'Describes information about license.';

    /**
     * @param Command $cmd
     * @return mixed|void
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     */
    public function run($cmd)
    {
        $offlineKeyPath = $cmd['offline-key'];
        $onlineKey = $cmd['online-key'];

        if ($offlineKeyPath === null && $onlineKey === null) {
            throw new \Exception(sprintf(
                "The %s command uses one of the options [--offline-key ''|--online-key '']." .
                " Please, specify one of the options and run command again.", self::COMMAND_NAME
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

        $licenseKey = new LicenseKey();
        $keyData = $licenseKey->keyInfo($licenseText);

        $platform = $keyData['platform'];
        $expiry = $keyData['expiry'];
        $modules = $keyData['modules'];

        echo "\n";
        echo "\033[0m License for platform " . strtoupper($platform) . ". Valid until $expiry. \n";
        echo "\033[0m Software packages list: \n";

        foreach ($modules as $module) {
            echo "\033[0m - ". $module[0] ." (version ~". $module[1] .") \n";
        }

        echo "\n";
    }

    /**
     * @param $cmd
     * @return Command|void
     */
    public function help($cmd)
    {
        if ($cmd === '--help') {
            $description = "\n";
            $description .= "\033[0;33mUsage\033[0m: ./bin/bim_license " . self::COMMAND_NAME . " [--offline-key ''|--online-key '']";
            $description .= "\n";
            $description .= "\033[0;33mHelp\033[0m: " . self::COMMAND_DESCRIPTION . "\n";
            $description .= "\n";

            echo $description;
        }
    }
}