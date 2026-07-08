<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\PhpDoc;
use Dedoc\Scramble\Support\RouteInfo;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::resolveTagsUsing(function (RouteInfo $routeInfo): array {
            $className = $routeInfo->className();

            if (! $className) {
                return [];
            }

            $reflection = $routeInfo->reflectionMethod()?->getDeclaringClass();
            $docComment = $reflection?->getDocComment();

            if ($docComment) {
                $phpDoc = PhpDoc::parse($docComment);
                $phpDocTags = $phpDoc->getTagsByName('@tags');

                if (count($phpDocTags)) {
                    $rawTags = trim((string) array_values($phpDocTags)[0]->value->value);

                    return array_values(array_filter(array_map('trim', explode(',', $rawTags))));
                }
            }

            return [class_basename($className)];
        });
    }
}
