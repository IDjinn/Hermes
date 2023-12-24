<?php

namespace App\Filament\Imports;

use App\Models\Product;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('product_type')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('category')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('sub_category')
                ->numeric()
                ->rules(['integer']),
            ImportColumn::make('brand')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('model')
                ->requiredMapping()
                ->rules(['required', 'max:200']),
            ImportColumn::make('part_number')
                ->rules(['max:200']),
            ImportColumn::make('datasheet')
                ->rules(['max:255']),
            ImportColumn::make('extra'),
        ];
    }

    public function resolveRecord(): ?Product
    {
        // return Product::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Product();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
