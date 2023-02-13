<?php
/**
 * BIM CMD Line Tool for license key validation.
 *
 * @copyright Copyright (c) Kyrylo Kostiukov 2023 - All Rights Reserved
 * @license MIT
 * @project cmd-license-key-cmd
 */

namespace CmdLicenseKey;


class Shell
{
    /**
     * @param $commandName
     * @return \CmdLicenseKey\CommandInterface
     * @throws \Exception
     */
    public static function execution($commandName)
    {
        $command = '';

        switch ($commandName) {
            case \CmdLicenseKey\Command\KeyInfo::COMMAND_NAME:
                $command = new \CmdLicenseKey\Command\KeyInfo();
                break;
            case \CmdLicenseKey\Command\VerifyKey::COMMAND_NAME:
                $command = new \CmdLicenseKey\Command\VerifyKey();
                break;
        }

        if (empty($command) && !empty($commandName)) {
            throw new \Exception(sprintf('Command %s is not supported', $commandName));
        }

        if (empty($command)) {
            $command = new \CmdLicenseKey\Command\ListOfCommands();

            echo "\n";

            echo "\033[0;32mUsage\033[0m: ./bin/bim_license command --arg '' \n";
            echo "\033[0;32mHelp\033[0m: ./bin/bim_license command --help \n";

            echo "\n";
        }

        return $command;
    }
}