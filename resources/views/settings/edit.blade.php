@extends('layouts.app')
@section('title', 'Settings')
@section('subtitle', 'Configure currency display and the default VAT rate for new purchases.')

@section('content')
<form method="POST" action="{{ route('settings.update') }}" class="panel form-panel form-narrow" @if(auth()->user()->hasPermission('settings.update')) data-confirm="Save these currency and VAT settings?" data-confirm-title="Save settings?" data-confirm-action="Save Settings" data-confirm-tone="primary" @endif>
    @csrf
    @method('PUT')

    <div class="form-section">
        <span class="eyebrow">REGIONAL &amp; TAX</span>
        <h2>Display and VAT defaults</h2>
        <p class="form-hint">Changing these defaults does not alter rates already saved on existing purchase orders.</p>

        <div class="form-grid">
            <label for="currency-code">Currency code <span class="required">*</span>
                <select id="currency-code" name="currency_code" data-currency-code required @disabled(!auth()->user()->hasPermission('settings.update'))>
                    @foreach($currencies as $code => $currency)
                        <option value="{{ $code }}" data-symbol="{{ $currency['symbol'] }}" @selected($currency_code === $code)>{{ $code }} — {{ $currency['name'] }}</option>
                    @endforeach
                </select>
                <small>Select the currency used throughout the system.</small>
            </label>

            <label for="currency-symbol">Currency symbol <span class="required">*</span>
                <input id="currency-symbol" name="currency_symbol" value="{{ $currency_symbol }}" data-currency-symbol readonly required @disabled(!auth()->user()->hasPermission('settings.update'))>
                <small>Automatically matched to the selected currency code.</small>
            </label>

            <label class="span-2">Default VAT rate (%) <span class="required">*</span>
                <input type="number" name="vat_rate" min="0" max="100" step="0.01" value="{{ old('vat_rate', $vat_rate) }}" required @disabled(!auth()->user()->hasPermission('settings.update'))>
                <small>Applied by default when drafting new purchase orders. Existing orders retain their historical rate.</small>
            </label>
        </div>
    </div>

    @if(auth()->user()->hasPermission('settings.update'))
        <div class="form-actions">
            <button class="button button-primary" type="submit">Save settings <span>→</span></button>
        </div>
    @endif
</form>

<section class="panel settings-note form-narrow" style="margin: 20px auto 0 auto;">
    <span class="eyebrow">ACCOUNT SECURITY</span>
    <h2>Administrator access</h2>
    <p>Sign-in is required for all inventory screens. The one-time setup page is disabled after the first administrator account is created.</p>
</section>
@endsection
