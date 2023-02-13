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

class ListOfCommands implements CommandInterface
{
    /**
     * @param \Commando\Command $cmd
     * @return mixed|void
     */
    public function run($cmd)
    {
        $headers = ['command', 'description'];

        $rows[] = [KeyInfo::COMMAND_NAME, KeyInfo::COMMAND_DESCRIPTION];
        $rows[] = [VerifyKey::COMMAND_NAME, VerifyKey::COMMAND_DESCRIPTION];

        sort($rows);

        $table = new \cli\Table($headers, $rows);

        return $table->display();
    }

    public function help($cmd)
    {
        // TODO: Implement help() method.
    }
}