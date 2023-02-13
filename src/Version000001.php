<?php
/**
 * BIM CMD Line Tool for license key validation.
 *
 * @copyright Copyright (c) Kyrylo Kostiukov 2023 - All Rights Reserved
 * @license MIT
 * @project cmd-license-key-cmd
 */

namespace CmdLicenseKey;


class Version000001 implements VersionInterface
{
    /**
     * @var array
     */
    protected $parts;

    /**
     * @var array|false
     */
    protected $data;

    /**
     * Version000001 constructor.
     * @param array $keyData
     */
    public function __construct($keyData)
    {
        $this->parts = $keyData;

        try {
            if (isset($this->parts[3])) {
                $this->data = unserialize(str_rot13($this->parts[3]));

                if (!is_array($this->data[1]) || !is_array($this->data[2])) {
                    throw new \Exception('Array expected');
                }

                foreach ($this->data[2] as $softwareItem) {
                    if (!is_array($softwareItem) && count($softwareItem) != 4) {
                        throw new \Exception('Array with 4 elements expected');
                    }
                }

                if (count($this->data[1]) != 5) {
                    throw new \Exception('Array with 5 elements expected');
                }
            } else {
                $this->data = false;
            }
        } catch (\Exception $e) {
            $this->data = false;
        }
    }

    /**
     * @return bool
     */
    public function isValidKey()
    {
        try {
            if ($this->data === false) {
                return false;
            }

            if (count($this->data) !== 3) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param string $domain
     * @return bool
     */
    public function isValidDomain($domain)
    {
        $hash = substr(hash('sha256', trim($domain)), 0, 6);

        return in_array($hash, $this->data[1]);
    }

    /**
     * @param string $platformCode
     * @return bool
     */
    public function isValidPlatform($platformCode)
    {
        return $this->data[0] == $platformCode;
    }

    /**
     * @param string $moduleName
     * @return int|mixed
     * @throws \Exception
     */
    public function getExpiryDate($moduleName)
    {
        foreach ($this->data[2] as $module) {
            if ($module[0] == $moduleName) {
                $dateYMD = $module[3];
                $date = implode('-', [
                    substr($dateYMD, 0, 4),
                    substr($dateYMD, 4, 2),
                    substr($dateYMD, 6, 2)
                ]);

                $dateTime = new \DateTime($date);

                return $dateTime->getTimestamp();
            }
        }

        return null;
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getLicenseType($moduleName)
    {
        foreach ($this->data[2] as $module) {
            if ($module[0] == $moduleName) {
                return $module[2];
            }
        }

        return null;
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleVersion($moduleName)
    {
        foreach ($this->data[2] as $module) {
            if ($module[0] == $moduleName) {
                return $module[1];
            }
        }

        return null;
    }

    /**
     * Get Time of License Key creation
     *
     * @return int - timestamp
     */
    public function getCreatedAt()
    {
        if (isset($this->parts[2])) {
            return $this->parts[2];
        }

        return 0;
    }

    /**
     * @param VersionInterface $version
     * @return bool
     */
    public function equalsTo($version)
    {
        if (! ($version instanceof Version000001)) {
            return false;
        }

        $partsA = $this->parts;
        $partsB = $version->parts;

        unset($partsA[2]);
        unset($partsB[2]);

        return serialize($partsA) == serialize($partsB);
    }

    /**
     * @return array|false
     */
    public function dumpKeyInfo()
    {
        return $this->data;
    }
}