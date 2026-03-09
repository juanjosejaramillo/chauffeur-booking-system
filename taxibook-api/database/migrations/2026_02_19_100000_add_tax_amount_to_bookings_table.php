<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('tax_amount', 10, 2)->default(0)->after('extras_total');
        });

        // Backfill existing bookings: extract tax from fare_breakdown JSON
        DB::table('bookings')->whereNotNull('fare_breakdown')->orderBy('id')->chunk(100, function ($bookings) {
            foreach ($bookings as $booking) {
                $breakdown = json_decode($booking->fare_breakdown, true);

                if (!$breakdown || !isset($breakdown['tax']['amount'])) {
                    continue;
                }

                $taxAmount = round((float) $breakdown['tax']['amount'], 2);
                if ($taxAmount <= 0) {
                    continue;
                }

                // Subtract tax from estimated_fare (it was baked in)
                $newEstimatedFare = round((float) $booking->estimated_fare - $taxAmount, 2);

                $updates = [
                    'tax_amount' => $taxAmount,
                    'estimated_fare' => $newEstimatedFare,
                ];

                // If final_fare equals the old estimated_fare, adjust it too
                if ($booking->final_fare !== null && (float) $booking->final_fare == (float) $booking->estimated_fare) {
                    $updates['final_fare'] = $newEstimatedFare;
                }

                DB::table('bookings')->where('id', $booking->id)->update($updates);
            }
        });
    }

    public function down(): void
    {
        // Re-bake tax into estimated_fare before dropping the column
        DB::table('bookings')->where('tax_amount', '>', 0)->orderBy('id')->chunk(100, function ($bookings) {
            foreach ($bookings as $booking) {
                $restoredFare = round((float) $booking->estimated_fare + (float) $booking->tax_amount, 2);

                $updates = ['estimated_fare' => $restoredFare];

                if ($booking->final_fare !== null && (float) $booking->final_fare == (float) $booking->estimated_fare) {
                    $updates['final_fare'] = $restoredFare;
                }

                DB::table('bookings')->where('id', $booking->id)->update($updates);
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('tax_amount');
        });
    }
};
