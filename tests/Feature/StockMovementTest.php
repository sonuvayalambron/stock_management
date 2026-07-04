<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prevents_stock_from_going_below_zero_and_rolls_back()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'is_active' => true,
        ]);

      
        $response = $this->postJson('/api/stock-movements', [
            'product_id' => $product->id,
            'movement_type' => 'sale',
            'quantity' => 10,
            'reference_number' => 'SALE-001',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'insufficient_stock');

        
        $this->assertDatabaseCount('stock_movements', 0);
        
      
        $this->assertEquals(0, $product->getCurrentStock());
    }
}
