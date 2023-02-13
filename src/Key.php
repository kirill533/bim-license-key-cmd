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

namespace CmdLicenseKey;

use CmdLicenseKey\Exception\LicenseDomainException;
use CmdLicenseKey\Exception\LicenseExpiredException;
use CmdLicenseKey\Exception\LicenseKeyWarningException;
use CmdLicenseKey\Exception\MissingComponentException;
use CmdLicenseKey\Exception\LicenseKeyInvalidException;
use CmdLicenseKey\Exception\LicenseKeyVersionInvalidException;
use CmdLicenseKey\Exception\LicenseKeyMalformedException;

class Key
{
    const LICENSE_TYPE_TERM = 'term';
    const LICENSE_TYPE_DEMO = 'trial';
    const LICENSE_TYPE_PERPETUAL = 'perpetual';

    const PLATFORM_MAGE_1 = 'M1';
    const PLATFORM_MAGE_2 = 'M2';
    const PLATFORM_PAWBO = 'pawbo';

    /**
     * @var VersionInterface
     */
    protected $version = null;

    /**
     * @var string
     */
    protected $keyText;

    /**
     * @var string
     */
    private $keyData = null;

    /**
     * @var string
     */
    private $softwareCode;

    /**
     * Key constructor.
     * @param string $moduleName
     * @param string $key
     */
    public function __construct($moduleName, $key)
    {
        $this->keyText = $key;
        $this->softwareCode = $moduleName;
    }

    /**
     * @return array|mixed|string
     * @throws LicenseKeyMalformedException
     */
    protected function parseKey()
    {
        if ($this->keyData === null) {
            $key = $this->keyText;
            $key = $this->unwrapKey($key);

            try {
                $keyData = unserialize(base64_decode($key));
                // check hash
                if (empty($keyData) || !is_array($keyData)) {
                    throw new LicenseKeyMalformedException(
                        'License text is malformed.',
                        LicenseKeyMalformedException::CODE_WRONG_FORMAT
                    );
                }
                $this->keyData = $keyData;
            } catch (\Exception $e) {
                throw new LicenseKeyMalformedException(
                    'License text is malformed.',
                    LicenseKeyMalformedException::CODE_WRONG_FORMAT,
                    $e
                );
            }
        }

        return $this->keyData;
    }

    /**
     * @return void
     * @throws LicenseKeyMalformedException
     */
    public function validateKeyFormat()
    {
        $this->parseKey();
    }

    /**
     * @throws LicenseKeyInvalidException
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     */
    public function validateKeyModule()
    {
        if (
            $this->getLicenseType() === null ||
            $this->getExpiryDate() === null ||
            $this->getModuleVersion() === null
        ) {
            throw new LicenseKeyInvalidException(
                sprintf('Software %s is not covered by the license.', $this->softwareCode),
                LicenseKeyInvalidException::CODE_INVALID_SOFTWARE
            );
        }
    }

    /**
     * @param string $domain
     * @param string $platformCode
     * @param string $softwareVersion
     * @param bool $includeWarnings
     * @throws LicenseDomainException
     * @throws LicenseExpiredException
     * @throws LicenseKeyInvalidException
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     * @throws MissingComponentException
     * @throws LicenseKeyWarningException
     */
    public function validate($domain, $platformCode, $softwareVersion, $includeWarnings = false)
    {
        $this->validateKeyFormat();
        $this->validateKeyModule();

        $softwareCode = $this->softwareCode;

        if (!$this->isValidPlatform($platformCode)) {
            throw new LicenseKeyInvalidException(sprintf(
                'Platform %s is not covered by the license for %s.',
                $platformCode,
                $softwareCode
            ), LicenseKeyInvalidException::CODE_INVALID_PLATFORM);
        }

        $version = $this->getModuleVersion();

        if (version_compare($version, $softwareVersion, '>=')) {
            throw new LicenseKeyInvalidException(sprintf(
                'Software version %s is not covered by the license for %s. Supported version: %s.',
                $softwareVersion,
                $softwareCode,
                $version
            ), LicenseKeyInvalidException::CODE_INVALID_SOFTWARE_VERSION);
        }

        if (!$this->isValidDomain($domain)) {
            throw new LicenseDomainException(sprintf(
                'Domain %s is not covered by the license for %s.',
                $domain,
                $softwareCode
            ), LicenseKeyInvalidException::CODE_INVALID_DOMAIN);
        }

        if ($this->isExpired()) {
            throw new LicenseExpiredException("License expired.", LicenseKeyInvalidException::CODE_EXPIRED);
        }

        if ($includeWarnings) {
            if ($this->isExpiredAfterTwoWeeks()) {
                throw new LicenseKeyWarningException(
                    "License for some of the BIM extensions will be expired at " . date('d.m.Y', $this->getExpiryDate()) . ". Please, prolongate the license before the current license will be expired. You can order the license on www.bimproject.net.",
                    LicenseKeyWarningException::CODE_EXPIRED_AFTER_TWO_WEEKS
                );
            }
        }
    }

