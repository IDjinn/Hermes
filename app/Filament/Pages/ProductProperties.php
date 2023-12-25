<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ProductPropertiesWidgetView;
use App\Filament\Widgets\ProductPropertyTypesWidgetView;
use Filament\Pages\Page;

class ProductProperties extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.product-properties';

    protected function getHeaderWidgets(): array
    {
        return [ProductPropertyTypesWidgetView::class, ProductPropertiesWidgetView::class];
    }
}
