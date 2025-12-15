<?php

declare(strict_types=1);

namespace Xterr\CpvCodes\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command to build translation files from YAML to PHP arrays and generate framework-specific formats.
 */
class BuildTranslationsCommand extends Command
{
    /**
     * @var string
     */
    private $resourcesDir;

    /**
     * @var string
     */
    private $domain = 'cpvCodes';

    public function __construct()
    {
        parent::__construct('build:translations');
        $this->resourcesDir = dirname(__DIR__, 2) . '/Resources/translations';
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Build translation files for CPV Codes')
            ->addArgument(
                'action',
                InputArgument::REQUIRED,
                'Action to perform: php:from-yaml, yaml:generate, laravel:generate, all'
            )
            ->addOption(
                'locale',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Process only a specific locale (e.g., de, fr)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');
        $locale = $input->getOption('locale');

        switch ($action) {
            case 'php:from-yaml':
                $io->title('Converting YAML files to PHP arrays');
                $this->yamlToPhp($io, $locale);
                break;

            case 'yaml:generate':
                $io->title('Generating YAML files from PHP arrays');
                $this->phpToYaml($io, $locale);
                break;

            case 'laravel:generate':
                $io->title('Generating Laravel files from PHP arrays');
                $this->phpToLaravel($io, $locale);
                break;

            case 'all':
                $io->title('Generating all translation formats');
                $io->section('YAML');
                $this->phpToYaml($io, $locale);
                $io->section('Laravel');
                $this->phpToLaravel($io, $locale);
                break;

            default:
                $io->error("Unknown action: {$action}");
                $io->text('Available actions: php:from-yaml, yaml:generate, laravel:generate, all');
                return Command::FAILURE;
        }

        $io->success('Done!');
        return Command::SUCCESS;
    }

    /**
     * Convert YAML files to PHP arrays (one-time migration).
     *
     * @param SymfonyStyle $io
     * @param string|null $locale
     * @return void
     */
    private function yamlToPhp(SymfonyStyle $io, $locale): void
    {
        $phpDir = $this->resourcesDir . '/php';
        if (!is_dir($phpDir)) {
            mkdir($phpDir, 0755, true);
        }

        // Try both patterns: messages.*.yaml (old) and cpvCodes.*.yaml (new)
        $yamlFiles = glob($this->resourcesDir . '/messages.*.yaml') ?: [];
        if (empty($yamlFiles)) {
            $yamlFiles = glob($this->resourcesDir . '/' . $this->domain . '.*.yaml') ?: [];
        }

        if (empty($yamlFiles)) {
            $io->warning('No YAML files found to convert.');
            return;
        }

        foreach ($yamlFiles as $yamlFile) {
            $fileLocale       = $this->extractLocaleFromYaml($yamlFile);
            $normalizedLocale = $this->normalizeLocale($fileLocale);

            if ($locale !== null && $normalizedLocale !== $this->normalizeLocale($locale)) {
                continue;
            }

            $io->text("Converting: {$yamlFile}");

            $translations = $this->parseYaml($yamlFile);
            $phpFile      = "{$phpDir}/{$this->domain}.{$normalizedLocale}.php";

            $this->writePhpArray($phpFile, $translations, $normalizedLocale);
            $io->text("  -> {$phpFile} (" . count($translations) . " entries)");
        }
    }

    /**
     * Generate YAML files from PHP arrays.
     *
     * @param SymfonyStyle $io
     * @param string|null $locale
     * @return void
     */
    private function phpToYaml(SymfonyStyle $io, $locale): void
    {
        $yamlDir = $this->resourcesDir . '/yaml';
        if (!is_dir($yamlDir)) {
            mkdir($yamlDir, 0755, true);
        }

        $phpFiles = glob($this->resourcesDir . "/php/{$this->domain}.*.php") ?: [];

        if (empty($phpFiles)) {
            $io->warning('No PHP files found. Run php:from-yaml first.');
            return;
        }

        foreach ($phpFiles as $phpFile) {
            $fileLocale = $this->extractLocaleFromPhp($phpFile);

            if ($locale !== null && $fileLocale !== $locale) {
                continue;
            }

            $io->text("Generating YAML: {$phpFile}");

            $translations = require $phpFile;
            $yamlFile     = "{$yamlDir}/{$this->domain}.{$fileLocale}.yaml";

            $this->writeYaml($yamlFile, $translations);
            $io->text("  -> {$yamlFile}");
        }
    }

    /**
     * Generate Laravel-format files from PHP arrays.
     *
     * @param SymfonyStyle $io
     * @param string|null $locale
     * @return void
     */
    private function phpToLaravel(SymfonyStyle $io, $locale): void
    {
        $laravelDir = $this->resourcesDir . '/laravel/vendor/cpvcodes';

        $phpFiles = glob($this->resourcesDir . "/php/{$this->domain}.*.php") ?: [];

        if (empty($phpFiles)) {
            $io->warning('No PHP files found. Run php:from-yaml first.');
            return;
        }

        foreach ($phpFiles as $phpFile) {
            $fileLocale = $this->extractLocaleFromPhp($phpFile);

            if ($locale !== null && $fileLocale !== $locale) {
                continue;
            }

            $io->text("Generating Laravel: {$phpFile}");

            $translations = require $phpFile;
            $localeDir    = "{$laravelDir}/{$fileLocale}";

            if (!is_dir($localeDir)) {
                mkdir($localeDir, 0755, true);
            }

            $laravelFile = "{$localeDir}/{$this->domain}.php";
            $this->writeLaravelArray($laravelFile, $translations);
            $io->text("  -> {$laravelFile}");
        }
    }

    /**
     * Simple YAML parser for key-value format.
     *
     * @param string $file
     * @return array<string, string>
     */
    private function parseYaml($file): array
    {
        $content      = file_get_contents($file);
        $lines        = explode("\n", $content);
        $translations = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#') {
                continue;
            }

            // Match patterns like: 'Key': 'Value' or Key: Value
            if (preg_match("/^['\"]?(.+?)['\"]?:\s*(.+)$/", $line, $matches)) {
                $key   = $this->unquoteYaml(trim($matches[1]));
                $value = $this->unquoteYaml(trim($matches[2]));

                if (!empty($key)) {
                    $translations[$key] = $value;
                }
            }
        }

