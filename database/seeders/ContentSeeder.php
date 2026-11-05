<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Support\ContentImporter;
use App\Support\DefaultContent;
use Illuminate\Database\Seeder;

/**
 * The content the website shows today, so the live site looks the same on
 * the first day it reads from the CMS.
 *
 * - Page texts and site settings: DefaultContent (the text typed in the
 *   front end's components).
 * - Core services, services, projects, reviews, hero, About photo and
 *   favicon: database/seeders/content/content.json, ported from the front
 *   end's src/lib/mock/data.ts, with its photos in content/media.
 */
class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(ContentImporter $importer): void
    {
        foreach ([Setting::Site, Setting::Hero, Setting::HomePage, Setting::ServicesPage, Setting::ProjectsPage, Setting::ContactPage] as $key) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => DefaultContent::for($key)]);
        }

        $importer->importFile(database_path('seeders/content/content.json'));
    }
}
