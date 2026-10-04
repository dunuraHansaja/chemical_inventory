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
    }

    public function test_material_catalogue_crud_and_authenticated_dashboard_are_available(): void
    {
        $user = User::factory()->create();
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
        ])->assertRedirect();

        $material = Material::where('item_code', 'ETH-001')->firstOrFail();
        $this->assertSame(['Flammable', 'Irritant'], $material->hazard_classes);
        $this->get('/materials/'.$material->id)->assertOk()->assertSee('Ethanol')->assertSee('Flammable, Irritant');

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
            'currency_symbol' => '€',
            'vat_rate' => 21,
        ])->assertRedirect('/settings');

        $this->assertDatabaseHas('inventory_settings', ['key' => 'vat_rate', 'value' => '21']);
        $this->get('/purchases/create')->assertOk()->assertSee('value="21"', false);
        $this->get('/dashboard')->assertOk()->assertSee('EUR');
    }

    public function test_supplier_and_category_records_can_be_created_updated_and_deleted_when_unused(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/suppliers', [
            'supplier_code' => 'SUP-CRUD',
            'name' => 'Initial supplier',
            'is_active' => '1',
        ])->assertRedirect('/suppliers');
        $supplier = Supplier::where('supplier_code', 'SUP-CRUD')->firstOrFail();
        $this->put('/suppliers/'.$supplier->id, [
            'supplier_code' => 'SUP-CRUD',
            'name' => 'Updated supplier',
            'is_active' => '1',
        ])->assertRedirect('/suppliers');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Updated supplier']);
        $this->delete('/suppliers/'.$supplier->id)->assertRedirect('/suppliers');
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);

        $this->post('/categories', ['name' => 'Initial category', 'is_active' => '1'])->assertRedirect('/categories');
        $category = Category::where('name', 'Initial category')->firstOrFail();
        $this->put('/categories/'.$category->id, ['name' => 'Updated category', 'is_active' => '1'])->assertRedirect('/categories');
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Updated category']);
        $this->delete('/categories/'.$category->id)->assertRedirect('/categories');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
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
        ])->assertRedirect('/lots');

        $lot = MaterialLot::where('lot_number', 'BATCH-001')->firstOrFail();
        $this->post('/stock/adjustments', [
            'material_lot_id' => $lot->id,
            'direction' => 'out',
            'quantity' => 2.5,
            'notes' => 'Damaged container',
        ])->assertRedirect('/stock/movements');
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
        ])->assertRedirect('/materials');
        $this->assertDatabaseHas('materials', ['item_code' => 'CSV-001', 'name' => 'Imported chemical']);
        $this->assertDatabaseHas('categories', ['name' => 'Reagents']);

        $this->get('/materials/export')->assertOk()->assertHeader('content-disposition', 'attachment; filename=materials.csv');
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
        ])->assertRedirect();

        $purchase = PurchaseOrder::firstOrFail();
        $this->assertSame('draft', $purchase->status);
        $this->post('/purchases/'.$purchase->id.'/receive')->assertRedirect();

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
        ])->assertRedirect('/production');

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
