<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Materials stock sheet · ChemStock</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@100..900&display=swap');
        body { margin: 24px; color: #263746; font: calc(12px * 1.15) 'Roboto', Arial, sans-serif; }
        header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; }
        h1 { margin: 0 0 5px; font-size: calc(22px * 1.15); }
        p { margin: 0; color: #637487; }
        .print-actions { margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d9e1e8; padding: 8px; text-align: left; }
        th { background: #f0f4f8; font-size: calc(10px * 1.15); text-transform: uppercase; }
        @media print {
            body { margin: 0; }
            .print-actions { display: none; }
            button { display: none; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
        }
    </style>
</head>
<body>
    <header>
        <div><h1>Materials stock sheet</h1><p>Generated {{ now()->format('d M Y, H:i') }} · {{ $materials->count() }} materials</p></div>
    </header>
    <div class="print-actions"><button class="button button-primary" type="button" data-confirm="The stock sheet will open in your print dialog." data-confirm-title="Print stock sheet?" data-confirm-action="Print Stock Sheet" data-confirm-tone="primary" data-confirm-print>Print stock sheet</button></div>
    <table>
        <thead><tr><th>Code</th><th>Material</th><th>CAS no.</th><th>Category</th><th>On hand</th><th>Reorder at</th><th>Next expiry</th><th>Location</th><th>Status</th></tr></thead>
        <tbody>
        @foreach($materials as $material)
            @php($onHand = (float) ($material->lots_sum_quantity_on_hand ?? 0))
            @php($status = !$material->is_active ? 'Inactive' : ($onHand <= 0 ? 'Out of stock' : ($onHand <= (float) $material->reorder_level ? 'Low stock' : 'In stock')))
            <tr>
                <td>{{ $material->item_code }}</td>
                <td>{{ $material->name }}</td>
                <td>{{ $material->cas_number ?: '—' }}</td>
                <td>{{ $material->category?->name ?? 'Uncategorized' }}</td>
                <td>{{ number_format($onHand, 3) }} {{ $material->stock_unit }}</td>
                <td>{{ number_format((float) $material->reorder_level, 3) }} {{ $material->stock_unit }}</td>
                <td>{{ $material->next_expiry_date ? \Illuminate\Support\Carbon::parse($material->next_expiry_date)->format('Y-m-d') : '—' }}</td>
                <td>{{ $material->storage_location ?: $material->effective_storage_location ?: '—' }}</td>
                <td>{{ $status }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @include('components.confirmation-dialog')
</body>
</html>
