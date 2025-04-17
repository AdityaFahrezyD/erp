<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\Storage;
use Livewire\TemporaryUploadedFile;
use Illuminate\Support\Facades\Auth;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(table: User::class, column: 'email', ignoreRecord: true),

                TextInput::make('first_name')
                    ->required()
                    ->label('Nama Depan'),

                TextInput::make('last_name')
                    ->required()
                    ->label('Nama Belakang'),

                TextInput::make('password')
                    ->password()
                    ->required()
                    ->label('Kata Sandi')
                    ->confirmed(),

                TextInput::make('password_confirmation')
                    ->password()
                    ->required()
                    ->dehydrated(false)
                    ->label('Konfirmasi Kata Sandi'),

                FileUpload::make('image')
                    ->label('Foto')
                    ->image()
                    ->disk('public')
                    ->directory('user-images')
                    ->required(),



                Select::make('role')
                    ->label('User Role')
                    ->required()
                    ->options([
                        'staff' => 'Staff',
                        'finance' => 'Finance',
                        //'user' => 'User',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email'),
                TextColumn::make('first_name')->label('Nama Depan'),
                TextColumn::make('last_name')->label('Nama Belakang'),
                TextColumn::make('role'),
                ImageColumn::make('image')->label('Foto')
                ->width(50)
                ->height(50)
                ->circular(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['admin', 'owner']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return $user && in_array($user->role, ['admin', 'owner']);
    }

}
