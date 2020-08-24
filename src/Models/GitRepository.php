<?php

namespace ThisIsDevelopment\GitManager\Models;

use ThisIsDevelopment\GitManager\Contracts\GitFileInterface;
use ThisIsDevelopment\GitManager\Contracts\GitPlatformInterface;
use ThisIsDevelopment\GitManager\Contracts\GitRepositoryInterface;

abstract class GitRepository extends AbstractGitModel implements GitRepositoryInterface
{
    protected static array $properties = [
        'id',
        'name',
        'description',
        'namespace',
        'clone_url_ssh',
        'clone_url_http',
    ];
    protected static array $updatable = [
        'name' => true,
        'description' => false,
    ];
    protected GitPlatformInterface $platform;

    public function __construct(GitPlatformInterface $platform, array $properties)
    {
        $this->platform = $platform;
        $this->hydrate($properties);
    }

    public function addFile(string $file, string $branch, string $message, string $contents): GitFileInterface
    {
        $file = $this->getFile($file, $branch, true);
        $file->update($message, ['contents' => $contents]);
        return $file;
    }

    public function update(array $properties): void
    {
        self::validateUpdate($properties);
        $this->hydrate($properties);
        $this->doUpdate();
    }

    abstract public function doUpdate(): void;
}
