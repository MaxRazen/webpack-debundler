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
        $json = json_encode([
            'dependencies' => array_values(array_unique($this->packages))
        ], JSON_PRETTY_PRINT);

        file_put_contents("{$path}/packages.json", stripslashes($json));
    }
}
