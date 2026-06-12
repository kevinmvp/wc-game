<?php
declare(strict_types=1);

namespace App\Helpers;

final class FlagHelper
{
    /**
     * Maps country names to their flag emojis.
     *
     * @var array<string, string>
     */
    private const COUNTRY_FLAGS = [
        'Mexico' => '🇲🇽',
        'South Africa' => '🇿🇦',
        'South Korea' => '🇰🇷',
        'Czechia' => '🇨🇿',
        'Canada' => '🇨🇦',
        'Bosnia' => '🇧🇦',
        'USA' => '🇺🇸',
        'Paraguay' => '🇵🇾',
        'Qatar' => '🇶🇦',
        'Switzerland' => '🇨🇭',
        'Brazil' => '🇧🇷',
        'Morocco' => '🇲🇦',
        'Haiti' => '🇭🇹',
        'Scotland' => '🏴󠁧󠁢󠁳󠁣󠁣󠁴󠁿',
        'Australia' => '🇦🇺',
        'Turkiye' => '🇹🇷',
        'Germany' => '🇩🇪',
        'Curaçao' => '🇨🇼',
        'Netherlands' => '🇳🇱',
        'Japan' => '🇯🇵',
        'Ivory Coast' => '🇨🇮',
        'Ecuador' => '🇪🇨',
        'Sweden' => '🇸🇪',
        'Tunisia' => '🇹🇳',
        'Spain' => '🇪🇸',
        'Cape Verde' => '🇨🇻',
        'Belgium' => '🇧🇪',
        'Egypt' => '🇪🇬',
        'Saudi Arabia' => '🇸🇦',
        'Uruguay' => '🇺🇾',
        'Iran' => '🇮🇷',
        'New Zealand' => '🇳🇿',
        'France' => '🇫🇷',
        'Senegal' => '🇸🇳',
        'Iraq' => '🇮🇶',
        'Norway' => '🇳🇴',
        'Argentina' => '🇦🇷',
        'Algeria' => '🇩🇿',
        'Austria' => '🇦🇹',
        'Jordan' => '🇯🇴',
        'Portugal' => '🇵🇹',
        'DR Congo' => '🇨🇩',
        'England' => '🏴󠁧󠁢󠁥󠁮󠁧󠁿',
        'Croatia' => '🇭🇷',
        'Ghana' => '🇬🇭',
        'Panama' => '🇵🇦',
        'Uzbekistan' => '🇺🇿',
        'Colombia' => '🇨🇴',
    ];

    public static function getFlag(string $countryName): string
    {
        return self::COUNTRY_FLAGS[$countryName] ?? '';
    }
}
