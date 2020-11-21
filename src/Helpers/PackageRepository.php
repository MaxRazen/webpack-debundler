<?php

namespace Helpers;

class PackageRepository
{
    protected $packages = [];

    public function setPackage(string $packageName)
    {
        array_push($this->packages, $packageName);

        return $this;
    }

    public function getPackages(): array
    {
        return $this->packages;
    }

    public function savePackages(string $path)
    {
        $packages = json_encode([
            'dependencies' => array_unique($this->packages)
        ]);

        file_put_contents("{$path}/packages.json", $packages);
    }
}
