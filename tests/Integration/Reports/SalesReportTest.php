<?php

namespace Tests\Integration\Reports;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use App\Jobs\GenerateReport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesExport;

class SalesReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_sales_report_with_date_range()
    {
        // Crear órdenes en diferentes fechas
        $oldOrder = Order::factory()->create([
            'total' => 100,
            'status' => 'completed',
            'created_at' => now()->subMonths(2)
        ]);
        
        $recentOrder1 = Order::factory()->create([
            'total' => 200,
            'status' => 'completed',
            'created_at' => now()->subDays(5)
        ]);
        
        $recentOrder2 = Order::factory()->create([
            'total' => 150,
            'status' => 'completed',
            'created_at' => now()->subDays(3)
        ]);
        
        $pendingOrder = Order::factory()->create([
            'total' => 300,
            'status' => 'pending',
            'created_at' => now()->subDays(1)
        ]);
        
        // Generar reporte de últimos 7 días
        $response = $this->getJson('/api/reports/sales?from=' . now()->subDays(7)->format('Y-m-d') . '&to=' . now()->format('Y-m-d'));
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'total_sales',
                'total_orders',
                'average_order_value',
                'orders' => [
                    '*' => ['id', 'total', 'status', 'created_at']
                ]
            ]
        ]);
        
        // Verificar cálculos
        $data = $response->json('data');
        $this->assertEquals(350, $data['total_sales']); // 200 + 150 (solo completed)
        $this->assertEquals(2, $data['total_orders']);
        $this->assertEquals(175, $data['average_order_value']); // 350 / 2
    }

    public function test_export_report_to_excel()
    {
        Excel::fake();
        
        Order::factory()->count(5)->create(['status' => 'completed']);
        
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/reports/sales/export');
        
        $response->assertStatus(200);
        
        Excel::assertDownloaded('sales-report.xlsx', function (SalesExport $export) {
            return $export->collection()->count() === 5;
        });
    }

    public function test_report_generation_queued_for_large_datasets()
    {
        Queue::fake();
        
        Order::factory()->count(1000)->create();
        
        $user = User::factory()->create(['email' => 'admin@example.com']);
        
        // Solicitar reporte grande
        $response = $this->actingAs($user)->postJson('/api/reports/generate', [
            'type' => 'sales',
            'format' => 'pdf',
            'from' => now()->subYear()->format('Y-m-d'),
            'to' => now()->format('Y-m-d')
        ]);
        
        $response->assertStatus(202); // Accepted
        $response->assertJson([
            'message' => 'Report generation queued. You will receive an email when ready.'
        ]);
        
        // Verificar que se encoló el job
        Queue::assertPushed(GenerateReport::class);
    }

    public function test_dashboard_statistics_integration()
    {
        // Crear datos de prueba
        $products = Product::factory()->count(10)->create();
        $users = User::factory()->count(50)->create();
        
        Order::factory()->count(20)->create([
            'status' => 'completed',
            'total' => 100,
            'created_at' => now()->subDays(rand(1, 30))
        ]);
        
        Order::factory()->count(5)->create([
            'status' => 'pending'
        ]);
        
        // Obtener estadísticas del dashboard
        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->getJson('/api/dashboard/stats');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_revenue',
            'total_orders',
            'pending_orders',
            'total_customers',
            'total_products',
            'recent_orders' => [
                '*' => ['id', 'total', 'status']
            ],
            'top_products' => [
                '*' => ['id', 'name', 'sales_count']
            ],
            'revenue_by_day' => [
                '*' => ['date', 'revenue']
            ]
        ]);
    }
}