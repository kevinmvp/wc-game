<?php
declare(strict_types=1);

require dirname(__DIR__, 2) . '/public/index.php';

use App\Models\MatchModel;
use App\Core\Environment;

// Load environment variables if not already loaded
Environment::load(dirname(__DIR__, 2) . '/.env');

// Assume $databaseConfig is available from public/index.php require
if (!isset($databaseConfig)) {
    die("Database configuration not loaded.\n");
}

$matchModel = new MatchModel($databaseConfig);

$fixtureData = <<<EOT
🏆 *FIFA WORLD CUP 2026*
🕒 *Malaysia Time (MYT)* 🇲🇾
GROUPING
📅 *12 June 2026 (Fri)*
🇲🇽 Mexico vs 🇿🇦 South Africa 
— 🕒 3:00 AM
🇰🇷 South Korea vs 🇨🇿 Czechia 
— 🕙 10:00 AM
📅 *13 June 2026 (Sat)*
🇨🇦 Canada vs 🇧🇦 Bosnia 
— 🕒 3:00 AM
🇺🇸 USA vs 🇵🇾 Paraguay 
— 🕘 9:00 AM
📅 *14 June 2026 (Sun)*
🇶🇦 Qatar vs 🇨🇭 Switzerland 
— 🕒 3:00 AM
🇧🇷 Brazil vs 🇲🇦 Morocco 
— 🕕 6:00 AM
🇭🇹 Haiti vs 🏴󠁧󠁢󠁳󠁣󠁴󠁿 Scotland 
— 🕘 9:00 AM
🇦🇺 Australia vs 🇹🇷 Turkiye 
— 🕛 12:00 PM
📅 *15 June 2026 (Mon)*
🇩🇪 Germany vs 🇨🇼 Curaçao 
— 🕐 1:00 AM
🇳🇱 Netherlands vs 🇯🇵 Japan 
— 🕓 4:00 AM
🇨🇮 Ivory Coast vs 🇪🇨 Ecuador 
— 🕖 7:00 AM
🇸🇪 Sweden vs 🇹🇳 Tunisia 
— 🕙 10:00 AM
📅 *16 June 2026 (Tue)*
🇪🇸 Spain vs 🇨🇻 Cape Verde 
— 🕛 12:00 AM
🇧🇪 Belgium vs 🇪🇬 Egypt 
— 🕒 3:00 AM
🇸🇦 Saudi Arabia vs 🇺🇾 Uruguay 
— 🕕 6:00 AM
🇮🇷 Iran vs 🇳🇿 New Zealand 
— 🕘 9:00 AM
📅 *17 June 2026 (Wed)*
🇫🇷 France vs 🇸🇳 Senegal 
— 🕒 3:00 AM
🇮🇶 Iraq vs 🇳🇴 Norway 
— 🕕 6:00 AM
🇦🇷 Argentina vs 🇩🇿 Algeria 
— 🕘 9:00 AM
🇦🇹 Austria vs 🇯🇴 Jordan 
— 🕛 12:00 PM
📅 *18 June 2026 (Thu)*
🇵🇹 Portugal vs 🇨🇩 DR Congo 
— 🕐 1:00 AM
🏴󠁧󠁢󠁥󠁮󠁧󠁿 England vs 🇭🇷 Croatia 
— 🕓 4:00 AM
🇬🇭 Ghana vs 🇵🇦 Panama 
— 🕖 7:00 AM
🇺🇿 Uzbekistan vs 🇨🇴 Colombia
— 🕙 10:00 AM
📅 *19 June 2026 (Fri)*
🇨🇿 Czechia vs 🇿🇦 South Africa 
— 🕒 12:00 AM
🇨🇭 Switzerland vs 🇧🇦 Bosnia 
— 🕙 3:00 AM
🇨🇦 Canada vs 🇶🇦 Qatar 
— 🕒 6:00 AM
🇲🇽 Mexico vs 🇰🇷 South Korea 
— 🕘 9:00 AM
📅 *20 June 2026 (Sat)*
🇺🇸 USA vs 🇦🇺 Australia 
— 🕒 3:00 AM
🏴󠁧󠁢󠁳󠁣󠁴󠁿 Scotland vs 🇲🇦 Morocco 
— 🕕 6:00 AM
🇧🇷 Brazil vs 🇭🇹 Haiti 
— 🕘 8:30 AM
🇹🇷 Turkiye vs 🇵🇾 Paraguay 
— 🕛 11:00 AM
📅 *21 June 2026 (Sun)*
🇳🇱 Netherlands vs 🇸🇪 Sweden 
— 🕐 1:00 AM
🇩🇪 Germany vs 🇨🇮 Ivory Coast 
— 🕓 4:00 AM
🇪🇨 Ecuador vs 🇨🇼 Curaçao 
— 🕖 8:00 AM
🇺🇿 Uzbekistan vs 🇨🇴 Colombia
— 🕙 10:00 AM
🇹🇳 Tunisia vs 🇯🇵 Japan 
— 🕙 12:00 PM
📅 *22 June 2026 (Mon)*
🇪🇸 Spain vs 🇸🇦 Saudi Arabia 
— 🕛 12:00 AM
🇧🇪 Belgium vs 🇮🇷 Iran 
— 🕒 3:00 AM
🇺🇾 Uruguay vs 🇨🇻 Cape Verde 
— 🕕 6:00 AM
🇳🇿 New Zealand vs 🇪🇬 Egypt 
— 🕘 9:00 AM
📅 *23 June 2026 (Tue)*
🇦🇷 Argentina vs 🇦🇹 Austria 
— 🕒 1:00 AM
🇫🇷 France vs 🇮🇶 Iraq 
— 🕕 5:00 AM
🇳🇴 Norway vs 🇸🇳 Senegal 
— 🕘 8:00 AM
🇯🇴 Jordan vs 🇩🇿 Algeria 
— 🕛 11:00 AM
📅 *24 June 2026 (Wed)*
🇵🇹 Portugal vs 🇺🇿 Uzbekistan 
— 🕐 1:00 AM
🏴󠁧󠁢󠁥󠁮󠁧󠁿 England vs 🇬🇭 Ghana 
— 🕓 4:00 AM
🇵🇦 Panama vs 🇭🇷 Croatia 
— 🕖 7:00 AM
🇨🇴 Colombia vs 🇨🇩 DR Congo 
— 🕙 10:00 AM
📅 *25 June 2026 (Thu)*
🇨🇭 Switzerland vs 🇨🇦 Canada 
— 🕒 3:00 AM
🇧🇦 Bosnia vs 🇶🇦 Qatar 
— 🕙 3:00 AM
🏴󠁧󠁢󠁳󠁣󠁴󠁿 Scotland vs 🇧🇷 Brazil 
— 🕒 6:00 AM
🇲🇦 Morocco vs 🇭🇹 Haiti — 🕘 6:00 AM
🇨🇿 Czechia vs 🇲🇽 Mexico 
— 🕒 9:00 AM
🇿🇦 South Africa vs 🇰🇷 South Korea 
— 🕕 9:00 AM
📅 *26 June 2026 (Fri)*
🇨🇼 Curaçao vs 🇨🇮 Ivory Coast 
— 🕒 4:00 AM
🇪🇨 Ecuador vs 🇩🇪 Germany 
— 🕕 4:00 AM
🇯🇵 Japan vs 🇸🇪 Sweden 
— 🕘 7:00 AM
🇹🇳 Tunisia vs 🇳🇱 Netherlands 
— 🕛 7:00 AM
🇹🇷 Turkiye vs 🇺🇸 USA 
— 🕐 10:00 AM
🇵🇾 Paraguay vs 🇦🇺 Australia 
—  🕓 10:00 AM
📅 *27 June 2026 (Sat)*
🇳🇴 Norway vs 🇫🇷 France 
— 🕛 3:00 AM
🇸🇳 Senegal vs 🇮🇶 Iraq 
— 🕒 3:00 AM
🇨🇻 Cape Verde vs 🇸🇦 Saudi Arabia 
— 🕕 8:00 AM
🇺🇾 Uruguay vs 🇪🇸 Spain 
— 🕘 8:00 AM
🇪🇬 Egypt vs 🇮🇷 Iran 
— 🕛 11:00 AM
🇳🇿 New Zealand vs 🇧🇪 Belgium 
— 🕒 11:00 AM
📅 *28 June 2026 (Sun)*
🇵🇦 Panama vs 🏴󠁧󠁢󠁥󠁮󠁧󠁿 England 
— 🕒 5:00 AM
🇭🇷 Croatia vs 🇬🇭 Ghana 
— 🕕 5:00 AM
🇨🇴 Colombia vs 🇵🇹 Portugal 
— 🕘 7:30 AM
🇨🇩 DR Congo vs 🇺🇿 Uzbekistan 
— 🕛 7:30 AM
🇩🇿 Algeria vs 🇦🇹 Austria 
— 🕐 10:00 AM
🇯🇴 Jordan vs  🇦🇷 Argentina 
— 🕓 10:00 AM
ROUND 32
📅 *29 June 2026 (Mon)* 
📅 *4 July 2026 (Sat)* 
ROUND 16
📅 *5 July 2026 (Sun)* 
📅 *8 July 2026 (Wed)* 
QUARTERFINALS
📅 *10 July 2026 (Fri)* 
📅 *12 July 2026 (Sun)* 
SEMIFINALS
📅 *15 July 2026 (Wed)* 
📅 *16 July 2026 (Thu)* 
🥉THIRD PLACE MATCH 
— 🕔 5:00 AM
📅 *19 July 2026 (Sun)*
🏆 FINAL 
— 🕒 3:00 AM
📅 *20 July 2026 (Mon)*
EOT;

