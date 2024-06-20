<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid as ComponentsGrid;
use Filament\Forms\Components\Group as ComponentsGroup;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Carbon;
use Filament\Notifications\Notification;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Grid;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('User')
                ->description('Form user data')
                ->schema([
                    Fieldset::make('User data')
                        ->schema([
                            ComponentsGrid::make()
                                ->schema([
                                    Section::make()
                                        ->columns([
                                            'sm' => 3,
                                            'xl' => 6,
                                            '2xl' => 12,
                                        ])
                                        ->schema([
                                            Forms\Components\TextInput::make('name')
                                                ->required()
                                                ->maxLength(100)
                                                ->minLength(3)
                                                ->columnSpan([
                                                    'sm' => 2,
                                                    'xl' => 3,
                                                    '2xl' => 12,
                                                ]),
                                            Forms\Components\TextInput::make('email')
                                                ->email()
                                                ->unique(ignoreRecord: true)
                                                ->required()
                                                ->maxLength(100)
                                                ->columnSpan([
                                                    'sm' => 2,
                                                    'xl' => 3,
                                                    '2xl' => 12,
                                                ]),
                                            Forms\Components\DateTimePicker::make('email_verified_at')
                                                ->label('Email Verified At')
                                                ->columnSpan([
                                                    'sm' => 2,
                                                    'xl' => 3,
                                                    '2xl' => 12,
                                                ]),
                                        ])->columnSpan(2),
                                    Section::make()
                                        ->columns([
                                            'sm' => 3,
                                            'xl' => 6,
                                            '2xl' => 1,
                                        ])
                                        ->schema([
                                            Forms\Components\FileUpload::make('avatar_url')
                                                ->label('Photo profile')
                                                ->image()
                                                ->imageEditor()
                                                ->imageEditorAspectRatios([
                                                    '16:9',
                                                    '4:3',
                                                    '1:1',
                                                ])
                                                ->imageEditorMode(2)
                                                ->uploadingMessage('Uploading your photo...')
                                                ->minSize(50)
                                                ->maxSize(3072)
                                        ])->columnSpan(1),
                                ])
                                ->columns(3),
                        ])->columns(3),
                    Fieldset::make('User auth')
                        ->schema([
                            Forms\Components\TextInput::make('password')
                                ->password()
                                ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                                ->dehydrated(fn (?string $state): bool => filled($state))->required(fn (string $operation): bool => $operation === 'create')->revealable()->minLength(8)->maxLength(12)->confirmed(),
                            Forms\Components\TextInput::make('password_confirmation')
                                ->password()
                                ->dehydrated(false)
                                ->revealable()
                                ->minLength(8)
                                ->maxLength(12),
                            Forms\Components\Select::make('roles')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                        ])->columns(3),
                ])
                ->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([Tables\Columns\TextColumn::make('No')->rowIndex(), Tables\Columns\TextColumn::make('name')->searchable(), Tables\Columns\TextColumn::make('email')->searchable(), Tables\Columns\ImageColumn::make('avatar_url')->label('Profile Photo')->size(60)->rounded()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('email_verified_at')->label('Email Verified At')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true), Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Filter::make('created_at')
                    ->form([DatePicker::make('created_from'), DatePicker::make('created_until')])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['created_from'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))->when($data['created_until'], fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): ?array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Created at ' . Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Created at ' . Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading(fn (User $record) => 'Delete User ' . $record->name . ' ?')
                    ->modalDescription('Are you sure you\'d like to delete this user? This cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('User deleted')
                            ->body('The user has been deleted successfully.')
                    ),
                Tables\Actions\ForceDeleteAction::make()
                    ->modalHeading(fn (User $record) => 'Delete User ' . $record->name . ' Permanently ?')
                    ->modalDescription('Are you sure you\'d like to delete this user? This data will be permanently deleted.')
                    ->modalSubmitActionLabel('Yes, delete permanently')
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('User deleted permanently')
                            ->body('The user has been deleted successfully.')
                    ),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make(), Tables\Actions\ForceDeleteBulkAction::make(), Tables\Actions\RestoreBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make([
                    ComponentsSection::make('User Information')
                        ->schema([
                            Grid::make()
                                ->schema([
                                    Group::make()
                                        ->schema([
                                            TextEntry::make('name')
                                                ->label('User name')
                                                ->icon('heroicon-o-user-circle')
                                                ->iconColor('info')
                                                ->weight(FontWeight::Bold),
                                            TextEntry::make('email')
                                                ->icon('heroicon-o-envelope')
                                                ->iconColor('info')
                                                ->weight(FontWeight::Bold),
                                            TextEntry::make('email_verified_at')
                                                ->icon('heroicon-o-shield-check')
                                                ->placeholder('Email has not been verified')
                                                ->iconColor('info')
                                                ->dateTime()
                                                ->weight(FontWeight::Bold),
                                        ])
                                        ->columnSpan(1),
                                    Group::make()
                                        ->schema([
                                            ImageEntry::make('avatar_url')
                                                ->label('Profile photo ')
                                                ->placeholder('Profile photo has not been uploaded')
                                                ->size(200)
                                                ->square()
                                                ->ring(2)
                                        ])
                                        ->columnSpan(1),
                                ])
                                ->columns(2),
                        ])
                        ->headerActions([
                            Action::make('edit')->action(function (User $record) {
                                return redirect()->route('filament.admin.resources.users.edit', ['record' => $record->id]);
                            }),
                        ]),
                ])->columnSpan([
                    'sm' => 2,
                    'md' => 4,
                    'lg' => 6,
                    'xl' => 7,
                    '2xl' => 8,
                ]),
                Group::make([
                    ComponentsSection::make('Detail Information')
                        ->schema([
                            TextEntry::make('created_at')
                                ->dateTime()
                                ->icon('heroicon-o-user-plus')
                                ->iconColor('success'),
                            TextEntry::make('updated_at')
                                ->dateTime()
                                ->icon('heroicon-o-pencil-square')
                                ->iconColor('primary'),
                            TextEntry::make('deleted_at')
                                ->label('Deleted at')
                                ->placeholder('User is not deleted')
                                ->dateTime()
                                ->icon('heroicon-o-trash')
                                ->iconColor('danger')
                        ])
                        ->grow(false),
                ])->columnSpan([
                    'sm' => 1,
                    'md' => 2,
                    'lg' => 2,
                    'xl' => 3,
                    '2xl' => 4,
                ]),
            ])
            ->columns([
                'sm' => 3,
                'md' => 6,
                'lg' => 8,
                'xl' => 10,
                '2xl' => 12,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
