<?php

use EnricoDeLazzari\ClaudeMonitor\Settings\Contracts\SettingsRepository;
use EnricoDeLazzari\ClaudeMonitor\Settings\Repositories\DatabaseSettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repo = app(SettingsRepository::class);
});

it('resolves to the database repository from the container', function () {
    expect($this->repo)->toBeInstanceOf(DatabaseSettingsRepository::class);
});

it('returns the default when a key is missing', function () {
    expect($this->repo->get('missing', 'fallback'))->toBe('fallback')
        ->and($this->repo->get('missing'))->toBeNull();
});

it('persists a value through set and reads it back via get', function () {
    $this->repo->set('monthly_budget', '500');

    expect($this->repo->get('monthly_budget'))->toBe('500');
});

it('overwrites an existing key on set', function () {
    $this->repo->set('holidays_country', 'it');
    $this->repo->set('holidays_country', 'fr');

    expect($this->repo->get('holidays_country'))->toBe('fr');
});

it('reports existence with has', function () {
    expect($this->repo->has('session_key'))->toBeFalse();

    $this->repo->set('session_key', 'abc');

    expect($this->repo->has('session_key'))->toBeTrue();
});

it('removes a key with forget', function () {
    $this->repo->set('contingency_pct', '10');
    $this->repo->forget('contingency_pct');

    expect($this->repo->has('contingency_pct'))->toBeFalse()
        ->and($this->repo->get('contingency_pct'))->toBeNull();
});

it('returns all settings as a key => value map', function () {
    $this->repo->set('monthly_budget', '500');
    $this->repo->set('holidays_country', 'it');

    expect($this->repo->all())->toBe([
        'monthly_budget' => '500',
        'holidays_country' => 'it',
    ]);
});
