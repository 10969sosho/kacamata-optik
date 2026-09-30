<?php

namespace Tests\Feature;

use App\Models\Accessory;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterItemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@optik.com', 'password' => 'password',
            'role' => 'admin', 'status' => 'active',
        ]);

        $this->staff = User::create([
            'name' => 'Staff', 'email' => 'staff@optik.com', 'password' => 'password',
            'role' => 'staff', 'status' => 'active',
        ]);
    }

    /**
     * @return array{frame: Frame, lens: Lens, softlens: Accessory, softCategory: ProductCategory}
     */
    private function seedItems(): array
    {
        $frame = Frame::create([
            'sku' => 'FR-001', 'name' => 'Ray-Ban Aviator', 'brand' => 'Ray-Ban',
            'sell_price' => 2500000, 'buy_price' => 1200000,
            'stock' => 5, 'min_stock' => 2, 'status' => 'active',
        ]);

        $lensCategory = ProductCategory::create([
            'name' => 'Lensa Single Vision', 'slug' => 'lensa-single-vision', 'type' => 'lens', 'is_active' => true,
        ]);

        $lens = Lens::create([
            'sku' => 'LS-001', 'brand' => 'Essilor', 'name' => 'Crizal Easy 1.56',
            'category_id' => $lensCategory->id, 'lens_type' => 'Single Vision', 'index_val' => '1.56',
            'sell_price' => 1500000, 'buy_price' => 700000, 'stock' => 10, 'min_stock' => 3,
        ]);

        $softCategory = ProductCategory::create([
            'name' => 'Softlens', 'slug' => 'softlens', 'type' => 'accessory', 'is_active' => true,
        ]);

        $caseCategory = ProductCategory::create([
            'name' => 'Case / Wadah', 'slug' => 'case-wadah', 'type' => 'accessory', 'is_active' => true,
        ]);

        $softlens = Accessory::create([
            'sku' => 'SL-001', 'name' => 'Acuvue Moist Daily', 'brand' => 'Acuvue',
            'category_id' => $softCategory->id, 'buy_price' => 120000, 'sell_price' => 225000,
            'stock' => 20, 'min_stock' => 5, 'status' => 'active',
        ]);

        Accessory::create([
            'sku' => 'CS-001', 'name' => 'Case Metal Round', 'brand' => 'Optik',
            'category_id' => $caseCategory->id, 'buy_price' => 25000, 'sell_price' => 65000,
            'stock' => 30, 'min_stock' => 10, 'status' => 'active',
        ]);

        return ['frame' => $frame, 'lens' => $lens, 'softlens' => $softlens, 'softCategory' => $softCategory];
    }

    public function test_master_item_lists_every_item_with_category_tabs(): void
    {
        $this->seedItems();

        $this->actingAs($this->admin)->get(route('items.index'))
            ->assertOk()
            ->assertSee('Master Item')
            ->assertSee('Ray-Ban Aviator')
            ->assertSee('Crizal Easy 1.56')
            ->assertSee('Acuvue Moist Daily')
            ->assertSee('Case Metal Round')
            ->assertSee('Softlens')
            ->assertSee('Case / Wadah')
            ->assertSee('Lensa Single Vision');
    }

    public function test_master_item_filters_by_category_tab(): void
    {
        $items = $this->seedItems();

        $this->actingAs($this->admin)->get(route('items.index', ['tab' => 'cat-'.$items['softCategory']->id]))
            ->assertOk()
            ->assertSee('Acuvue Moist Daily')
            ->assertDontSee('Ray-Ban Aviator')
            ->assertDontSee('Crizal Easy 1.56');

        $this->actingAs($this->admin)->get(route('items.index', ['tab' => 'frame']))
            ->assertOk()
            ->assertSee('Ray-Ban Aviator')
            ->assertDontSee('Acuvue Moist Daily');
    }

    public function test_master_item_search_filters_items(): void
    {
        $this->seedItems();

        $this->actingAs($this->admin)->get(route('items.index', ['q' => 'Aviator']))
            ->assertOk()
            ->assertSee('Ray-Ban Aviator')
            ->assertDontSee('Case Metal Round');
    }

    public function test_accessory_crud_from_master_item(): void
    {
        $category = ProductCategory::create([
            'name' => 'Softlens', 'slug' => 'softlens', 'type' => 'accessory', 'is_active' => true,
        ]);

        $this->actingAs($this->admin)->post(route('items.accessories.store'), [
            'sku' => 'SL-NEW', 'name' => 'Air Optix Plus', 'brand' => 'Alcon',
            'category_id' => $category->id, 'buy_price' => 150000, 'sell_price' => 285000,
            'stock' => 12, 'min_stock' => 5, 'status' => 'active',
        ])->assertRedirect(route('items.index', ['tab' => 'cat-'.$category->id]));

        $accessory = Accessory::where('sku', 'SL-NEW')->firstOrFail();
        $this->assertDatabaseHas('accessories', ['sku' => 'SL-NEW', 'brand' => 'Alcon']);

        $this->actingAs($this->admin)->put(route('items.accessories.update', $accessory), [
            'sku' => 'SL-NEW', 'name' => 'Air Optix Plus Hydraglyde', 'brand' => 'Alcon',
            'category_id' => $category->id, 'buy_price' => 150000, 'sell_price' => 310000,
            'stock' => 10, 'min_stock' => 5, 'status' => 'active',
        ])->assertRedirect(route('items.index', ['tab' => 'cat-'.$category->id]));

        $this->assertSame('Air Optix Plus Hydraglyde', $accessory->fresh()->name);

        $this->actingAs($this->admin)->get(route('items.accessories.edit', $accessory))->assertOk();

        $this->actingAs($this->admin)->delete(route('items.accessories.destroy', $accessory))
            ->assertRedirect(route('items.index'));

        $this->assertDatabaseMissing('accessories', ['id' => $accessory->id]);
    }

    public function test_accessory_requires_category(): void
    {
        $this->actingAs($this->admin)->post(route('items.accessories.store'), [
            'sku' => 'SL-NO-CAT', 'name' => 'Tanpa Kategori',
            'buy_price' => 1000, 'sell_price' => 2000,
            'stock' => 1, 'min_stock' => 1, 'status' => 'active',
        ])->assertSessionHasErrors('category_id');

        $this->assertDatabaseMissing('accessories', ['sku' => 'SL-NO-CAT']);
    }

    public function test_staff_cannot_access_master_item(): void
    {
        $this->seedItems();

        $this->actingAs($this->staff)->get(route('items.index'))->assertForbidden();
        $this->actingAs($this->staff)->get(route('items.accessories.create'))->assertForbidden();
    }
}
