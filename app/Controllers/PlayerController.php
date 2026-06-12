<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\PlayerModel;

/**
 * Handles players CRUD actions.
 */
class PlayerController extends BaseController
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
     * Lists all players.
     */
    public function index(): void
    {
        $playerModel = new PlayerModel($this->databaseConfig);

        $this->render('players.index', [
            'title' => 'Players',
            'players' => $playerModel->all(),
        ]);
    }

    /**
     * Shows a single player details page.
     */
    public function show(string $id): void
    {
        $playerModel = new PlayerModel($this->databaseConfig);
        $player = $playerModel->findById((int) $id);

        if ($player === null) {
            $this->render('errors.404', ['title' => 'Player Not Found'], 404);
            return;
        }

        $this->render('players.show', [
            'title' => 'Player ' . $player['name'],
            'player' => $player,
        ]);
    }

    /**
     * Displays a create-player form.
     */
    public function createForm(): void
    {
        $this->render('players.create', [
            'title' => 'Create Player',
            'errors' => [],
            'formData' => ['name' => '', 'email' => '', 'level' => '1'],
        ]);
    }

    /**
     * Persists a new player from request data.
     */
    public function store(): void
    {
        $formData = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'level' => trim((string) ($_POST['level'] ?? '1')),
        ];

        $errors = $this->validateForm($formData);
        if ($errors !== []) {
            $this->render('players.create', [
                'title' => 'Create Player',
                'errors' => $errors,
                'formData' => $formData,
            ], 422);
            return;
        }

        $playerModel = new PlayerModel($this->databaseConfig);
        $newId = $playerModel->create($formData['name'], $formData['email'], (int) $formData['level']);

        $this->redirect('/players/' . (string) $newId);
    }

    /**
     * Displays an edit form for an existing player.
     */
    public function editForm(string $id): void
    {
        $playerModel = new PlayerModel($this->databaseConfig);
        $player = $playerModel->findById((int) $id);

        if ($player === null) {
            $this->render('errors.404', ['title' => 'Player Not Found'], 404);
            return;
        }

        $this->render('players.edit', [
            'title' => 'Edit Player',
            'errors' => [],
            'playerId' => (int) $id,
            'formData' => [
                'name' => (string) $player['name'],
                'email' => (string) $player['email'],
                'level' => (string) $player['level'],
            ],
        ]);
    }

    /**
     * Updates an existing player from request data.
     */
    public function update(string $id): void
    {
        $formData = [
            'name' => trim((string) ($_POST['name'] ?? '')),
            'email' => trim((string) ($_POST['email'] ?? '')),
            'level' => trim((string) ($_POST['level'] ?? '1')),
        ];

        $errors = $this->validateForm($formData);
        if ($errors !== []) {
            $this->render('players.edit', [
                'title' => 'Edit Player',
                'errors' => $errors,
                'playerId' => (int) $id,
                'formData' => $formData,
            ], 422);
            return;
        }

        $playerModel = new PlayerModel($this->databaseConfig);
        $playerModel->updateById((int) $id, $formData['name'], $formData['email'], (int) $formData['level']);

        $this->redirect('/players/' . $id);
    }

    /**
     * Deletes a player.
     */
    public function destroy(string $id): void
    {
        $playerModel = new PlayerModel($this->databaseConfig);
        $playerModel->deleteById((int) $id);

        $this->redirect('/players');
    }

    /**
     * Validates incoming player form data.
     *
     * @param array<string, string> $formData Submitted values.
     *
     * @return array<int, string>
     */
    private function validateForm(array $formData): array
    {
        $errors = [];

        if ($formData['name'] === '') {
            $errors[] = 'Name is required.';
        }

        if ($formData['email'] === '' || filter_var($formData['email'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'A valid email is required.';
        }

        $levelValue = filter_var($formData['level'], FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 100],
        ]);
        if ($levelValue === false) {
            $errors[] = 'Level must be an integer between 1 and 100.';
        }

        return $errors;
    }
}
