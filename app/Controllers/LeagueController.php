<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\LeagueModel;
use App\Models\MatchModel;
use App\Models\ParticipantModel;
use App\Models\VoteModel;

/**
 * Handles participant onboarding, daily voting, and leaderboard pages.
 */
class LeagueController extends BaseController
{
    /**
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
     * Displays the participant join form.
     */
    public function joinForm(): void
    {
        $this->render('league.join', [
            'title' => 'Join League',
            'errors' => [],
            'formData' => [
                'name' => '',
                'team_name' => '',
                'mobile' => '',
                'password' => '',
            ],
        ]);
    }

    /**
     * Creates or reuses a participant profile by mobile number.
     */
    public function joinSubmit(): void
    {
        $formData = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'team_name' => trim((string) ($_POST['team_name'] ?? '')),
            'mobile' => preg_replace('/\s+/', '', trim((string) ($_POST['mobile'] ?? ''))) ?: '',
            'password' => (string) ($_POST['password'] ?? ''),
        ];

        if (!$this->verifyCsrfToken((string) ($_POST['_csrf'] ?? ''))) {
            $this->render('league.join', [
                'title' => 'Join League',
                'errors' => ['Security token is invalid or expired. Please refresh and try again.'],
                'formData' => $formData,
            ], 422);

            return;
        }

        $errors = $this->validateJoinForm($formData);
        if ($errors !== []) {
            $this->render('league.join', [
                'title' => 'Join League',
                'errors' => $errors,
                'formData' => $formData,
            ], 422);

            return;
        }

        $participantModel = new ParticipantModel($this->databaseConfig);
        $participant = $participantModel->registerForLeague(
            $formData['name'],
            $formData['team_name'],
            $formData['mobile'],
            $formData['password']
        );

        session_regenerate_id(true);
        $_SESSION['participant'] = [
            'id' => (int) $participant['id'],
            'name' => (string) $participant['name'],
            'team_name' => (string) $participant['team_name'],
            'mobile' => (string) $participant['mobile'],
        ];

