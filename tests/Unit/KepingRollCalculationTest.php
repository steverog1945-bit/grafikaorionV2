<?php

namespace Tests\Unit;

use App\Exceptions\ValidasiNotaException;
use App\Services\PenjualanCheckoutService;
use PHPUnit\Framework\TestCase;

class KepingRollCalculationTest extends TestCase
{
    public function test_meter_jahit_is_number_of_pieces_times_piece_length_in_meters(): void
    {
        $meter = PenjualanCheckoutService::meterKepingTerjaga([
            'keping_lebar' => 50,
            'keping_panjang' => 500,
            'keping_jumlah' => 2,
        ], 500, 120, 'ukuran');

        $this->assertSame(10.0, $meter);
    }

    public function test_rejects_piece_count_that_exceeds_print_size_capacity(): void
    {
        $this->expectException(ValidasiNotaException::class);

        PenjualanCheckoutService::meterKepingTerjaga([
            'keping_lebar' => 50,
            'keping_panjang' => 400,
            'keping_jumlah' => 3,
        ], 500, 120, 'ukuran');
    }

    public function test_rejects_piece_larger_than_print_dimensions(): void
    {
        $this->expectException(ValidasiNotaException::class);

        PenjualanCheckoutService::meterKepingTerjaga([
            'keping_lebar' => 50,
            'keping_panjang' => 600,
            'keping_jumlah' => 2,
        ], 500, 120, 'ukuran');
    }

    public function test_rejects_fractional_piece_count_instead_of_silently_rounding_down(): void
    {
        $this->expectException(ValidasiNotaException::class);

        PenjualanCheckoutService::meterKepingTerjaga([
            'keping_lebar' => 50,
            'keping_panjang' => 500,
            'keping_jumlah' => '1.5',
        ], 500, 120, 'ukuran');
    }
}
