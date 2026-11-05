<?php

namespace App\Console\Commands;

use App\Support\ContentImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Loads API-shaped content (the front end's src/lib/mock/data.ts exported as
 * JSON) into the CMS. `php artisan db:seed` already imports the copy kept in
 * database/seeders/content; use this command for another file.
 */
#[Signature('cms:import {file : JSON file shaped like database/seeders/content/content.json}
    {--draft : Import everything unpublished}')]
#[Description('Import API-shaped content (core services, services, projects, reviews, hero) into the CMS')]
class ImportContentCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ContentImporter $importer): int
    {
        try {
            $counts = $importer->importFile((string) $this->argument('file'), published: ! $this->option('draft'));
        } catch (RuntimeException $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        }

        foreach ($counts as $label => $count) {
            $this->components->twoColumnDetail($label, (string) $count);
        }

        $this->components->info('Import finished.');

        return self::SUCCESS;
    }
}
