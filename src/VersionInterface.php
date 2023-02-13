<?php
/**
 * BIM CMD Line Tool for license key validation.
 *
 * @copyright Copyright (c) Kyrylo Kostiukov 2023 - All Rights Reserved
 * @license MIT
 * @project cmd-license-key-cmd
 */

namespace CmdLicenseKey;


interface VersionInterface
{
    /**
     * @return boolean
     */
    public function isValidKey();

    /**
     * @param string $domain
     * @return boolean
     */
    public function isValidDomain($domain);

    /**
     * @param string $platformCode
     * @return boolean
     */
    public function isValidPlatform($platformCode);

    /**
     * @param string $moduleName
     * @return int timestamp
     */
    public function getExpiryDate($moduleName);

    /**
     * @param string $moduleName
     * @return string
     */
    public function getLicenseType($moduleName);

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleVersion($moduleName);

    /**
     * Get Time of License Key creation
     *
     * @return int - timestamp
     */
    public function getCreatedAt();

    /**
     * @param VersionInterface $version
     * @return boolean
     */
    public function equalsTo($version);

    /**
     * @return array
     */
    public function dumpKeyInfo();
}