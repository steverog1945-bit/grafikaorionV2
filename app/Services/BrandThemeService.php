<?php

namespace App\Services;

use InvalidArgumentException;
use JsonException;
use RuntimeException;

class BrandThemeService
{
    public const DEFAULT_PRESET = 'orion-classic';

    private const PRESETS = [
        'orion-classic' => [
            'name' => 'Orion Classic',
            'description' => 'Palet aplikasi saat ini dengan aksen teal yang tenang.',
            'swatches' => ['#006C7B', '#0A4C57', '#BFD5C9', '#E8891D'],
            'ramp' => [
                '50' => '#EAF6F7', '100' => '#D2EBEE', '200' => '#A6D6DD',
                '300' => '#6FBCC7', '400' => '#2E9CAB', '500' => '#04727E',
                '600' => '#006C7B', '700' => '#005663', '800' => '#01434D',
                '900' => '#02363E', '950' => '#012228',
            ],
            'accent' => [
                'light' => '#006C7B', 'hover_light' => '#005663', 'surface_light' => '#EAF6F7',
                'dark' => '#5FC4D4', 'hover_dark' => '#7FD0DE', 'dark_hover' => '#05818F', 'surface_dark' => '#02363E',
            ],
        ],
        'copilot-light' => [
            'name' => 'Copilot Light',
            'description' => 'Aksen indigo yang jelas dengan nuansa terang dan ringan.',
            'font_family' => "'Segoe UI', ui-sans-serif, system-ui",
            'swatches' => ['#5055B8', '#7C80D6', '#DFE0F5', '#A7AAFF'],
            'ramp' => [
                '50' => '#F1F1FB', '100' => '#DFE0F5', '200' => '#C2C4ED',
                '300' => '#A0A4E2', '400' => '#7C80D6', '500' => '#676BC9',
                '600' => '#5055B8', '700' => '#40448F', '800' => '#30336B',
                '900' => '#24254C', '950' => '#17182F',
            ],
            'accent' => [
                'light' => '#5055B8', 'hover_light' => '#40448F', 'surface_light' => '#F1F1FB',
                'dark' => '#A7AAFF', 'hover_dark' => '#C2C4FF', 'dark_hover' => '#8589E8', 'surface_dark' => '#30305C',
            ],
        ],
        'coastal-slate' => [
            'name' => 'Coastal Slate',
            'description' => 'Biru slate yang lembut, terinspirasi warna pesisir.',
            'swatches' => ['#3F6578', '#6E98AA', '#DCE9ED', '#8CBAC9'],
            'ramp' => [
                '50' => '#EDF4F6', '100' => '#DCE9ED', '200' => '#BDD5DC',
                '300' => '#97BBC8', '400' => '#6E98AA', '500' => '#4F788B',
                '600' => '#3F6578', '700' => '#345261', '800' => '#2B424E',
                '900' => '#1F323B', '950' => '#15232A',
            ],
            'accent' => [
                'light' => '#3F6578', 'hover_light' => '#345261', 'surface_light' => '#EDF4F6',
                'dark' => '#8CBAC9', 'hover_dark' => '#A6CCD8', 'dark_hover' => '#6E9CAC', 'surface_dark' => '#203944',
            ],
        ],
        'deep-ocean' => [
            'name' => 'Deep Ocean',
            'description' => 'Aksen biru laut yang lebih dalam dengan kontras tegas.',
            'swatches' => ['#146A80', '#3996A9', '#CFE7EC', '#56B8CD'],
            'ramp' => [
                '50' => '#E9F5F7', '100' => '#CFE7EC', '200' => '#A4D2DB',
                '300' => '#70B5C3', '400' => '#3996A9', '500' => '#1F7C93',
                '600' => '#146A80', '700' => '#11586B', '800' => '#104653',
                '900' => '#0C343D', '950' => '#071F25',
            ],
            'accent' => [
                'light' => '#146A80', 'hover_light' => '#11586B', 'surface_light' => '#E9F5F7',
                'dark' => '#56B8CD', 'hover_dark' => '#78C9D9', 'dark_hover' => '#3A9DB3', 'surface_dark' => '#103744',
            ],
        ],
    ];

    public function __construct(private readonly ?string $path = null)
    {
    }

    public function presets(): array
    {
        return array_map(
            fn (string $id, array $preset): array => $this->withVariables($id, $preset),
            array_keys(self::PRESETS),
            array_values(self::PRESETS),
        );
    }

    public function current(): array
    {
        return $this->preset($this->readPresetId());
    }

