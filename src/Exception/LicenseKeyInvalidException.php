<?php
/**
 * BIM CMD Line Tool for license key validation.
 *
 * @copyright Copyright (c) Kyrylo Kostiukov 2023 - All Rights Reserved
 * 
 * @license MIT
 * @project cmd-license-key-cmd
 * 
 */

namespace CmdLicenseKey\Exception;

class LicenseKeyInvalidException extends \Exception
{
    const CODE_INVALID_SOFTWARE_VERSION = 566;
    const CODE_INVALID_DOMAIN = 567;
    const CODE_INVALID_SOFTWARE = 568;
    const CODE_EXPIRED = 570;
    const CODE_INVALID_PLATFORM = 569;
}