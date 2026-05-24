<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AvatarStyleService
{
    protected string $storagePath = 'avatar_mode.txt';

    protected array $styleSets = [
        'default' => [
            ['id' => 'adventurer', 'name' => 'Classic Champion', 'description' => 'Heroic cartoon portrait'],
            ['id' => 'big-ears', 'name' => 'Masked Defender', 'description' => 'Friendly vigilante with bold style'],
            ['id' => 'bottts', 'name' => 'Tech Guardian', 'description' => 'Futuristic robot hero look'],
            ['id' => 'croodles', 'name' => 'Alien Protector', 'description' => 'Colorful fantasy character style'],
            ['id' => 'pixel-art', 'name' => 'Retro Avenger', 'description' => 'Pixel-powered action avatar'],
            ['id' => 'micah', 'name' => 'Shadow Archer', 'description' => 'Stylized hero with dramatic silhouette'],
            ['id' => 'open-peeps', 'name' => 'Team Hero', 'description' => 'Hand-drawn squad member look'],
        ],
        'superb' => [
            ['id' => 'avataaars', 'name' => 'Legendary Hero', 'description' => 'Epic comic-style portrait'],
            ['id' => 'adventurer', 'name' => 'Galactic Sentinel', 'description' => 'Space-powered hero energy'],
            ['id' => 'big-ears', 'name' => 'Night Watch', 'description' => 'Dark urban vigilante style'],
            ['id' => 'bottts', 'name' => 'Cyber Samurai', 'description' => 'High-tech warrior guardian'],
            ['id' => 'croodles', 'name' => 'Cartoon Crew', 'description' => 'Playful animated character vibe'],
            ['id' => 'fun-emoji', 'name' => 'Neon Squad', 'description' => 'Bright, fun hero faces'],
            ['id' => 'pixel-art', 'name' => 'Retro Warrior', 'description' => 'Classic arcade fighting hero'],
            ['id' => 'micah', 'name' => 'Mystic Guardian', 'description' => 'Stylized magic protector'],
            ['id' => 'lorelei', 'name' => 'Fantasy Sentinel', 'description' => 'Dreamy illustrated hero look'],
            ['id' => 'open-peeps', 'name' => 'Family Hero', 'description' => 'Friendly group character style'],
        ],
    ];

    /**
     * Human-friendly inspiration tags for styles. These are non-copyrighted
     * descriptive labels (e.g. "team hero", "dark-knight-inspired") meant
     * to help admins pick a themed label set while keeping provider IDs valid.
     *
     * Keys are DiceBear style ids and values are arrays of short inspiration tags.
     */
    protected array $inspirationMap = [
        'avataaars' => ['legendary-team', 'comic-epic'],
        'adventurer' => ['team-leader', 'classic-hero'],
        'big-ears' => ['dark-knight-inspired', 'masked-vigilante'],
        'bottts' => ['cyber-warrior', 'tech-guardian'],
        'croodles' => ['cartoon-squad', 'playful-crew'],
        'pixel-art' => ['retro-arcade', 'pixel-vigilante'],
        'micah' => ['mystic-guardian', 'stylized-hero'],
        'open-peeps' => ['ensemble', 'group-heroes'],
        'fun-emoji' => ['neon-faces', 'bright-personas'],
        'lorelei' => ['fantasy-illustration', 'dreamy-guardian'],
    ];

    public function getMode(): string
    {
        if (! Storage::disk('local')->exists($this->storagePath)) {
            return 'default';
        }

        $mode = trim(Storage::disk('local')->get($this->storagePath));

        return $this->isValidMode($mode) ? $mode : 'default';
    }

    public function setMode(string $mode): void
    {
        $mode = Str::lower(trim($mode));

        if (! $this->isValidMode($mode)) {
            throw new InvalidArgumentException("Unknown avatar mode: {$mode}");
        }

        Storage::disk('local')->put($this->storagePath, $mode);
    }

    public function getAvailableStyles(): array
    {
        $styles = $this->styleSets[$this->getMode()] ?? $this->styleSets['default'];

        // Inject non-breaking inspiration labels for UI without changing IDs
        return array_map(function ($style) {
            $id = $style['id'];
            $style['inspirations'] = $this->inspirationMap[$id] ?? [];
            return $style;
        }, $styles);
    }

    public function getAllStyleIds(): array
    {
        return array_map(fn ($style) => $style['id'], $this->getAvailableStyles());
    }

    public function getModeOptions(): array
    {
        return array_keys($this->styleSets);
    }

    protected function isValidMode(string $mode): bool
    {
        return array_key_exists($mode, $this->styleSets);
    }
}
