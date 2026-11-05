<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
});

test('a photo is stored as uploaded and its size is returned', function () {
    $response = $this->postJson('/admin/uploads', [
        'kind' => 'image',
        'file' => UploadedFile::fake()->image('kitchen.jpg', 2400, 1600),
    ])->assertOk();

    expect($response->json('width'))->toBe(2400)
        ->and($response->json('url'))->toEndWith($response->json('path'));
    Storage::disk('public')->assertExists($response->json('path'));
});

test('only jpg, png and webp photos up to 10 MB are accepted', function (UploadedFile $file) {
    $this->postJson('/admin/uploads', ['kind' => 'image', 'file' => $file])->assertUnprocessable();
})->with([
    'pdf' => fn () => UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'),
    'too large' => fn () => UploadedFile::fake()->image('huge.jpg')->size(11 * 1024),
]);

test('the favicon must be a square png', function () {
    $this->postJson('/admin/uploads', ['kind' => 'favicon', 'file' => UploadedFile::fake()->image('icon.png', 512, 300)])
        ->assertUnprocessable();

    $this->postJson('/admin/uploads', ['kind' => 'favicon', 'file' => UploadedFile::fake()->image('icon.png', 512, 512)])
        ->assertOk();
});

test('guests cannot upload', function () {
    auth()->logout();

    $this->postJson('/admin/uploads', ['kind' => 'image', 'file' => UploadedFile::fake()->image('a.jpg')])
        ->assertUnauthorized();
});
