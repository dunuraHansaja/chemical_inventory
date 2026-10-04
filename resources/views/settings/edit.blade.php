@extends('layouts.app')
@section('title', 'Settings')
@section('subtitle', 'Configure currency display and the default VAT rate for new purchases.')
@section('content')
<form method="POST" action="{{ route('settings.update') }}" class="panel form-panel form-narrow">@csrf @method('PUT')
<div class="form-section"><span class="eyebrow">REGIONAL &amp; TAX</span><h2>Display and VAT defaults</h2><p class="form-hint">Changing these defaults does not alter rates already saved on purchase orders.</p><div class="form-grid">
<label>Currency code <span class="required">*</span><input name="currency_code" value="{{ old('currency_code', $currency_code) }}" minlength="3" maxlength="3" placeholder="USD" required><small>Three-letter ISO code, e.g. USD, EUR, LKR.</small></label>
<label>Currency symbol <span class="required">*</span><input name="currency_symbol" value="{{ old('currency_symbol', $currency_symbol) }}" maxlength="5" placeholder="$" required></label>
<label>Default VAT rate (%) <span class="required">*</span><input type="number" name="vat_rate" min="0" max="100" step="0.01" value="{{ old('vat_rate', $vat_rate) }}" required><small>Applied by default to new purchase orders. Existing orders keep their saved VAT rate.</small></label>
</div></div><div class="form-actions"><button class="button button-primary" type="submit">Save settings <span>→</span></button></div></form>
<section class="panel settings-note"><span class="eyebrow">ACCOUNT SECURITY</span><h2>Administrator access</h2><p>Sign-in is required for all inventory screens. The one-time setup page is disabled after the first administrator account is created.</p></section>
@endsection
