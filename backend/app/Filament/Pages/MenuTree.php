<?php

namespace App\Filament\Pages;

use App\Models\Menu;
use App\Models\NewsPost;
use App\Models\Product;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use SolutionForest\FilamentTree\Pages\TreePage;

class MenuTree extends TreePage
{
    protected static string $model = Menu::class;

    protected static string $accessKey = 'menus';

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-bars-3';

    protected static ?string $navigationLabel = 'Menus';

    protected static int $maxDepth = 2;

    protected static function hasAccess(string $type): bool {
        return auth('admins')->user()?->hasAccess(static::$accessKey.'.'.$type) ?? false;
    }

    // Must stay in sync with the route names in frontend/src/routes/router.ts
    protected static function routeOptions(): array {
        return [
            'home' => 'Home',
            'products' => 'Products (list)',
            'product' => 'Product (single)',
            'authors' => 'Authors',
            'about' => 'About Us',
            'contact' => 'Contact Us',
            'news' => 'News (list)',
            'post' => 'News post (single)',
            'cart' => 'Cart',
        ];
    }

    // Routes with a dynamic ":slug" segment in router.ts — value is looked up by "slug", label shown by "title"
    protected static function routeSlugModels(): array {
        return [
            'product' => Product::class,
            'post' => NewsPost::class,
        ];
    }

    public static function canAccess(): bool {
        return static::hasAccess('view');
    }

    protected function getTreeToolbarActions(): array {
        return [];
    }

    protected function getActions(): array {
        return [
            ...($this->hasCreateAction() ? [$this->getCreateAction()] : []),
        ];
    }

    protected function getFormSchema(): array {
        $can_edit = static::hasAccess('edit');

        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255)
                ->disabled(! $can_edit),
            Select::make('type')
                ->options([
                    'link' => 'External link',
                    'route' => 'Internal route',
                ])
                ->default('route')
                ->required()
                ->live()
                ->disabled(! $can_edit),
            TextInput::make('route')
                ->label('URL')
                ->maxLength(255)
                ->visible(fn ($get) => $get('type') === 'link')
                ->dehydrated(fn ($get) => $get('type') === 'link')
                ->disabled(! $can_edit),
            Select::make('route')
                ->label('Route')
                ->options(static::routeOptions())
                ->visible(fn ($get) => $get('type') === 'route')
                ->dehydrated(fn ($get) => $get('type') === 'route')
                ->live()
                ->disabled(! $can_edit),
            Select::make('params.slug')
                ->label('Target')
                ->searchable()
                ->getSearchResultsUsing(function (string $search, $get) {
                    $model = static::routeSlugModels()[$get('route')] ?? null;

                    if (! $model) {
                        return [];
                    }

                    return $model::query()
                        ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
                        ->latest('id')
                        ->limit(10)
                        ->pluck('title', 'slug');
                })
                ->getOptionLabelUsing(function ($value, $get) {
                    $model = static::routeSlugModels()[$get('route')] ?? null;

                    return $model ? $model::query()->where('slug', $value)->value('title') : null;
                })
                ->visible(fn ($get) => $get('type') === 'route' && $get('route') && array_key_exists($get('route'), static::routeSlugModels()))
                ->dehydrated(fn ($get) => $get('type') === 'route' && $get('route') && array_key_exists($get('route'), static::routeSlugModels()))
                ->disabled(! $can_edit),
            Select::make('location')
                ->options([
                    'header' => 'Header',
                    'footer' => 'Footer',
                ])
                ->default('header')
                ->required()
                ->disabled(! $can_edit),
        ];
    }

    protected function hasCreateAction(): bool {
        return static::hasAccess('edit');
    }

    protected function hasDeleteAction(): bool {
        return static::hasAccess('edit');
    }

    protected function hasEditAction(): bool {
        return true;
    }

    protected function hasViewAction(): bool {
        return false;
    }

    protected function getHeaderWidgets(): array {
        return [];
    }

    protected function getFooterWidgets(): array {
        return [];
    }
}
