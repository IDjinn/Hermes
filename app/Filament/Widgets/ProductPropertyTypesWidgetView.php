<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductResource;
use App\Models\Category;
use App\Models\ProductType;
use App\Models\PropertyTypes;
use App\Models\SubCategory;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ProductPropertyTypesWidgetView extends BaseWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                PropertyTypes::query()
            )
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->form([
                        TextInput::make('name')->required()->rules(['uppercase']),
                        TextInput::make('value')->json()->nullable(),
                        Select::make('product_type_id')
                            ->searchable()
                            ->options(ProductResource::getProductTypeOptions())
                            ->createOptionForm([
                                TextInput::make('product_type')
                                    ->ascii()
                                    ->unique(table: ProductType::class, column: 'name')
                                    ->rules(['uppercase'])
                                    ->required()
                            ])
                            ->createOptionUsing(function (array $data) {
                                if (isset($data['product_type'])) {
                                    $new_product_type = ProductType::query()->create(['name' => $data['product_type']]);
                                    Notification::make('create_product_type_ok')->title('Product type created successfully!')->success()->send();
                                    return $new_product_type->id;
                                }

                                Notification::make('create_product_type_error')
                                    ->title('Error while creating a product type')
                                    ->send();
                                return null;
                            }),
                        Select::make('category_id')
                            ->id('category_id')
                            ->searchable()
                            ->options(ProductResource::getCategoryOptions())
                            ->createOptionForm([TextInput::make('category')->required()])
                            ->createOptionUsing(function (array $data) {
                                if (isset($data['category'])) {
                                    $new_category = Category::query()->create(['name' => $data['category']]);
                                    Notification::make('create_category_ok')->title('Product category created successfully!')->success()->send();
                                    return $new_category->id;
                                }

                                Notification::make('create_category_error')
                                    ->title('Error while creating a product category')
                                    ->send();
                                return null;
                            }),
                        Select::make('sub_category_id')
                            ->searchable()
                            ->options(ProductResource::getSubCategoryOptions())
                            ->createOptionForm([TextInput::make('sub_category')->required()])
                            ->createOptionUsing(function (array $data, $form, Component $component) {
                                $category_picked = null;
                                foreach ($form->getComponents() as $formComponent) {
                                    if ($formComponent->getId() == 'category_id') {
                                        $category_picked = $formComponent->getState();
                                        break;
                                    }
                                }
                                if (is_null($category_picked)) {
                                    Notification::make('missing_category')
                                        ->title('Error while creating a product sub category. You must select first a category.')
                                        ->send();

                                    return null;
                                }

                                if (isset($data['sub_category'])) {
                                    $new_sub_category = SubCategory::query()->create(['name' => $data['sub_category'], "parent_category_id" => $category_picked]);
                                    Notification::make('create_sub_category_ok')->title('Product sub-category created successfully!')->success()->send();
                                    return $new_sub_category->id;
                                }

                                Notification::make('create_sub_category_error')
                                    ->title('Error while creating a product sub category')
                                    ->send();

                                return null;
                            }),
                    ])
            ])
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('product_type.name'),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('sub_category.name'),
                Tables\Columns\TextColumn::make('value'),
            ]);
    }
}