    public function preset(string $id): array
    {
        if (! isset(self::PRESETS[$id])) {
            throw new InvalidArgumentException('Preset tema tidak dikenal.');
        }

        return $this->withVariables($id, self::PRESETS[$id]);
    }

    public function save(string $id): array
    {
        if (! isset(self::PRESETS[$id])) {
            throw new InvalidArgumentException('Preset tema tidak dikenal.');
        }

        $path = $this->filePath();
        $directory = dirname($path);
        $createdDirectory = false;

        if (! is_dir($directory)) {
            if (! mkdir($directory, 0700, true) && ! is_dir($directory)) {
                throw new RuntimeException('Folder penyimpanan tema tidak dapat dibuat.');
            }
            $createdDirectory = true;
        }

        if ($createdDirectory && DIRECTORY_SEPARATOR !== '\\') {
            if (! chmod($directory, 0700)) {
                throw new RuntimeException('Izin privat folder tema tidak dapat diterapkan.');
            }
        }

        $temporaryPath = tempnam($directory, '.brand-theme-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Berkas sementara tema tidak dapat dibuat.');
        }

        try {
            $temporaryDirectory = realpath(dirname($temporaryPath));
            $storageDirectory = realpath($directory);
            $sameDirectory = $temporaryDirectory !== false
                && $storageDirectory !== false
                && (DIRECTORY_SEPARATOR === '\\'
                    ? strcasecmp($temporaryDirectory, $storageDirectory) === 0
                    : $temporaryDirectory === $storageDirectory);
            if (! $sameDirectory) {
                throw new RuntimeException('Berkas sementara tema harus berada di folder penyimpanan privat.');
            }

            $json = json_encode(['preset' => $id], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
            $written = file_put_contents($temporaryPath, $json, LOCK_EX);
            if ($written !== strlen($json)) {
                throw new RuntimeException('Berkas tema tidak dapat ditulis dengan lengkap.');
            }

            if (DIRECTORY_SEPARATOR !== '\\' && ! chmod($temporaryPath, 0600)) {
                throw new RuntimeException('Izin privat berkas tema tidak dapat diterapkan.');
            }

            if (! rename($temporaryPath, $path)) {
                throw new RuntimeException('Berkas tema tidak dapat diganti secara atomik.');
            }
        } catch (\Throwable $exception) {
            if (is_file($temporaryPath)) {
                if (! unlink($temporaryPath)) {
                    error_log('Temporary brand theme file could not be removed after a failed save.');
                }
            }

            throw $exception;
        }

        return $this->preset($id);
    }

    private function readPresetId(): string
    {
        $path = $this->filePath();
        if (! is_file($path)) {
            return self::DEFAULT_PRESET;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            error_log('Brand theme settings file could not be read; using the default preset.');

            return self::DEFAULT_PRESET;
        }

        try {
            $data = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            error_log('Brand theme settings file is malformed; using the default preset.');

            return self::DEFAULT_PRESET;
        }

        if (! is_array($data) || array_keys($data) !== ['preset'] || ! is_string($data['preset'] ?? null)) {
            error_log('Brand theme settings file has an invalid shape; using the default preset.');

            return self::DEFAULT_PRESET;
        }

        if (! isset(self::PRESETS[$data['preset']])) {
            error_log('Brand theme settings file contains an unknown preset; using the default preset.');

            return self::DEFAULT_PRESET;
        }

        return $data['preset'];
    }

    private function withVariables(string $id, array $preset): array
    {
        $variables = [];
        foreach ($preset['ramp'] as $shade => $color) {
            $variables["--color-indigo-{$shade}"] = $color;
        }

        if (isset($preset['font_family'])) {
            $variables['--brand-font-family'] = $preset['font_family'];
        }

        $variables['--brand-accent-light'] = $preset['accent']['light'];
        $variables['--brand-accent-hover-light'] = $preset['accent']['hover_light'];
        $variables['--brand-accent-surface-light'] = $preset['accent']['surface_light'];
        $variables['--brand-accent-dark'] = $preset['accent']['dark'];
        $variables['--brand-accent-hover-dark'] = $preset['accent']['hover_dark'];
        $variables['--brand-dark-accent-hover'] = $preset['accent']['dark_hover'];
        $variables['--brand-accent-surface-dark'] = $preset['accent']['surface_dark'];

        return [
            'id' => $id,
            'name' => $preset['name'],
            'description' => $preset['description'],
            'swatches' => $preset['swatches'],
            'ramp' => $preset['ramp'],
            'accent' => $preset['accent'],
            'variables' => $variables,
        ];
    }

    private function filePath(): string
    {
        return $this->path ?? storage_path('app/system/brand-theme.json');
    }
}