    /**
     * @param string $domain
     * @return boolean
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     */
    public function isValidDomain($domain)
    {
        $version = $this->getVersion();

        return $version->isValidDomain($domain);
    }

    /**
     * @param string $productCode
     * @return boolean
     * @throws LicenseKeyVersionInvalidException
     * @throws LicenseKeyMalformedException
     */
    public function isValidPlatform($productCode)
    {
        $version = $this->getVersion();

        return $version->isValidPlatform($productCode);
    }

    /**
     * @return string
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     */
    public function getLicenseType()
    {
        $version = $this->getVersion();

        return $version->getLicenseType($this->softwareCode);
    }

    /**
     * @return string
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     */
    public function getModuleVersion()
    {
        $version = $this->getVersion();

        return $version->getModuleVersion($this->softwareCode);
    }

    /**
     * @return int timestamp
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     */
    public function getExpiryDate()
    {
        $version = $this->getVersion();

        return $version->getExpiryDate($this->softwareCode);
    }

    /**
     * @return VersionInterface
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     */
    private function getVersion()
    {
        if ($this->version === null) {
            $this->parseKey();

            $key = $this->keyData;

            $versionNumber = $key[0];
            switch ($versionNumber) {
                case '000001':
                    $version = new Version000001($this->keyData);
                    break;
                default:
                    throw new LicenseKeyVersionInvalidException();
            }
            $this->version = $version;
        }

        return $this->version;
    }

    /**
     * @return boolean
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     * @throws MissingComponentException
     */
    public function isExpired()
    {
        if ($this->getLicenseType() == self::LICENSE_TYPE_PERPETUAL) {
            return false;
        }

        return $this->getExpiryDate() <= (int)gmdate('U');
    }

    /**
     * @return bool
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     * @throws MissingComponentException
     */
    public function isExpiredAfterTwoWeeks()
    {
        if ($this->getLicenseType() == self::LICENSE_TYPE_PERPETUAL) {
            return false;
        }

        $dateTimeExpiry = new \DateTime('@' . $this->getExpiryDate());
        $dateTimeCurrent = new \DateTime('@' . (int)gmdate('U'));

        $interval = intval(ceil($dateTimeExpiry->diff($dateTimeCurrent)->days / 7));

        return 2 >= $interval;
    }

    /**
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     */
    public function getCratedAt()
    {
        $version = $this->getVersion();

        return $version->getCreatedAt();
    }

    /**
     * @param Key $key
     * @return bool
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     */
    public function equalsTo($key)
    {
        return $this->getVersion()->equalsTo($key->getVersion());
    }

    /**
     * @return array
     * @throws LicenseKeyMalformedException
     * @throws LicenseKeyVersionInvalidException
     */
    public function dumpKeyInfo()
    {
        $this->parseKey();
        $version = $this->getVersion();

        return [
            'version' => $this->keyData[0],
            'createdAt' => $version->getCreatedAt(),
            'data' => $version->dumpKeyInfo()
        ];
    }

    /**
     * @param string $key
     * @return bool|mixed|string
     * @throws LicenseKeyMalformedException
     */
    protected function unwrapKey($key)
    {
        $key = str_replace("============ LICENSE BEGIN =============", "", $key);
        $key = str_replace("============= LICENSE END ==============", "", $key);

        $keyStrings = explode("\n", $key ?? '');
        foreach ($keyStrings as $k => $v) {
            $keyStrings[$k] = trim($v);
        }

        $key = implode("", $keyStrings);
        $hash = substr($key, 0, 40);
        $key = substr($key, 40);

        if (sha1($key) !== $hash) {
            throw new LicenseKeyMalformedException(
                'License text is malformed.',
                LicenseKeyMalformedException::CODE_WRONG_FORMAT
            );
        }

        return $key;
    }
}