<?php

use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Support\DefaultContent;
use App\Support\FrontendRevalidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'cms.frontend.url' => 'https://site.test',
        'cms.frontend.revalidate_secret' => 'shared-secret',
    ]);
    Http::fake(['site.test/*' => Http::response(['ok' => true])]);
});

test('saving content tells the front end which tags to refresh', function () {
    Service::factory()->create();
    Project::factory()->create();

    app(FrontendRevalidator::class)->flush();

    Http::assertSent(fn (Request $request) => $request->url() === 'https://site.test/api/revalidate'
        && $request->header('x-revalidate-secret') === ['shared-secret']
        && collect($request['tags'])->sort()->values()->all() === ['projects', 'services']);
    Http::assertSentCount(1);
});

test('site settings refresh every tag', function () {
    Setting::query()->create(['key' => Setting::Site, 'value' => DefaultContent::site()]);

    expect(app(FrontendRevalidator::class)->pending())->toBe(config('cms.frontend.tags'));
});

test('nothing is sent when the front end url is not set', function () {
    config(['cms.frontend.url' => null]);
    Service::factory()->create();

    app(FrontendRevalidator::class)->flush();

    Http::assertNothingSent();
});
