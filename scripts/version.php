<?php

class VersionUpdater
{
    private string $composerFile;

    private array $composerJson;

    private string $currentVersion;

    public function __construct()
    {
        $this->composerFile = dirname(__DIR__) . '/composer.json';
        $this->composerJson = json_decode(file_get_contents($this->composerFile), true);
        $this->currentVersion = $this->composerJson['version'] ?? '1.0.0';
    }

    public function update(string $type): void
    {
        $newVersion = $this->incrementVersion($type);

        // Update composer.json
        $this->composerJson['version'] = $newVersion;
        file_put_contents(
            $this->composerFile,
            json_encode($this->composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
        );

        // Create git tag
        $this->createGitTag($newVersion);

        echo "Version updated to $newVersion\n";
        echo "Don't forget to push your changes:\n";
        echo "    git push && git push --tags\n";
    }

    private function incrementVersion(string $type): string
    {
        $parts = explode('.', $this->currentVersion);

        switch ($type) {
            case 'major':
                $parts[0]++;
                $parts[1] = 0;
                $parts[2] = 0;
                break;
            case 'minor':
                $parts[1]++;
                $parts[2] = 0;
                break;
            case 'patch':
                $parts[2]++;
                break;
            default:
                throw new InvalidArgumentException('Invalid version type. Use major, minor, or patch.');
        }

        return implode('.', $parts);
    }

    private function createGitTag(string $version): void
    {
        // Check if we're in a git repository
        if (!is_dir(dirname(__DIR__) . '/.git')) {
            echo "Warning: Not a git repository\n";

            return;
        }

        $version = 'v' . $version;

        // Create tag locally only
        exec('git add composer.json');
        exec(sprintf('git commit -m "Version bump to %s"', $version));
        exec(sprintf('git tag -a %s -m "Version %s"', escapeshellarg($version), escapeshellarg($version)));
    }
}

// Get the version type from arguments
$type = $argv[count($argv) - 1] ?? null;

// Validate version type
if (!in_array($type, ['major', 'minor', 'patch'])) {
    exit("Usage: composer version-[major|minor|patch]\n");
}

$updater = new VersionUpdater();
$updater->update($type);
