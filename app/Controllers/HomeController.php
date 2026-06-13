<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\HealthCheckModel;
use App\Models\LeagueModel;
use App\Models\MatchModel;
use App\Models\VoteModel;
use DateTimeImmutable;
use DateTimeZone;

/**
 * Handles homepage and health endpoints.
 */
class HomeController extends BaseController
{
    /**
     * Creates a new controller with app and database configuration.
     *
     * @param array<string, mixed> $appConfig Application configuration.
     * @param array<string, mixed> $databaseConfig Database configuration.
     */
    public function __construct(
        array $appConfig,
        protected array $databaseConfig
    ) {
        parent::__construct($appConfig);
    }

    /**
     * Renders the main landing page.
     */
    public function index(): void
    {
        $leagueModel = new LeagueModel($this->databaseConfig);
        $matchModel = new MatchModel($this->databaseConfig);
        $voteModel = new VoteModel($this->databaseConfig);

        $appTimezone = new DateTimeZone((string) ($this->appConfig['timezone'] ?? 'UTC'));
        $now = new DateTimeImmutable('now', $appTimezone);

        $today = $now->format('Y-m-d');
        $tomorrow = $now->modify('+1 day')->format('Y-m-d');

        $leaderboardRows = $leagueModel->leaderboard();
        $matchVoteSummary = $voteModel->voteSummaryByPastAndTomorrowMatches(
            $now->format('Y-m-d H:i:s'),
            $tomorrow
        );

        $allUpcomingMatches = [];
        $todayMatchesRaw = $matchModel->allByDate($today);
        $tomorrowMatchesRaw = $matchModel->allByDate($tomorrow);

        // Filter out matches that have already started
        foreach (array_merge($todayMatchesRaw, $tomorrowMatchesRaw) as $match) {
            $matchDateTimeString = $match['match_date'] . ' ' . ($match['local_time'] ?? '00:00:00');
            $matchDateTime = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $matchDateTimeString, $appTimezone);

            if ($matchDateTime && $matchDateTime > $now) {
                $allUpcomingMatches[] = $match;
            }
        }

        // Sort upcoming matches by date and time
        usort($allUpcomingMatches, static function ($a, $b) {
            $dateTimeA = (string) $a['match_date'] . ' ' . ((string) $a['local_time'] ?: '00:00:00');
            $dateTimeB = (string) $b['match_date'] . ' ' . ((string) $b['local_time'] ?: '00:00:00');
            return strtotime($dateTimeA) - strtotime($dateTimeB);
        });

        $participant = null;
        $todayVotes = []; // This will now include votes for today's *upcoming* matches
        $pastVotedMatches = [];
        $participantSession = $_SESSION['participant'] ?? null;
        if (is_array($participantSession) && isset($participantSession['id'])) {
            $participant = $participantSession;
            // Fetch votes for today's matches, not necessarily upcoming ones, to cover votes already cast
            $todayVotes = $voteModel->votesByParticipantOnDate((int) $participantSession['id'], $today);
            $pastVotedMatches = $voteModel->pastVotedMatchesByParticipant(
                (int) $participantSession['id'],
                $now->format('Y-m-d H:i:s')
            );
        }

        $this->render('home.index', [
            'title' => 'RPC World Cup League',
            'leaderboardRows' => $leaderboardRows,
            'matchVoteSummary' => $matchVoteSummary,
            'today' => $today,
            'upcomingMatches' => $allUpcomingMatches, // Changed from todayMatches to upcomingMatches
            'participant' => $participant,
            'todayVotes' => $todayVotes,
            'pastVotedMatches' => $pastVotedMatches,
            'allowedPredictions' => MatchModel::allowedResults(),
            'hideAppBrand' => true,
        ]);
    }

    /**
     * Returns a JSON health payload for basic monitoring.
     */
    public function health(): void
    {
        $healthCheckModel = new HealthCheckModel($this->databaseConfig);
        $databaseOnline = $healthCheckModel->pingDatabase();

        $this->renderJson([
            'application' => (string) ($this->appConfig['app_name'] ?? 'Lemonade Stack'),
            'status' => $databaseOnline ? 'ok' : 'degraded',
            'database' => 'up',
            'timestamp' => gmdate('c'),
        ], $databaseOnline ? 200 : 503);
    }
}
