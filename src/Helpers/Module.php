<?php

namespace Helpers;

class Module
{
    protected $moduleName;

    protected $sourceCode;

    public function setModuleName(string $name)
    {
        $this->moduleName = $name;
        return $this;
    }

    public function setModuleSource(string $source)
    {
        if ($this->isNodeModule()) {
            return $this;
        }

        $pattern = 'base64,';
        $base64 = substr($source, strpos($source, $pattern) + strlen($pattern));
        $sourceMap = json_decode(base64_decode($base64));
        $this->sourceCode = implode("\n\n", $sourceMap->sourcesContent);
        return $this;
    }

    public function dump(PackageRepository $repository)
    {
        if ($this->isNodeModule()) {
            $repository->setPackage(extractPackageName($this->moduleName));
            return;
        }
        try {
            $pathPart = extractPath($this->moduleName);
        } catch (\Exception $e) {
            var_dump($this->moduleName);
            die;
        }

        $moduleFileName = extractFileName($this->moduleName);

        prepareModuleDir($pathPart);

        file_put_contents(makeFilePath($pathPart) . "/{$moduleFileName}", $this->sourceCode);
    }

    public function isNodeModule(): bool
    {
        return strpos($this->moduleName, 'node_modules/') !== false;
    }
}
