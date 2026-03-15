<?php

namespace zennit\ABAC\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use zennit\ABAC\DTO\AccessResult;
use zennit\ABAC\Providers\AbacServiceProvider;
use zennit\ABAC\Tests\Fixtures\Models\Post;
use zennit\ABAC\Tests\Fixtures\Models\User;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [AbacServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('abac.cache.enabled', true);
        $app['config']->set('abac.cache.store', 'array');
        $app['config']->set('abac.cache.include_context', true);
        $app['config']->set('abac.database.actor_additional_attributes', User::class);
        $app['config']->set('abac.monitoring.logging.enabled', false);
        $app['config']->set('abac.monitoring.logging.channel', 'stack');
        $app['config']->set('abac.middleware.actor_method', 'user');
    }

    protected function defineRoutes($router): void
    {
        Route::middleware(['api', 'abac'])->get('/posts/{post:slug}', function (Post $post) {
            return response()->json([
                'ok' => true,
                'post_id' => $post->getKey(),
            ]);
        });

        Route::middleware(['api', 'abac'])->get('/posts', function () {
            $result = request()->attributes->get('abac');
            $count = 0;

            if ($result instanceof AccessResult) {
                $count = $result->query->count();
            }

            return response()->json([
                'ok' => true,
                'count' => $count,
            ]);
        });

        Route::middleware(['api', 'abac'])->withoutScopedBindings()->get('/users/{user:slug}/posts/{post:slug}', function (User $user, Post $post) {
            return response()->json([
                'ok' => true,
                'user_id' => $user->getKey(),
                'post_id' => $post->getKey(),
            ]);
        });

        Route::middleware(['api', 'abac'])->get('/manual-posts/{id}', function () {
            return response()->json(['ok' => true]);
        });
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('_id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('role')->default('user');
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->string('_id')->primary();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('owner_id');
            $table->timestamps();
        });

        $migration = include __DIR__.'/../database/migrations/create_abac_tables.php';
        $migration->up();
    }
}
