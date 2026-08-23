<?php

use App\Support\ClockTime;

it('normalizes mysql time and html time to hh:mm', function () {
    expect(ClockTime::toHi('19:00:00'))->toBe('19:00')
        ->and(ClockTime::toHi('9:05'))->toBe('09:05')
        ->and(ClockTime::toHis('19:00'))->toBe('19:00:00');
});

it('compares start and end without string-order bugs', function () {
    expect(ClockTime::minutes('19:00:00'))->toBeLessThan(ClockTime::minutes('22:00'));
});