        return $translations;
    }

    /**
     * @param string $value
     * @return string
     */
    private function unquoteYaml($value): string
    {
        $value = trim($value);

        // Handle single quotes
        if (strlen($value) >= 2 && $value[0] === "'" && substr($value, -1) === "'") {
            $value = substr($value, 1, -1);
            $value = str_replace("''", "'", $value);
            return $value;
        }

        // Handle double quotes
        if (strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
            $value = str_replace('\"', '"', $value);
            // Handle unicode escapes like \u{e9}
            $value = preg_replace_callback('/\\\\u\{([0-9a-fA-F]+)\}/', function ($matches) {
                return mb_chr(hexdec($matches[1]), 'UTF-8');
            }, $value);
            return $value;
        }

        return $value;
    }

    /**
     * @param string $file
     * @param array<string, string> $translations
     * @return void
     */
    private function writeYaml($file, array $translations): void
    {
        $content = "# CPV Codes translations\n# Generated file - do not edit manually\n\n";

        foreach ($translations as $key => $value) {
            $escapedKey   = $this->escapeYaml($key);
            $escapedValue = $this->escapeYaml($value);
            $content      .= "{$escapedKey}: {$escapedValue}\n";
        }

        file_put_contents($file, $content);
    }

    /**
     * @param string $value
     * @return string
     */
    private function escapeYaml($value): string
    {
        if (preg_match('/[:\[\]{}#&*!|>\'"%@`\n]/', $value) ||
            ctype_digit($value) ||
            in_array(strtolower($value), ['true', 'false', 'null', 'yes', 'no'], true)) {
            return "'" . str_replace("'", "''", $value) . "'";
        }
        return $value;
    }

    /**
     * @param string $file
     * @param array<string, string> $translations
     * @param string $locale
     * @return void
     */
    private function writePhpArray($file, array $translations, $locale): void
    {
        $content = "<?php\n\n";
        $content .= "/**\n";
        $content .= " * CPV Codes translations - " . strtoupper($locale) . "\n";
        $content .= " *\n";
        $content .= " * @generated Source of truth for translations\n";
        $content .= " */\n\n";
        $content .= "return [\n";

        foreach ($translations as $key => $value) {
            $escapedKey   = var_export($key, true);
            $escapedValue = var_export($value, true);
            $content      .= "    {$escapedKey} => {$escapedValue},\n";
        }

        $content .= "];\n";

        file_put_contents($file, $content);
    }

    /**
     * @param string $file
     * @param array<string, string> $translations
     * @return void
     */
    private function writeLaravelArray($file, array $translations): void
    {
        $content = "<?php\n\n";
        $content .= "/**\n";
        $content .= " * CPV Codes - Laravel translations\n";
        $content .= " *\n";
        $content .= " * @generated Generated from PHP source\n";
        $content .= " */\n\n";
        $content .= "return [\n";

        foreach ($translations as $key => $value) {
            $escapedKey   = var_export($key, true);
            $escapedValue = var_export($value, true);
            $content      .= "    {$escapedKey} => {$escapedValue},\n";
        }

        $content .= "];\n";

        file_put_contents($file, $content);
    }

    /**
     * @param string $file
     * @return string
     */
    private function extractLocaleFromYaml($file): string
    {
        $basename = basename($file);
        // Match both messages.locale.yaml and cpvCodes.locale.yaml patterns
        preg_match('/(?:messages|' . preg_quote($this->domain, '/') . ')\.(.+)\.yaml$/', $basename, $matches);
        return $matches[1] ?? 'en';
    }

    /**
     * @param string $file
     * @return string
     */
    private function extractLocaleFromPhp($file): string
    {
        $basename = basename($file);
        preg_match('/' . preg_quote($this->domain, '/') . '\.(.+)\.php$/', $basename, $matches);
        return $matches[1] ?? 'en';
    }

    /**
     * @param string $locale
     * @return string
     */
    private function normalizeLocale($locale): string
    {
        // bg_bg -> bg, de_de -> de
        return strtolower(explode('_', str_replace('-', '_', $locale))[0]);
    }
}
