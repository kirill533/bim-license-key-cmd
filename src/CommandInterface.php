<?php
/**
 * BIM CMD Line Tool for license key validation.
 *
 * @copyright Copyright (c) Kyrylo Kostiukov 2023 - All Rights Reserved
 * @license MIT
 * @project cmd-license-key-cmd
 */

namespace CmdLicenseKey;


interface CommandInterface
{
    /**
     * @param \Commando\Command $cmd
     * @return mixed
     */
    public function run($cmd);

    /**
     * @return \Commando\Command
     */
    public function help($cmd);
}