$lines = explode("\n", $fixtureData);
$fixturesToImport = [];
$currentDate = '';

foreach ($lines as $line) {
    $line = trim($line);

    // Skip empty lines, headers, and knockout stage markers for now
    if (
        $line === '' ||
        str_starts_with($line, '🏆') ||
        str_starts_with($line, '🕒') ||
        str_starts_with($line, 'GROUPING') ||
        str_starts_with($line, 'ROUND 32') ||
        str_starts_with($line, 'ROUND 16') ||
        str_starts_with($line, 'QUARTERFINALS') ||
        str_starts_with($line, 'SEMIFINALS') ||
        str_starts_with($line, '🥉THIRD PLACE MATCH') ||
        str_starts_with($line, '🏆 FINAL')
    ) {
        continue;
    }

    // Parse date line
    if (str_starts_with($line, '📅')) {
        preg_match('/\\*(\\d{1,2} [A-Za-z]+ \\d{4})/', $line, $matches);
        if (isset($matches[1])) {
            $dateString = $matches[1];
            $dateTime = DateTime::createFromFormat('j F Y', $dateString);
            if ($dateTime) {
                $currentDate = $dateTime->format('Y-m-d');
            }
        }
        continue; // Move to the next line after parsing date
    }

    // Parse match and time lines
    // This assumes match line is followed by a time line
    if (str_contains($line, ' vs ')) {
        // Remove emojis and trim extra spaces for teams
        $teamsRaw = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $line);
        [$homeTeam, $awayTeam] = array_map('trim', explode(' vs ', $teamsRaw, 2));
        
        // Peek next line for time
        $nextLineIndex = array_search($line, $lines) + 1;
        $timeLine = trim($lines[$nextLineIndex] ?? '');

        if (str_starts_with($timeLine, '— 🕒') || str_starts_with($timeLine, '— 🕙') || str_starts_with($timeLine, '— 🕘') || str_starts_with($timeLine, '— 🕛') || str_starts_with($timeLine, '— 🕐') || str_starts_with($timeLine, '— 🕓') || str_starts_with($timeLine, '— 🕖') || str_starts_with($timeLine, '— 🕕') || str_starts_with($timeLine, '— 🕔')) {
            preg_match('/(\\d{1,2}:\\d{2}) (AM|PM)/', $timeLine, $timeMatches);
            if (isset($timeMatches[1], $timeMatches[2])) {
                $time12Hour = $timeMatches[1] . ' ' . $timeMatches[2];
                $time24Hour = DateTime::createFromFormat('g:i A', $time12Hour);
                if ($time24Hour) {
                    $localTime = $time24Hour->format('H:i:s');
                    $fixturesToImport[] = [
                        'stage' => MatchModel::STAGE_GROUP,
                        'match_date' => $currentDate,
                        'local_time' => $localTime,
                        'home_team' => $homeTeam,
                        'away_team' => $awayTeam,
                        'venue' => null, // Not provided in source data
                        'venue_city' => null, // Not provided in source data
                        'notes' => null, // Not provided in source data
                    ];
                }
            }
        }
    }
}

if (!empty($fixturesToImport)) {
    try {
        $summary = $matchModel->createDetailedFixturesBulk($fixturesToImport);
        echo "Fixture import complete:\n";
        echo " - Imported: " . $summary['imported'] . " matches\n";
        echo " - Skipped: " . $summary['skipped'] . " matches (already existed)\n";
    } catch (Throwable $e) {
        echo "Error importing fixtures: " . $e->getMessage() . "\n";
    }
} else {
    echo "No fixtures found to import.\n";
}

exit(0);