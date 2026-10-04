<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $settings = DB::table('inventory_settings')->pluck('value', 'key');

        return view('settings.edit', [
            'currency_code' => $settings['currency_code'] ?? 'USD',
            'currency_symbol' => $settings['currency_symbol'] ?? '$',
            'vat_rate' => $settings['vat_rate'] ?? '0',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'currency_code' => ['required', 'string', 'size:3', 'alpha'],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'vat_rate' => ['required', 'numeric', 'between:0,100'],
        ]);

        foreach ($data as $key => $value) {
            $setting = DB::table('inventory_settings')->where('key', $key);
            if ($setting->exists()) {
                $setting->update(['value' => (string) $value, 'updated_at' => now()]);
            } else {
                DB::table('inventory_settings')->insert([
                    'key' => $key,
                    'value' => (string) $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return back()->with('success', 'Settings updated.');
    }
}
