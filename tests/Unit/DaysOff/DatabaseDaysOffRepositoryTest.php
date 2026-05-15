<?php

use EnricoDeLazzari\ClaudeMonitor\DaysOff\Contracts\DaysOffRepository;
use EnricoDeLazzari\ClaudeMonitor\DaysOff\Repositories\DatabaseDaysOffRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->repo = app(DaysOffRepository::class);
    $this->may = Carbon::parse('2026-05-15');
});

it('resolves to the database repository from the container', function () {
    expect($this->repo)->toBeInstanceOf(DatabaseDaysOffRepository::class);
});

it('returns empty array when no days off exist for the month', function () {
    expect($this->repo->datesForMonth($this->may))->toBe([]);
});

it('returns formatted dates for the given month', function () {
    $this->repo->add('2026-05-06');
    $this->repo->add('2026-05-20');

    expect($this->repo->datesForMonth($this->may))->toBe(['2026-05-06', '2026-05-20']);
});

it('does not return dates from other months', function () {
    $this->repo->add('2026-04-30');
    $this->repo->add('2026-05-06');
    $this->repo->add('2026-06-01');

    expect($this->repo->datesForMonth($this->may))->toBe(['2026-05-06']);
});

it('counts days off for the given month', function () {
    $this->repo->add('2026-05-06');
    $this->repo->add('2026-05-07');

    expect($this->repo->countForMonth($this->may))->toBe(2);
});

it('returns zero count when no days off exist for the month', function () {
    expect($this->repo->countForMonth($this->may))->toBe(0);
});

it('does not count days off from other months', function () {
    $this->repo->add('2026-04-30');
    $this->repo->add('2026-05-06');

    expect($this->repo->countForMonth($this->may))->toBe(1);
});

it('reports existence with has', function () {
    expect($this->repo->has('2026-05-06'))->toBeFalse();

    $this->repo->add('2026-05-06');

    expect($this->repo->has('2026-05-06'))->toBeTrue();
});

it('persists a note when provided', function () {
    $this->repo->add('2026-05-06', 'company event');

    expect($this->repo->has('2026-05-06'))->toBeTrue();
});

it('overwrites the note on duplicate add', function () {
    $this->repo->add('2026-05-06', 'first note');
    $this->repo->add('2026-05-06', 'updated note');

    expect($this->repo->countForMonth($this->may))->toBe(1);
});

it('removes a date with remove', function () {
    $this->repo->add('2026-05-06');
    $this->repo->remove('2026-05-06');

    expect($this->repo->has('2026-05-06'))->toBeFalse()
        ->and($this->repo->datesForMonth($this->may))->toBe([]);
});

it('remove is a no-op when date does not exist', function () {
    $this->repo->remove('2026-05-06');

    expect($this->repo->countForMonth($this->may))->toBe(0);
});
