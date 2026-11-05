<?php

use App\Support\TurnstileVerifier;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
});

/**
 * The seed file with media paths reduced to file names, for comparing with the API.
 *
 * @return array<string, mixed>
 */
function frontEndContent(): array
{
    $content = json_decode((string) file_get_contents(database_path('seeders/content/content.json')), true);

    array_walk_recursive($content, function (mixed &$value): void {
        if (is_string($value) && str_starts_with($value, 'media/')) {
            $value = basename($value);
        }
    });

    return $content;
}

/**
 * An API response with every media URL reduced to its file name.
 *
 * @return array<mixed>
 */
function apiContent(string $uri): array
{
    $response = test()->getJson($uri)->assertOk()->json();

    array_walk_recursive($response, function (mixed &$value): void {
        if (is_string($value) && str_contains($value, '/content/')) {
            $value = basename($value);
        }
    });

    return $response;
}

test('the seeded api returns exactly the front end content', function () {
    $this->seed(ContentSeeder::class);
    $content = frontEndContent();

    $cardFields = fn (array $category): array => [
        'key' => $category['key'],
        'title' => $category['title'],
        'summary' => $category['summary'],
        'image' => $category['image'],
        'externalUrl' => $category['externalUrl'] ?? null,
        'showOnHome' => $category['showOnHome'] ?? true,
    ];

    expect(array_map($cardFields, apiContent('/api/v1/service-categories')))
        ->toEqual(array_map($cardFields, $content['serviceCategories']))
        ->and(apiContent('/api/v1/service-filters'))->toBe($content['serviceFilters'])
        ->and(apiContent('/api/v1/services'))->toEqual($content['services'])
        ->and(apiContent('/api/v1/project-categories'))->toBe($content['projectCategories'])
        ->and(apiContent('/api/v1/projects'))->toEqual($content['projects'])
        ->and(apiContent('/api/v1/reviews'))->toEqual($content['reviews'])
        ->and(apiContent('/api/v1/settings'))->toEqual($content['settings']);

    foreach ($content['serviceCategories'] as $category) {
        expect(array_intersect_key(apiContent("/api/v1/service-categories/{$category['key']}"), array_flip(['body', 'gallery'])))
            ->toEqual(['body' => $category['body'], 'gallery' => $category['gallery']]);
    }
});

test('the seeded home page uses the front end about photo and the site gets a favicon', function () {
    $this->seed(ContentSeeder::class);

    expect(apiContent('/api/v1/pages/home')['about']['image'])->toEqual(frontEndContent()['aboutImage'])
        ->and(array_keys(apiContent('/api/v1/site')['favicon']['sizes']))->toBe([32, 180, 192, 512]);
});

test('seeding twice updates the same records', function () {
    $this->seed(ContentSeeder::class);
    $this->seed(ContentSeeder::class);

    expect($this->getJson('/api/v1/services')->json())->toHaveCount(17)
        ->and($this->getJson('/api/v1/projects')->json())->toHaveCount(9);
});

test('the import command downloads media given as urls', function () {
    Http::fake([
        'images.example.com/*' => Http::response('jpeg-bytes', 200, ['Content-Type' => 'image/jpeg']),
        TurnstileVerifier::VerifyUrl => Http::response(['success' => true]),
    ]);
    $file = tempnam(sys_get_temp_dir(), 'content').'.json';
    file_put_contents($file, json_encode([
        'projectCategories' => [['key' => 'cafes', 'label' => 'Cafés']],
        'projects' => [[
            'id' => 'cafe',
            'title' => 'Neighborhood Café',
            'location' => 'Westerville, OH',
            'description' => 'A warm, open café.',
            'categories' => ['cafes'],
            'featured' => true,
            'cover' => ['src' => 'https://images.example.com/cover.jpg', 'alt' => 'Counter'],
            'gallery' => [
                ['src' => 'https://images.example.com/cover.jpg', 'alt' => 'Counter'],
                ['src' => 'https://images.example.com/seating.jpg', 'alt' => 'Seating'],
            ],
        ]],
    ]));

    $this->artisan('cms:import', ['file' => $file])->assertSuccessful();

    $project = $this->getJson('/api/v1/projects')->json('0');
    expect($project['title'])->toBe('Neighborhood Café')
        ->and(array_column($project['gallery'], 'alt'))->toBe(['Counter', 'Seating']);
    Storage::disk('public')->assertExists('content/'.sha1('https://images.example.com/seating.jpg').'.jpg');
});

test('the import command reports a missing file', function () {
    $this->artisan('cms:import', ['file' => 'missing.json'])->assertFailed();
});
