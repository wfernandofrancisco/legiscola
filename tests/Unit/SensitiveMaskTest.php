<?php

use App\Support\SensitiveMask;

it('formata CPF completo com pontos e hífen', function () {
    expect(SensitiveMask::cpfFormatted('52998224725'))->toBe('529.982.247-25')
        ->and(SensitiveMask::cpfFormatted('529.982.247-25'))->toBe('529.982.247-25')
        ->and(SensitiveMask::cpfFormatted(null))->toBe('—')
        ->and(SensitiveMask::cpfFormatted(''))->toBe('—');
});