        $this->redirect('/'); // Changed from '/league/daily' to '/'
    }

    /**
     * Displays the participant login form.
     */
    public function loginForm(): void
    {
        $this->render('league.login', [
            'title' => 'Login to League',
            'errors' => [],
            'formData' => [
                'mobile' => '',
                'password' => '',
            ],
        ]);
    }

    /**
     * Authenticates a participant and starts a session.
     */
    public function loginSubmit(): void
    {
        $formData = [
            'mobile' => preg_replace('/\s+/', '', trim((string) ($_POST['mobile'] ?? ''))) ?: '',
            'password' => (string) ($_POST['password'] ?? ''),
        ];

        if (!$this->verifyCsrfToken((string) ($_POST['_csrf'] ?? ''))) {
            $this->render('league.login', [
                'title' => 'Login to League',
                'errors' => ['Security token is invalid or expired. Please refresh and try again.'],
                'formData' => $formData,
            ], 422);

            return;
        }

        $errors = [];
        if (!preg_match('/^[0-9+]{8,15}$/', $formData['mobile'])) {
            $errors[] = 'Mobile number must be 8 to 15 digits and may include +.';
        }
        if ($formData['password'] === '') {
            $errors[] = 'Password is required.';
        }

        if ($errors !== []) {
            $this->render('league.login', [
                'title' => 'Login to League',
                'errors' => $errors,
                'formData' => $formData,
            ], 422);
            return;
        }

        $participantModel = new ParticipantModel($this->databaseConfig);
        $participant = $participantModel->findByMobile($formData['mobile']);

        if ($participant === null || !password_verify($formData['password'], (string) $participant['password'])) {
            $this->render('league.login', [
                'title' => 'Login to League',
                'errors' => ['Invalid mobile number or password.'],
                'formData' => $formData,
            ], 422);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['participant'] = [
            'id' => (int) $participant['id'],
            'name' => (string) $participant['name'],
            'team_name' => (string) $participant['team_name'],
            'mobile' => (string) $participant['mobile'],
        ];

        $this->redirect('/'); // Changed from '/league/daily' to '/'
    }

    /**
     * Displays upcoming matches for today and tomorrow with current votes.
     */
    public function dailyGames(): void
    {
        $participant = $this->requireParticipant();
        $timezone = new \DateTimeZone((string) ($this->appConfig['timezone'] ?? date_default_timezone_get()));
        $now = new \DateTimeImmutable('now', $timezone);
        $today = $now->format('Y-m-d');
        $tomorrow = $now->modify('+1 day')->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        $viewMode = trim((string) ($_GET['view'] ?? 'table'));
        if (!in_array($viewMode, ['table', 'grid'], true)) {
            $viewMode = 'table';
        }

        $matchModel = new MatchModel($this->databaseConfig);
        $voteModel = new VoteModel($this->databaseConfig);
        $leagueModel = new LeagueModel($this->databaseConfig);

        $todayMatches = $matchModel->allByDate($today, $currentTime);
        $tomorrowMatches = $matchModel->allByDate($tomorrow);
        $matches = array_merge($todayMatches, $tomorrowMatches);

        $matchIds = array_map(
            static fn (array $match): int => (int) ($match['id'] ?? 0),
            $matches
        );
        $votes = $voteModel->votesByParticipantForMatches((int) $participant['id'], $matchIds);
        $points = $leagueModel->pointsForParticipant((int) $participant['id']);

        $this->render('league.daily', [
            'title' => 'Daily Matches',
            'today' => $today,
            'tomorrow' => $tomorrow,
            'participant' => $participant,
            'matches' => $matches,
            'votes' => $votes,
            'points' => $points,
            'viewMode' => $viewMode,
            'allowedPredictions' => MatchModel::allowedResults(),
        ]);
    }

    /**
     * Displays all fixtures with stage and group filters.
     */
    public function fixtures(): void
    {
        $selectedStage = trim((string) ($_GET['stage'] ?? MatchModel::STAGE_GROUP));
        $selectedGroup = trim((string) ($_GET['group'] ?? ''));
        $selectedDate = trim((string) ($_GET['date'] ?? ''));
        $selectedVenue = trim((string) ($_GET['venue'] ?? ''));
        $imported = max(0, (int) ($_GET['imported'] ?? 0));
        $skipped = max(0, (int) ($_GET['skipped'] ?? 0));
        $viewMode = trim((string) ($_GET['view'] ?? 'table'));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 12;

        if (!in_array($viewMode, ['table', 'grid'], true)) {
            $viewMode = 'table';
        }

        if ($selectedStage !== '' && !in_array($selectedStage, MatchModel::allowedStages(), true)) {
            $selectedStage = MatchModel::STAGE_GROUP;
        }

        if ($selectedGroup !== '' && !in_array($selectedGroup, MatchModel::allowedGroups(), true)) {
            $selectedGroup = '';
        }

        if ($selectedStage !== MatchModel::STAGE_GROUP) {
            $selectedGroup = '';
        }

        $matchModel = new MatchModel($this->databaseConfig);
        $venueOptions = $matchModel->distinctVenues();

        if ($selectedVenue !== '' && !in_array($selectedVenue, $venueOptions, true)) {
            $selectedVenue = '';
        }

        if ($selectedDate !== '') {
            $parsedDate = date_create_from_format('Y-m-d', $selectedDate);
            if ($parsedDate === false || $parsedDate->format('Y-m-d') !== $selectedDate) {
                $selectedDate = '';
            }
        }

        $totalFixtures = $matchModel->countFixtures(
            $selectedStage === '' ? null : $selectedStage,
            $selectedGroup === '' ? null : $selectedGroup,
            $selectedDate === '' ? null : $selectedDate,
            $selectedVenue === '' ? null : $selectedVenue
        );
        $totalPages = max(1, (int) ceil($totalFixtures / $perPage));
        if ($page > $totalPages) {
            $page = $totalPages;
        }
        $offset = ($page - 1) * $perPage;

        $this->render('league.fixtures', [
            'title' => 'Fixtures',
            'matches' => $matchModel->filterFixturesPaginated(
                $selectedStage === '' ? null : $selectedStage,
                $selectedGroup === '' ? null : $selectedGroup,
                $selectedDate === '' ? null : $selectedDate,
                $selectedVenue === '' ? null : $selectedVenue,
                $perPage,
                $offset
            ),
            'selectedStage' => $selectedStage,
            'selectedGroup' => $selectedGroup,
            'selectedDate' => $selectedDate,
            'selectedVenue' => $selectedVenue,
            'imported' => $imported,
            'skipped' => $skipped,
            'viewMode' => $viewMode,
            'stageOptions' => MatchModel::allowedStages(),
            'groupOptions' => MatchModel::allowedGroups(),
            'venueOptions' => $venueOptions,
            'page' => $page,
            'perPage' => $perPage,
            'totalFixtures' => $totalFixtures,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Stores one participant vote for one match.
     */
    public function submitVote(string $matchId): void
    {
        $participant = $this->requireParticipant();
        if (!$this->verifyCsrfToken((string) ($_POST['_csrf'] ?? ''))) {
            $this->redirect('/league/daily');
        }

        $prediction = trim((string) ($_POST['prediction'] ?? ''));

        if (!in_array($prediction, MatchModel::allowedResults(), true)) {
            $this->redirect('/league/daily');
        }

        $matchModel = new MatchModel($this->databaseConfig);
        $match = $matchModel->findById((int) $matchId);
        if ($match === null) {
            $this->redirect('/league/daily');
        }

        $timezone = new \DateTimeZone((string) ($this->appConfig['timezone'] ?? date_default_timezone_get()));
        $now = new \DateTimeImmutable('now', $timezone);
        $today = $now->format('Y-m-d');
        $tomorrow = $now->modify('+1 day')->format('Y-m-d');
        $matchDate = (string) ($match['match_date'] ?? '');

        if (!in_array($matchDate, [$today, $tomorrow], true)) {
            $this->redirect('/league/daily');
        }

        $localTime = (string) ($match['local_time'] ?? '');
        if ($localTime !== '') {
            $kickoff = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                $matchDate . ' ' . $localTime,
                $timezone
            );

            if ($kickoff !== false && $now >= $kickoff) {
                $this->redirect('/league/daily');
            }
        }

        if ((string) ($match['result'] ?? '') !== '') {
            $this->redirect('/league/daily');
        }

        $voteModel = new VoteModel($this->databaseConfig);
        $voteModel->saveVote((int) $participant['id'], (int) $matchId, $prediction);

        $this->redirect('/league/daily');
    }

    /**
     * Stores many participant votes submitted from the daily page.
     */
    public function submitVotesBulk(): void
    {
        $participant = $this->requireParticipant();

        if (!$this->verifyCsrfToken((string) ($_POST['_csrf'] ?? ''))) {
            $this->redirect('/league/daily');
        }

        $submittedVotes = $_POST['predictions'] ?? [];
        if (!is_array($submittedVotes) || $submittedVotes === []) {
            $viewMode = trim((string) ($_POST['view'] ?? 'table'));
            $viewMode = in_array($viewMode, ['table', 'grid'], true) ? $viewMode : 'table';
            $this->redirect('/league/daily?view=' . $viewMode);
        }

        $timezone = new \DateTimeZone((string) ($this->appConfig['timezone'] ?? date_default_timezone_get()));
        $now = new \DateTimeImmutable('now', $timezone);
        $matchModel = new MatchModel($this->databaseConfig);
        $voteModel = new VoteModel($this->databaseConfig);
        $allowedResults = MatchModel::allowedResults();

        foreach ($submittedVotes as $rawMatchId => $rawPrediction) {
            $matchId = (int) $rawMatchId;
            if ($matchId <= 0) {
                continue;
            }

            $prediction = trim((string) $rawPrediction);
            if (!in_array($prediction, $allowedResults, true)) {
                continue;
            }

            $match = $matchModel->findById($matchId);
            if ($match === null) {
                continue;
            }

            $matchDate = (string) ($match['match_date'] ?? '');
            if ($matchDate === '') {
                continue;
            }

            $localTime = (string) ($match['local_time'] ?? '');
            if ($localTime !== '') {
                $timeValue = strlen($localTime) === 5 ? ($localTime . ':00') : $localTime;
                $kickoff = \DateTimeImmutable::createFromFormat(
                    'Y-m-d H:i:s',
                    $matchDate . ' ' . $timeValue,
                    $timezone
                );

                if ($kickoff !== false && $now >= $kickoff) {
                    continue;
                }
            }

            if ((string) ($match['result'] ?? '') !== '') {
                continue;
            }

            $voteModel->saveVote((int) $participant['id'], $matchId, $prediction);
        }

        $viewMode = trim((string) ($_POST['view'] ?? 'table'));
        $viewMode = in_array($viewMode, ['table', 'grid'], true) ? $viewMode : 'table';
        $this->redirect('/league/daily?view=' . $viewMode);
    }

    /**
     * Renders global participant rankings by points.
     */
    public function leaderboard(): void
    {
        $leagueModel = new LeagueModel($this->databaseConfig);

        $this->render('league.leaderboard', [
            'title' => 'Leaderboard',
            'rows' => $leagueModel->leaderboard(),
        ]);
    }

    /**
     * Displays a lightweight match management screen.
     */
    public function manageMatches(): void
    {
        $this->requireLeagueAdmin();

        $matchModel = new MatchModel($this->databaseConfig);

        $this->render('league.manage_matches', [
            'title' => 'Manage Matches',
            'errors' => [],
            'formData' => [
                'match_date' => date('Y-m-d'),
                'home_team' => '',
                'away_team' => '',
            ],
            'matches' => $matchModel->allOrdered(),
            'allowedResults' => MatchModel::allowedResults(),
        ]);
    }

    /**
     * Displays a protected form for importing exact knockout fixtures later.
     */
    public function knockoutImportForm(): void
    {
        $this->requireLeagueAdmin();

        $this->render('league.knockout_import', [
            'title' => 'Import Knockout Fixture',
            'errors' => [],
            'formData' => [
                'stage' => MatchModel::STAGE_ROUND_OF_32,
                'match_date' => '',
                'local_time' => '',
                'home_team' => '',
                'away_team' => '',
                'venue' => '',
                'venue_city' => '',
                'notes' => '',
                'bulk_fixtures' => '',
            ],
            'stageOptions' => array_values(array_filter(
                MatchModel::allowedStages(),
                static fn (string $stage): bool => $stage !== MatchModel::STAGE_GROUP
            )),
        ]);
    }

    /**
     * Stores one or many exact knockout fixtures for later-stage scheduling.
     */
    public function knockoutImportSubmit(): void
    {
        $this->requireLeagueAdmin();

        $formData = [
            'stage' => trim((string) ($_POST['stage'] ?? '')),
            'match_date' => trim((string) ($_POST['match_date'] ?? '')),
            'local_time' => trim((string) ($_POST['local_time'] ?? '')),
            'home_team' => trim((string) ($_POST['home_team'] ?? '')),
            'away_team' => trim((string) ($_POST['away_team'] ?? '')),
            'venue' => trim((string) ($_POST['venue'] ?? '')),
            'venue_city' => trim((string) ($_POST['venue_city'] ?? '')),
            'notes' => trim((string) ($_POST['notes'] ?? '')),
            'bulk_fixtures' => trim((string) ($_POST['bulk_fixtures'] ?? '')),
        ];

        if (!$this->verifyCsrfToken((string) ($_POST['_csrf'] ?? ''))) {
            $this->render('league.knockout_import', [
                'title' => 'Import Knockout Fixture',
                'errors' => ['Security token is invalid or expired. Please refresh and try again.'],
                'formData' => $formData,
                'stageOptions' => array_values(array_filter(
                    MatchModel::allowedStages(),
                    static fn (string $stage): bool => $stage !== MatchModel::STAGE_GROUP
                )),
            ], 422);

            return;
        }

        $errors = $this->validateKnockoutImportForm($formData);
        if ($errors !== []) {
            $this->render('league.knockout_import', [
                'title' => 'Import Knockout Fixture',
                'errors' => $errors,
                'formData' => $formData,
                'stageOptions' => array_values(array_filter(
                    MatchModel::allowedStages(),
                    static fn (string $stage): bool => $stage !== MatchModel::STAGE_GROUP
                )),
            ], 422);

            return;
        }

        $matchModel = new MatchModel($this->databaseConfig);
        if ($formData['bulk_fixtures'] !== '') {
            $parsedBulk = $this->parseBulkKnockoutFixtures($formData['bulk_fixtures'], $formData['stage']);
            $bulkRows = $parsedBulk['rows'];
            $bulkErrors = $parsedBulk['errors'];

            if ($bulkErrors !== []) {
                $this->render('league.knockout_import', [
                    'title' => 'Import Knockout Fixture',
                    'errors' => $bulkErrors,
                    'formData' => $formData,
                    'stageOptions' => array_values(array_filter(
                        MatchModel::allowedStages(),
                        static fn (string $stage): bool => $stage !== MatchModel::STAGE_GROUP
                    )),
                ], 422);

                return;
            }

            $summary = $matchModel->createDetailedFixturesBulk($bulkRows);

            $this->redirect(
                '/league/fixtures?stage=' . rawurlencode($bulkRows[0]['stage'])
                . '&imported=' . (string) $summary['imported']
                . '&skipped=' . (string) $summary['skipped']
            );
        }

        $summary = $matchModel->createDetailedFixturesBulk([[
            'stage' => $formData['stage'],
            'match_date' => $formData['match_date'],
            'local_time' => $formData['local_time'],
            'home_team' => $formData['home_team'],
            'away_team' => $formData['away_team'],
            'venue' => $formData['venue'],
            'venue_city' => $formData['venue_city'],
            'notes' => $formData['notes'],
        ]]);

        $this->redirect(
            '/league/fixtures?stage=' . rawurlencode($formData['stage'])
            . '&imported=' . (string) $summary['imported']
            . '&skipped=' . (string) $summary['skipped']
        );
    }

    /**
     * Creates a match row for the schedule.
     */
    public function createMatch(): void
    {
        $this->requireLeagueAdmin();

        $formData = [
            'match_date' => trim((string) ($_POST['match_date'] ?? '')),
            'home_team' => trim((string) ($_POST['home_team'] ?? '')),
            'away_team' => trim((string) ($_POST['away_team'] ?? '')),
        ];

        $errors = $this->validateMatchForm($formData);
        if ($errors !== []) {
            $matchModel = new MatchModel($this->databaseConfig);
            $this->render('league.manage_matches', [
                'title' => 'Manage Matches',
                'errors' => $errors,
                'formData' => $formData,
                'matches' => $matchModel->allOrdered(),
                'allowedResults' => MatchModel::allowedResults(),
            ], 422);

            return;
        }

        $matchModel = new MatchModel($this->databaseConfig);
        $matchModel->create($formData['match_date'], $formData['home_team'], $formData['away_team']);

        $this->redirect('/league/manage-matches');
    }

    /**
     * Sets a completed match outcome for scoring.
     */
    public function setResult(string $matchId): void
    {
        $this->requireLeagueAdmin();

        $result = trim((string) ($_POST['result'] ?? ''));
        $normalizedResult = $result === '' ? null : $result;

        if ($normalizedResult !== null && !in_array($normalizedResult, MatchModel::allowedResults(), true)) {
            $this->redirect('/league/manage-matches');
        }

        $matchModel = new MatchModel($this->databaseConfig);
        $match = $matchModel->findById((int) $matchId);
        if ($match === null) {
            $this->redirect('/league/manage-matches');
        }

        $matchModel->updateResult((int) $matchId, $normalizedResult);

        $this->redirect('/league/manage-matches');
    }

    /**
     * Updates schedule details and scoreline for a match.
     */
    public function updateMatchDetails(string $matchId): void
    {
        $this->requireLeagueAdmin();

        $formData = [
            'match_date' => trim((string) ($_POST['match_date'] ?? '')),
            'local_time' => trim((string) ($_POST['local_time'] ?? '')),
            'home_team' => trim((string) ($_POST['home_team'] ?? '')),
            'away_team' => trim((string) ($_POST['away_team'] ?? '')),
            'venue' => trim((string) ($_POST['venue'] ?? '')),
            'venue_city' => trim((string) ($_POST['venue_city'] ?? '')),
            'home_score' => trim((string) ($_POST['home_score'] ?? '')),
            'away_score' => trim((string) ($_POST['away_score'] ?? '')),
        ];

        $errors = $this->validateMatchAdminUpdateForm($formData);
        if ($errors !== []) {
            $matchModel = new MatchModel($this->databaseConfig);
            $this->render('league.manage_matches', [
                'title' => 'Manage Matches',
                'errors' => $errors,
                'formData' => [
                    'match_date' => date('Y-m-d'),
                    'home_team' => '',
                    'away_team' => '',
                ],
                'matches' => $matchModel->allOrdered(),
                'allowedResults' => MatchModel::allowedResults(),
            ], 422);

            return;
        }

        $matchModel = new MatchModel($this->databaseConfig);
        $match = $matchModel->findById((int) $matchId);
        if ($match === null) {
            $this->redirect('/league/manage-matches');
        }

        $homeScore = $formData['home_score'] === '' ? null : (int) $formData['home_score'];
        $awayScore = $formData['away_score'] === '' ? null : (int) $formData['away_score'];

        $result = null;
        if ($homeScore !== null && $awayScore !== null) {
            if ($homeScore > $awayScore) {
                $result = MatchModel::RESULT_HOME;
            } elseif ($awayScore > $homeScore) {
                $result = MatchModel::RESULT_AWAY;
            } else {
                $result = MatchModel::RESULT_DRAW;
            }
        }

        $matchModel->updateScheduleAndScore(
            (int) $matchId,
            $formData['match_date'],
            $formData['local_time'] === '' ? null : $formData['local_time'],
            $formData['home_team'],
            $formData['away_team'],
            $formData['venue'] === '' ? null : $formData['venue'],
            $formData['venue_city'] === '' ? null : $formData['venue_city'],
            $homeScore,
            $awayScore,
            $result
        );

        $this->redirect('/league/manage-matches');
    }

    /**
     * Ends participant session.
     */
    public function logoutParticipant(): void
    {
        unset($_SESSION['participant']);

        $this->redirect('/league/login');
    }

    /**
     * Displays admin login form for privileged league actions.
     */
    public function adminLoginForm(): void
    {
        $this->render('league.admin_login', [
            'title' => 'League Admin Login',
            'errors' => [],
        ]);
    }

    /**
     * Authenticates league admin session using configured password.
     */
    public function adminLoginSubmit(): void
    {
        if (!$this->verifyCsrfToken((string) ($_POST['_csrf'] ?? ''))) {
            $this->render('league.admin_login', [
                'title' => 'League Admin Login',
                'errors' => ['Security token is invalid or expired. Please refresh and try again.'],
            ], 422);

            return;
        }

        $password = (string) ($_POST['admin_password'] ?? '');
        $configuredPassword = (string) ($this->appConfig['league_admin_password'] ?? '');

        if ($configuredPassword === '') {
            $this->render('league.admin_login', [
                'title' => 'League Admin Login',
                'errors' => ['League admin password is not configured.'],
            ], 422);

            return;
        }

        if (!hash_equals($configuredPassword, $password)) {
            $this->render('league.admin_login', [
                'title' => 'League Admin Login',
                'errors' => ['Admin password is invalid.'],
            ], 422);

            return;
        }

        session_regenerate_id(true);
        $_SESSION['league_admin_authenticated'] = true;

        $this->redirect('/league/knockout-import');
    }

    /**
     * Ends league admin authenticated session.
     */
    public function adminLogout(): void
    {
        if (!$this->verifyCsrfToken((string) ($_POST['_csrf'] ?? ''))) {
            $this->redirect('/league/admin-login');
        }

        unset($_SESSION['league_admin_authenticated']);

        $this->redirect('/league/admin-login');
    }

    /**
     * @param array<string, string> $formData
     *
     * @return array<int, string>
     */
    private function validateJoinForm(array $formData): array
    {
        $errors = [];

        if ($formData['name'] === '') {
            $errors[] = 'Name is required.';
        }

        if ($formData['team_name'] === '') {
            $errors[] = 'Team name is required.';
        }

        if (!preg_match('/^[0-9+]{8,15}$/', $formData['mobile'])) {
            $errors[] = 'Mobile number must be 8 to 15 digits and may include +.';
        }

        if (strlen($formData['password']) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }

        return $errors;
    }

    /**
     * @param array<string, string> $formData
     *
     * @return array<int, string>
     */
    private function validateMatchForm(array $formData): array
    {
        $errors = [];

        if ($formData['home_team'] === '') {
            $errors[] = 'Home team is required.';
        }

        if ($formData['away_team'] === '') {
            $errors[] = 'Away team is required.';
        }

        if ($formData['home_team'] !== '' && $formData['away_team'] !== '' && $formData['home_team'] === $formData['away_team']) {
            $errors[] = 'Home and away team must be different.';
        }

        $parsed = date_create_from_format('Y-m-d', $formData['match_date']);
        if ($parsed === false || $parsed->format('Y-m-d') !== $formData['match_date']) {
            $errors[] = 'Match date must be a valid Y-m-d value.';
        }

        return $errors;
    }

    /**
     * @param array<string, string> $formData
     *
     * @return array<int, string>
     */
    private function validateMatchAdminUpdateForm(array $formData): array
    {
        $errors = $this->validateMatchForm([
            'match_date' => $formData['match_date'],
            'home_team' => $formData['home_team'],
            'away_team' => $formData['away_team'],
        ]);

        if ($formData['local_time'] !== '') {
            $parsedTime = date_create_from_format('H:i', $formData['local_time']);
            if ($parsedTime === false || $parsedTime->format('H:i') !== $formData['local_time']) {
                $errors[] = 'Local time must use HH:MM format.';
            }
        }

        $homeScoreEmpty = $formData['home_score'] === '';
        $awayScoreEmpty = $formData['away_score'] === '';
        if ($homeScoreEmpty !== $awayScoreEmpty) {
            $errors[] = 'Both home and away scores are required when setting a scoreline.';
        }

        if (!$homeScoreEmpty && (!ctype_digit($formData['home_score']) || (int) $formData['home_score'] > 99)) {
            $errors[] = 'Home score must be a number between 0 and 99.';
        }

        if (!$awayScoreEmpty && (!ctype_digit($formData['away_score']) || (int) $formData['away_score'] > 99)) {
            $errors[] = 'Away score must be a number between 0 and 99.';
        }

        return $errors;
    }

    /**
     * Ensures the request is authenticated as a participant.
     *
     * @return array<string, mixed>
     */
    private function requireParticipant(): array
    {
        $participant = $_SESSION['participant'] ?? null;
        if (!is_array($participant) || !isset($participant['id'])) {
            $this->redirect('/league/login');
        }

        return $participant;
    }

    /**
     * @param array<string, string> $formData
     *
     * @return array<int, string>
     */
    private function validateKnockoutImportForm(array $formData): array
    {
        $errors = [];

        $knockoutStages = array_values(array_filter(
            MatchModel::allowedStages(),
            static fn (string $stage): bool => $stage !== MatchModel::STAGE_GROUP
        ));
        if (!in_array($formData['stage'], $knockoutStages, true)) {
            $errors[] = 'A valid knockout stage is required.';
        }

        $parsedDate = date_create_from_format('Y-m-d', $formData['match_date']);
        if ($parsedDate === false || $parsedDate->format('Y-m-d') !== $formData['match_date']) {
            $errors[] = 'Match date must be a valid Y-m-d value.';
        }

        if ($formData['local_time'] !== '') {
            $parsedTime = date_create_from_format('H:i', $formData['local_time']);
            if ($parsedTime === false || $parsedTime->format('H:i') !== $formData['local_time']) {
                $errors[] = 'Local time must use HH:MM format.';
            }
        }

        if ($formData['home_team'] === '') {
            $errors[] = 'Home team is required.';
        }

        if ($formData['away_team'] === '') {
            $errors[] = 'Away team is required.';
        }

        if ($formData['home_team'] !== '' && $formData['away_team'] !== '' && $formData['home_team'] === $formData['away_team']) {
            $errors[] = 'Home and away team must be different.';
        }

        return $errors;
    }

    /**
     * Parses and validates multiline knockout fixture imports.
     *
     * Supported formats per line:
     * - date|time|home|away|venue|city|notes
     * - stage|date|time|home|away|venue|city|notes
     *
     * @return array{rows: array<int, array<string, string>>, errors: array<int, string>}
     */
    private function parseBulkKnockoutFixtures(string $bulkFixtures, string $defaultStage): array
    {
        $rows = [];
        $errors = [];

        $lines = preg_split('/\r\n|\r|\n/', $bulkFixtures) ?: [];
        foreach ($lines as $index => $rawLine) {
            $line = trim($rawLine);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode('|', $line));
            $lineNumber = $index + 1;

            if (count($parts) !== 7 && count($parts) !== 8) {
                $errors[] = 'Line ' . $lineNumber . ': expected 7 or 8 pipe-separated fields.';
                continue;
            }

            $fixture = count($parts) === 8
                ? [
                    'stage' => (string) $parts[0],
                    'match_date' => (string) $parts[1],
                    'local_time' => (string) $parts[2],
                    'home_team' => (string) $parts[3],
                    'away_team' => (string) $parts[4],
                    'venue' => (string) $parts[5],
                    'venue_city' => (string) $parts[6],
                    'notes' => (string) $parts[7],
                ]
                : [
                    'stage' => $defaultStage,
                    'match_date' => (string) $parts[0],
                    'local_time' => (string) $parts[1],
                    'home_team' => (string) $parts[2],
                    'away_team' => (string) $parts[3],
                    'venue' => (string) $parts[4],
                    'venue_city' => (string) $parts[5],
                    'notes' => (string) $parts[6],
                ];

            $lineErrors = $this->validateKnockoutImportForm($fixture);
            foreach ($lineErrors as $lineError) {
                $errors[] = 'Line ' . $lineNumber . ': ' . $lineError;
            }

            if ($lineErrors === []) {
                $rows[] = $fixture;
            }
        }

        if ($rows === [] && $errors === []) {
            $errors[] = 'Bulk import is empty. Add at least one valid line.';
        }

        return [
            'rows' => $rows,
            'errors' => $errors,
        ];
    }

    /**
     * Ensures the request is authenticated as league admin.
     */
    private function requireLeagueAdmin(): void
    {
        if (($_SESSION['league_admin_authenticated'] ?? false) !== true) {
            $this->redirect('/league/admin-login');
        }
    }
}


