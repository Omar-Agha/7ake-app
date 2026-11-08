<?php

namespace App\Filament\Resources\Customer\Customers;

use App\Filament\Navigations\NavigationItems;
use App\Filament\Resources\Customer\Customers\Pages\ManageCustomers;
use App\Models\Customer\Customer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static string | UnitEnum | null $navigationGroup = NavigationItems::CUSTOMER_MENU_GROUP;


    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('full_name')
            ->columns([
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ImageColumn::make('avatar')
                    ->defaultImageUrl(url('images/default-avatar.png'))
                    ->circular(),
                TextColumn::make('full_name')
                    ->label('Customer Name')
                    ->state(fn($record) => "{$record->f_name} {$record->l_name}")
                    ->searchable(['f_name', 'l_name']),
                TextColumn::make('phone')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),

            ])
            ->filters([])
            ->recordActions([
                Action::make('viewAddresses')
                    ->label('Addresses')
                    ->tooltip('View addresses')
                    ->iconButton()
                    ->icon(Heroicon::OutlinedMapPin)
                    ->modalHeading('Customer addresses')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(fn(Customer $record) => view(
                        'filament.resources.customer.customers.addresses',
                        ['addresses' => $record->addresses],
                    )),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCustomers::route('/'),
        ];
    }
}
