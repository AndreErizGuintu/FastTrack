<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $response = Http::timeout(6)
            ->retry(2, 200)
            ->acceptJson()
            ->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $validated['lat'],
                'longitude' => $validated['lng'],
                'current' => 'temperature_2m,precipitation,weather_code,wind_speed_10m',
                'hourly' => 'precipitation_probability,precipitation',
                'forecast_days' => 1,
                'timezone' => 'auto',
            ]);

        if (!$response->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'Weather data temporarily unavailable.',
            ], 502);
        }

        $payload = $response->json();
        $current = $payload['current'] ?? [];
        $hourly = $payload['hourly'] ?? [];

        $precip = (float)($current['precipitation'] ?? 0);
        $wind = (float)($current['wind_speed_10m'] ?? 0);
        $temp = $current['temperature_2m'] ?? null;
        $code = $current['weather_code'] ?? null;
        $currentTime = $current['time'] ?? null;

        $riskLevel = 'low';
        $advisory = 'Clear skies. Expect normal delivery times.';

        if ($precip >= 5 || $wind >= 35) {
            $riskLevel = 'high';
            $advisory = 'Severe weather nearby. Expect slower courier movement.';
        } elseif ($precip >= 1 || $wind >= 20) {
            $riskLevel = 'medium';
            $advisory = 'Light weather disturbances. Add buffer time for delivery.';
        }

        $hourlyPreview = [];
        if (!empty($hourly['time'])) {
            $probabilities = $hourly['precipitation_probability'] ?? [];
            $amounts = $hourly['precipitation'] ?? [];

            foreach ($hourly['time'] as $index => $time) {
                if (count($hourlyPreview) >= 3) {
                    break;
                }

                $hourlyPreview[] = [
                    'time' => $time,
                    'precipitation_probability' => $probabilities[$index] ?? null,
                    'precipitation_mm' => $amounts[$index] ?? null,
                ];
            }
        }

        return [
            'success' => true,
            'meta' => [
                'risk_level' => $riskLevel,
                'advisory' => $advisory,
                'source' => 'open-meteo',
                'queried_at' => now()->toIso8601String(),
                'current_time' => $currentTime,
            ],
            'current' => [
                'temperature_c' => $temp,
                'precipitation_mm' => $precip,
                'wind_speed_kph' => $wind * 3.6,
                'weather_code' => $code,
            ],
            'hourly_preview' => $hourlyPreview,
        ];
    }
}
