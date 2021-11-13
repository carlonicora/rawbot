<?php
namespace CarloNicora\Minimalism\Raw;

use CarloNicora\Minimalism\Interfaces\DefaultServiceInterface;
use CarloNicora\Minimalism\Interfaces\ServiceInterface;
use Composer\InstalledVersions;
use PackageVersions\Versions;

class Raw implements ServiceInterface, DefaultServiceInterface
{
    /**
     * Raw constructor.
     * @param string $PUBLIC_KEY
     * @param string $APPLICATION_ID
     * @param string $DISCORD_ENDPOINT
     */
    public function __construct(
        private string $PUBLIC_KEY,
        private string $APPLICATION_ID,
        private string $DISCORD_ENDPOINT,
    )
    {
    }

    /**
     * @return string
     */
    public function getVersion(
    ): string
    {
        return InstalledVersions::getPrettyVersion(Versions::rootPackageName());
    }

    /**
     * @return string
     */
    public function getApiEndpoint(): string
    {
        return $this->DISCORD_ENDPOINT;
    }

    /**
     * @return string
     */
    public function getCommandEndpoint():string
    {
        return $this->DISCORD_ENDPOINT . '/applications/' . $this->APPLICATION_ID . '/commands';
    }

    /**
     * @return string
     */
    public function getPublicKey(
    ): string
    {
        return $this->PUBLIC_KEY;
    }

    /**
     * @return string
     */
    public function getApplicationId(): string
    {
        return $this->APPLICATION_ID;
    }

    /**
     * @return array
     */
    public function getDelayedServices(): array
    {
        return [];
    }

    /**
     *
     */
    public function initialise(): void
    {
    }

    /**
     *
     */
    public function destroy(): void
    {
    }

    public function getApplicationUrl(
    ): ?string
    {
        return null;
    }

    public function getApiUrl(
    ): ?string
    {
        return null;
    }
}