<?php

namespace App\Http\Controllers;

use App\Support\CurrencyCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $settings = DB::table('inventory_settings')->pluck('value', 'key');
        $currencies = CurrencyCatalog::all();
        $storedCurrencyCode = $settings['currency_code'] ?? 'USD';
        $currencyCode = old('currency_code', $storedCurrencyCode);
        if (! isset($currencies[$currencyCode])) {
            $currencyCode = isset($currencies[$storedCurrencyCode]) ? $storedCurrencyCode : 'USD';
        }

        return view('settings.edit', [
            'currencies' => $currencies,
            'currency_code' => $currencyCode,
            'currency_symbol' => $currencies[$currencyCode]['symbol'],
            'vat_rate' => $settings['vat_rate'] ?? '0',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'currency_code' => ['required', 'string', Rule::in(array_keys(CurrencyCatalog::all()))],
            'vat_rate' => ['required', 'numeric', 'between:0,100'],
        ]);
        $data['currency_symbol'] = CurrencyCatalog::all()[$data['currency_code']]['symbol'];

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
