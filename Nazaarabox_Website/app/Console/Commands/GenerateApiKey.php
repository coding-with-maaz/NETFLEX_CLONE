<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiKey;
use Carbon\Carbon;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-key 
                            {name : Name/description for the API key}
                            {--expires= : Expiration date (Y-m-d format, optional)}
                            {--ips= : Comma-separated list of allowed IPs (optional)}
                            {--notes= : Additional notes (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API key for accessing protected APIs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        
        $options = [];
        
        // Parse expiration date if provided
        if ($this->option('expires')) {
            try {
                $options['expires_at'] = Carbon::parse($this->option('expires'));
                $this->info("API key will expire on: {$options['expires_at']->format('Y-m-d H:i:s')}");
            } catch (\Exception $e) {
                $this->error("Invalid expiration date format. Use Y-m-d format (e.g., 2025-12-31)");
                return 1;
            }
        }
        
        // Parse IP whitelist if provided
        if ($this->option('ips')) {
            $ips = array_map('trim', explode(',', $this->option('ips')));
            $options['allowed_ips'] = $ips;
            $this->info("IP restrictions: " . implode(', ', $ips));
        }
        
        // Parse notes if provided
        if ($this->option('notes')) {
            $options['notes'] = $this->option('notes');
        }
        
        // Generate the API key
        $result = ApiKey::generate($name, $options);
        
        $this->info('');
        $this->info('✅ API Key Generated Successfully!');
        $this->info('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->line('⚠️  IMPORTANT: Save this key now. It will NOT be shown again!');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->info('');
        $this->line("Key ID: {$result['apiKey']->id}");
        $this->line("Name: {$result['apiKey']->name}");
        $this->line("Prefix: {$result['apiKey']->key_prefix}");
        $this->line("API Key: <fg=green;options=bold>{$result['key']}</>");
        $this->info('');
        $this->line('═══════════════════════════════════════════════════════════');
        $this->info('');
        $this->line('Usage in headers:');
        $this->line('  X-API-Key: ' . $result['key']);
        $this->info('');
        $this->line('Or in query parameter:');
        $this->line('  ?api_key=' . $result['key']);
        $this->info('');
        
        return 0;
    }
}

