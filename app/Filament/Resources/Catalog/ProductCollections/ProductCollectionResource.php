<?php

namespace App\Filament\Resources\Catalog\ProductCollections;

use App\Enums\PublishStatus;
use App\Filament\Resources\Catalog\ProductCollections\Pages\ManageProductCollections;
use App\Models\Catalog\ProductCollection;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductCollectionResource extends Resource
{
    protected static ?string $model = ProductCollection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(PublishStatus::class)
                    ->default(PublishStatus::Draft)
                    ->disabled()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    // ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PublishStatus::class)
            ],layout: FiltersLayout::Modal)
            ->recordActions([
                EditAction::make()->visible(fn(ProductCollection $record) => $record->status == PublishStatus::Draft),

                ActionGroup::make([

                    Action::make("publish")
                        ->visible(fn(ProductCollection $record): bool => in_array($record->status, [PublishStatus::Draft]))

                        ->icon(Heroicon::PaperAirplane)
                        ->requiresConfirmation()
                        ->action(function (ProductCollection $record) {
                            $record->publish();
                            $record->save();
                        }),


                    Action::make("archive")
                        ->visible(fn(ProductCollection $record): bool => in_array($record->status, [PublishStatus::Published]))
                        ->icon(Heroicon::ArchiveBox)
                        ->requiresConfirmation()
                        ->action(function (ProductCollection $record) {
                            $record->archive();
                            $record->save();
                        }),
                    Action::make("unarchive")
                        ->visible(fn(ProductCollection $record): bool => in_array($record->status, [PublishStatus::Archived]))
                        ->icon(Heroicon::OutlinedArchiveBoxXMark)
                        ->requiresConfirmation()
                        ->action(function (ProductCollection $record) {
                            $record->unarchive();
                            $record->save();
                        }),

                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProductCollections::route('/'),
        ];
    }
}
