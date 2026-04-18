<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_export_import_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export-import.index'));
        $response->assertStatus(200);
    }

    public function test_export_download_returns_json_file(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export-import.download'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_export_contains_version_field(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export-import.download'));
        $json     = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('version', $json);
        $this->assertArrayHasKey('exported_at', $json);
        $this->assertArrayHasKey('customers', $json);
    }

    public function test_import_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get(route('export-import.index'));
        $response->assertStatus(200);
    }

    public function test_import_with_valid_json_merge_mode(): void
    {
        $exportData = [
            'version'         => '1.1',
            'exported_at'     => now()->toIso8601String(),
            'customers'       => [
                ['id' => 1, 'name' => 'Importierter Kunde', 'email' => 'import@test.de', 'customer_number' => 'ABCD-1234'],
            ],
            'work_categories' => [],
            'projects'        => [],
            'time_entries'    => [],
            'expenses'        => [],
            'invoices'        => [],
            'invoice_time_entry' => [],
            'invoice_expense' => [],
            'quotes'          => [],
            'quote_features'  => [],
            'contract_templates' => [],
            'contracts'       => [],
        ];

        $file = UploadedFile::fake()->createWithContent(
            'export.json',
            json_encode($exportData)
        );

        $response = $this->actingAs($this->admin)->post(route('export-import.import.post'), [
            'file' => $file,
            'mode' => 'merge',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', ['name' => 'Importierter Kunde']);
    }

    public function test_import_requires_json_file(): void
    {
        $file     = UploadedFile::fake()->create('notjson.txt', 100, 'text/plain');
        $response = $this->actingAs($this->admin)->post(route('export-import.import.post'), [
            'file' => $file,
            'mode' => 'merge',
        ]);
        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_invalid_json(): void
    {
        $file = UploadedFile::fake()->createWithContent('bad.json', 'this is not json');

        $response = $this->actingAs($this->admin)->post(route('export-import.import.post'), [
            'file' => $file,
            'mode' => 'merge',
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_export_requires_authentication(): void
    {
        $response = $this->get(route('export-import.download'));
        $response->assertRedirect(route('login'));
    }
}
