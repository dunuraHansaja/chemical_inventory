<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_time_setup_creates_the_only_publicly_registerable_user(): void
    {
        $this->get('/login')->assertRedirect('/setup');

        $this->post('/setup', [
            'name' => 'Inventory Admin',
            'email' => 'admin@example.test',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ])->assertRedirect('/dashboard');
        $this->assertAuthenticated();

        $this->get('/setup')->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'admin@example.test']);
        $this->assertDatabaseHas('inventory_settings', ['key' => 'administrator_initialized', 'value' => 'true']);
        $admin = User::where('email', 'admin@example.test')->firstOrFail();
        $this->assertTrue($admin->role->is_system);
        $this->assertTrue($admin->hasPermission('dashboard.view'));
    }

    public function test_material_catalogue_crud_and_authenticated_dashboard_are_available(): void
    {
        $user = User::factory()->create();
        $this->assertDatabaseHas('roles', ['id' => $user->role_id, 'is_system' => true]);
        $this->assertTrue($user->hasPermission('dashboard.view'));
        $this->actingAs($user);

        $category = Category::create(['name' => 'Solvents', 'is_active' => true]);
        $supplier = Supplier::create(['supplier_code' => 'SUP-001', 'name' => 'Lab Supply Co', 'is_active' => true]);

        $this->get('/dashboard')->assertOk()->assertSee('Your inventory, at a glance.');
        $this->post('/materials', [
            'item_code' => 'ETH-001',
            'name' => 'Ethanol',
            'cas_number' => '64-17-5',
            'category_id' => $category->id,
            'preferred_supplier_id' => $supplier->id,
            'stock_unit' => 'L',
            'reorder_level' => 5,
            'cost_per_unit' => 10.50,
            'hazard_classes' => 'Flammable, Irritant',
            'is_active' => '1',
        ])->assertRedirect()
            ->assertSessionHas('success_title', 'Material Added')
            ->assertSessionHas('success', 'The material “Ethanol” was added successfully.');

        $material = Material::where('item_code', 'ETH-001')->firstOrFail();
        $this->assertSame(['Flammable', 'Irritant'], $material->hazard_classes);
        $this->get('/materials/'.$material->id)->assertOk()->assertSee('Ethanol')->assertSee('Flammable, Irritant');
        $this->get('/materials/'.$material->id.'/edit')->assertOk()->assertSee('data-confirm', false);

        $this->put('/materials/'.$material->id, [
            'item_code' => 'ETH-001',
            'name' => 'Ethanol 96%',
            'category_id' => $category->id,
            'stock_unit' => 'L',
            'reorder_level' => 5,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('materials', ['id' => $material->id, 'name' => 'Ethanol 96%']);
    }

    public function test_all_inventory_screens_render_for_an_authenticated_user(): void
    {
        $this->actingAs(User::factory()->create());

        foreach ([
            '/dashboard',
            '/materials',
            '/materials/create',
            '/suppliers',
            '/suppliers/create',
            '/categories',
            '/categories/create',
            '/lots',
            '/lots/create',
            '/stock/adjustments/create',
            '/stock/movements',
            '/purchases',
            '/purchases/create',
            '/production',
            '/production/create',
            '/settings',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_currency_and_vat_settings_persist_and_are_used_as_purchase_defaults(): void
    {
        $this->actingAs(User::factory()->create());

        $this->from('/settings')->put('/settings', [
            'currency_code' => 'EUR',
            'currency_symbol' => 'not-the-euro-symbol',
            'vat_rate' => 21,
        ])->assertRedirect('/settings');

        $this->assertDatabaseHas('inventory_settings', ['key' => 'currency_code', 'value' => 'EUR']);
        $this->assertDatabaseHas('inventory_settings', ['key' => 'currency_symbol', 'value' => '€']);
        $this->assertDatabaseHas('inventory_settings', ['key' => 'vat_rate', 'value' => '21']);
        $this->get('/purchases/create')->assertOk()->assertSee('value="21"', false);
        $this->get('/dashboard')->assertOk()->assertSee('EUR');
        $this->get('/settings')->assertOk()
            ->assertSee('<select id="currency-code" name="currency_code" data-currency-code', false)
            ->assertSee('<option value="EUR" data-symbol="€" selected>', false)
            ->assertSee('id="currency-symbol" name="currency_symbol" value="€" data-currency-symbol readonly', false)
            ->assertSee('data-confirm', false);

        $this->from('/settings')->put('/settings', [
            'currency_code' => 'XYZ',
            'vat_rate' => 21,
        ])->assertRedirect('/settings')->assertSessionHasErrors('currency_code');
        $this->assertDatabaseHas('inventory_settings', ['key' => 'currency_code', 'value' => 'EUR']);
    }

    public function test_supplier_and_category_records_can_be_created_updated_and_deleted_when_unused(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/suppliers', [
            'supplier_code' => 'SUP-CRUD',
            'name' => 'Initial supplier',
            'is_active' => '1',
        ])->assertRedirect('/suppliers')
            ->assertSessionHas('success_title', 'Supplier Added');
        $supplier = Supplier::where('supplier_code', 'SUP-CRUD')->firstOrFail();
        $this->get('/suppliers/'.$supplier->id.'/edit')->assertOk()->assertSee('data-confirm', false);
        $this->get('/suppliers')->assertOk()->assertSee('data-confirm', false);
        $this->put('/suppliers/'.$supplier->id, [
            'supplier_code' => 'SUP-CRUD',
            'name' => 'Updated supplier',
            'is_active' => '1',
        ])->assertRedirect('/suppliers');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Updated supplier']);
        $this->delete('/suppliers/'.$supplier->id)
            ->assertRedirect('/suppliers')
            ->assertSessionHas('warning', 'The supplier “Updated supplier” was deleted.');
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);

        $this->post('/categories', ['name' => 'Initial category', 'is_active' => '1'])
            ->assertRedirect('/categories')
            ->assertSessionHas('success_title', 'Category Added');
        $category = Category::where('name', 'Initial category')->firstOrFail();
        $this->get('/categories/'.$category->id.'/edit')->assertOk()->assertSee('data-confirm', false);
        $this->get('/categories')->assertOk()->assertSee('data-confirm', false);
        $this->put('/categories/'.$category->id, ['name' => 'Updated category', 'is_active' => '1'])->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated category']);
        $this->delete('/categories/'.$category->id)
            ->assertRedirect('/categories')
            ->assertSessionHas('warning', 'The category “Updated category” was deleted.');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_invalid_record_creation_shows_relevant_error_notification(): void
    {
        $this->actingAs(User::factory()->create());

        $this->followingRedirects()
            ->from('/categories/create')
            ->post('/categories', ['name' => ''])
            ->assertOk()
            ->assertSee('toast-error', false)
            ->assertSee('Please correct the following issues')
            ->assertSee('The name field is required.');

        $this->assertDatabaseCount('categories', 0);
    }

    public function test_stock_receipts_and_adjustments_update_the_lot_and_reject_negative_balance(): void
    {
        $user = User::factory()->create();
        $material = Material::create([
            'item_code' => 'LOT-001',
            'name' => 'Test chemical',
            'stock_unit' => 'kg',
            'reorder_level' => 1,
            'is_active' => true,
        ]);
        $this->actingAs($user)->post('/lots', [
            'material_id' => $material->id,
            'lot_number' => 'BATCH-001',
            'quantity_received' => 10,
            'unit_cost' => 3.5,
        ])->assertRedirect('/lots')
            ->assertSessionHas('success_title', 'Stock Lot Added');

        $lot = MaterialLot::where('lot_number', 'BATCH-001')->firstOrFail();
        $this->post('/stock/adjustments', [
            'material_lot_id' => $lot->id,
            'direction' => 'out',
            'quantity' => 2.5,
            'notes' => 'Damaged container',
        ])->assertRedirect('/stock/movements')
            ->assertSessionHas('success_title', 'Stock Adjustment Recorded');
        $this->assertSame('7.500', $lot->fresh()->quantity_on_hand);

        $this->post('/stock/adjustments', [
            'material_lot_id' => $lot->id,
            'direction' => 'out',
            'quantity' => 8,
            'notes' => 'Overdraw attempt',
        ])->assertSessionHasErrors('quantity');
        $this->assertSame('7.500', $lot->fresh()->quantity_on_hand);
        $this->assertSame(2, StockMovement::count());
    }

    public function test_material_csv_import_and_export_are_available(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/materials/import', [
            'file' => UploadedFile::fake()->createWithContent(
                'materials.csv',
                "item_code,name,stock_unit,reorder_level,category\nCSV-001,Imported chemical,kg,2,Reagents\n",
            ),
        ])->assertRedirect('/materials')
            ->assertSessionHas('success_title', 'Materials Imported');
        $this->assertDatabaseHas('materials', ['item_code' => 'CSV-001', 'name' => 'Imported chemical']);
        $this->assertDatabaseHas('categories', ['name' => 'Reagents']);

        $this->get('/materials/export')->assertOk()->assertHeader('content-disposition', 'attachment; filename=materials.csv');
    }

    public function test_materials_page_uses_database_stock_data_for_summaries_filters_and_table(): void
    {
        $this->actingAs(User::factory()->create());
        $category = Category::create(['name' => 'Raw Materials', 'is_active' => true]);
        $supplier = Supplier::create(['supplier_code' => 'SUP-MAT', 'name' => 'Regional Chemical Supply', 'is_active' => true]);

        $inStock = Material::create([
            'item_code' => 'RM-001',
            'name' => 'Sodium hydroxide',
            'cas_number' => '1310-73-2',
            'category_id' => $category->id,
            'preferred_supplier_id' => $supplier->id,
            'storage_location' => 'Acid store, Bay 2',
            'stock_unit' => 'kg',
            'reorder_level' => 10,
            'is_active' => true,
        ]);
        MaterialLot::create([
            'material_id' => $inStock->id,
            'lot_number' => 'LOT-RM-001',
            'expiry_date' => today()->addDays(30),
            'quantity_received' => 20,
            'quantity_on_hand' => 20,
            'status' => 'active',
        ]);

        $lowStock = Material::create([
            'item_code' => 'RM-002',
            'name' => 'Glutaraldehyde',
            'category_id' => $category->id,
            'stock_unit' => 'L',
            'reorder_level' => 10,
            'is_active' => true,
        ]);
        MaterialLot::create([
            'material_id' => $lowStock->id,
            'lot_number' => 'LOT-RM-002',
            'quantity_received' => 4,
            'quantity_on_hand' => 4,
            'status' => 'active',
        ]);

        Material::create([
            'item_code' => 'RM-003',
            'name' => 'Out of stock reagent',
            'category_id' => $category->id,
            'stock_unit' => 'kg',
            'reorder_level' => 2,
            'is_active' => true,
        ]);

        $this->get('/materials')
            ->assertOk()
            ->assertSee('mobile-bottom-nav', false)
            ->assertSee('mobile-menu-toggle', false)
            ->assertSee('Total Materials')
            ->assertSee('In Stock')
            ->assertSee('Low Stock')
            ->assertSee('Out of Stock')
            ->assertSee('Expiring Soon')
            ->assertSee('1310-73-2')
            ->assertSee(today()->addDays(30)->toDateString())
            ->assertSee('Acid store, Bay 2')
            ->assertSee('data-label="CAS No."', false)
            ->assertSee('data-label="On Hand"', false)
            ->assertSee('data-label="Status"', false)
            ->assertSee('Raw Materials')
            ->assertSee('3 materials');

        $this->get('/materials?status=low_stock')
            ->assertOk()
            ->assertSee('Glutaraldehyde')
            ->assertDontSee('Sodium hydroxide');
        $this->get('/materials?category_id='.$category->id.'&q=Regional%20Chemical')
            ->assertOk()
            ->assertSee('Sodium hydroxide');
        $this->get('/materials?status=out_of_stock')
            ->assertOk()
            ->assertSee('Out of stock reagent')
            ->assertDontSee('Glutaraldehyde');
        $this->get('/materials?per_page=25&sort=code&direction=desc')
            ->assertOk()
            ->assertSee('value="25" selected', false)
            ->assertSee('<th>Code</th>', false)
            ->assertDontSee('materials-sort-link')
            ->assertDontSee('aria-sort');
    }

    public function test_material_stock_sheet_print_and_selected_csv_export_use_database_values(): void
    {
        $this->actingAs(User::factory()->create());
        $first = Material::create([
            'item_code' => 'SHEET-001',
            'name' => 'Print test material',
            'cas_number' => '123-45-6',
            'storage_location' => 'Cabinet A',
            'stock_unit' => 'kg',
            'reorder_level' => 1,
            'is_active' => true,
        ]);
        MaterialLot::create([
            'material_id' => $first->id,
            'lot_number' => 'SHEET-LOT-001',
            'expiry_date' => today()->addDays(45),
            'quantity_received' => 8,
            'quantity_on_hand' => 8,
            'status' => 'active',
        ]);
        Material::create([
            'item_code' => 'SHEET-002',
            'name' => 'Not selected material',
            'stock_unit' => 'L',
            'reorder_level' => 0,
            'is_active' => true,
        ]);

        $this->get('/materials/print?ids[]='.$first->id)
            ->assertOk()
            ->assertSee('<title>INVENTROO</title>', false)
            ->assertSee('Materials stock sheet')
            ->assertSee('Print test material')
            ->assertSee('123-45-6')
            ->assertDontSee('Not selected material');

        $export = $this->get('/materials/export?ids[]='.$first->id)
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=materials.csv');
        $content = $export->streamedContent();
        $this->assertStringContainsString('Print test material', $content);
        $this->assertStringContainsString('Cabinet A', $content);
        $this->assertStringNotContainsString('Not selected material', $content);
    }

    public function test_purchase_receipt_creates_stock_lot_and_audited_movement(): void
    {
        $user = User::factory()->create();
        $supplier = Supplier::create(['supplier_code' => 'SUP-002', 'name' => 'Chemical House', 'is_active' => true]);
        $material = Material::create([
            'item_code' => 'ACD-001',
            'name' => 'Acetic acid',
            'stock_unit' => 'L',
            'reorder_level' => 2,
            'is_active' => true,
        ]);

        $this->actingAs($user)->post('/purchases', [
            'supplier_id' => $supplier->id,
            'ordered_at' => today()->toDateString(),
            'vat_rate' => 15,
            'items' => [['material_id' => $material->id, 'quantity' => 12.5, 'unit_cost' => 4.25]],
        ])->assertRedirect()
            ->assertSessionHas('success_title', 'Purchase Order Added');
        $purchase = PurchaseOrder::firstOrFail();
        $this->assertSame('draft', $purchase->status);
        $this->get('/purchases/'.$purchase->id.'/edit')->assertOk()->assertSee('data-confirm', false);
        $this->get('/purchases/'.$purchase->id)->assertOk()->assertSee('data-confirm', false);
        $this->post('/purchases/'.$purchase->id.'/receive')
            ->assertRedirect()
            ->assertSessionHas('success_title', 'Stock Received');

        $purchase->refresh();
        $this->assertSame('received', $purchase->status);
        $lot = MaterialLot::where('material_id', $material->id)->firstOrFail();
        $this->assertSame('12.500', $lot->quantity_on_hand);
        $this->assertDatabaseHas('stock_movements', [
            'material_id' => $material->id,
            'material_lot_id' => $lot->id,
            'movement_type' => 'purchase_receipt',
            'quantity' => 12.5,
        ]);

        $this->post('/purchases/'.$purchase->id.'/receive')->assertSessionHasErrors('purchase');
        $this->assertSame(1, StockMovement::where('movement_type', 'purchase_receipt')->count());
    }

    public function test_production_uses_unexpired_lots_and_records_input_and_output_movements(): void
    {
        $user = User::factory()->create();
        $input = Material::create([
            'item_code' => 'RAW-001',
            'name' => 'Raw compound',
            'stock_unit' => 'kg',
            'reorder_level' => 1,
            'is_active' => true,
        ]);
        $output = Material::create([
            'item_code' => 'FIN-001',
            'name' => 'Finished compound',
            'stock_unit' => 'kg',
            'reorder_level' => 1,
            'cost_per_unit' => 25,
            'is_active' => true,
        ]);
        $expired = MaterialLot::create([
            'material_id' => $input->id, 'lot_number' => 'OLD-LOT',
            'quantity_received' => 5, 'quantity_on_hand' => 5,
            'expiry_date' => today()->subDay(), 'status' => 'active',
        ]);
        $available = MaterialLot::create([
            'material_id' => $input->id, 'lot_number' => 'GOOD-LOT',
            'quantity_received' => 4, 'quantity_on_hand' => 4,
            'expiry_date' => today()->addDay(), 'status' => 'active',
        ]);

        $this->actingAs($user)->post('/production', [
            'produced_at' => today()->toDateString(),
            'notes' => 'Test batch',
            'inputs' => [['material_id' => $input->id, 'quantity' => 3]],
            'outputs' => [['material_id' => $output->id, 'quantity' => 2]],
        ])->assertRedirect('/production')
            ->assertSessionHas('success_title', 'Production Completed');

        $this->assertSame('5.000', $expired->fresh()->quantity_on_hand);
        $this->assertSame('1.000', $available->fresh()->quantity_on_hand);
        $this->assertDatabaseHas('stock_movements', [
            'material_id' => $input->id,
            'material_lot_id' => $available->id,
            'movement_type' => 'production_consumption',
            'quantity' => -3,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'material_id' => $output->id,
            'movement_type' => 'production_output',
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('material_lots', [
            'material_id' => $output->id,
            'quantity_on_hand' => 2,
        ]);
    }

    public function test_production_rolls_back_when_unexpired_stock_is_insufficient(): void
    {
        $user = User::factory()->create();
        $material = Material::create([
            'item_code' => 'RAW-002',
            'name' => 'Limited raw stock',
            'stock_unit' => 'kg',
            'reorder_level' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/production', [
            'produced_at' => today()->toDateString(),
            'inputs' => [['material_id' => $material->id, 'quantity' => 1]],
            'outputs' => [['material_id' => $material->id, 'quantity' => 1]],
        ]);

        $response->assertSessionHasErrors('inputs');
        $this->assertDatabaseCount('production_orders', 0);
        $this->assertDatabaseCount('stock_movements', 0);
        $this->assertDatabaseCount('material_lots', 0);
    }
}
