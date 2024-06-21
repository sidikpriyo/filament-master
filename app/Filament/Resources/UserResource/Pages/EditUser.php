<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $breadcrumb = "Edit User";

    protected static ?string $title = 'Edit User Data';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->icon('heroicon-o-trash')
                ->modalHeading(fn (User $record) => 'Delete User ' . $record->name . ' ?')
                ->modalDescription('Are you sure you\'d like to delete this user? This data will be moved to trash.')
                ->modalSubmitActionLabel('Yes, delete it')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('User Deleted Successfully!')
                        ->body('The user has been successfully deleted.')
                ),
            Actions\ForceDeleteAction::make()
                ->icon('heroicon-o-trash')
                ->modalHeading(fn (User $record) => 'Delete User ' . $record->name . ' Permanently ?')
                ->modalDescription('Are you sure you\'d like to delete this user? This data will be permanently deleted.')
                ->modalSubmitActionLabel('Yes, delete permanently')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('User Permanently Deleted Successfully!')
                        ->body('The user has been successfully deleted permanently.')
                ),
            Actions\RestoreAction::make()
                ->icon('heroicon-o-arrow-left-start-on-rectangle')
                ->color('info')
                ->modalHeading(fn (User $record) => 'Restore User ' . $record->name . '?')
                ->modalDescription('Are you sure you\'d like to restore this user? This data will be restored in the list.')
                ->modalSubmitActionLabel('Yes, restore')
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('User Restored Successfully!')
                        ->body('The user has been successfully restored.')
                ),
        ];
    }

    /**
     * getFormActions : custom button action in edit form
     *
     * @return array
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save & Update')
                ->color('success')
                ->icon('heroicon-o-document-check'),
            $this->getCancelFormAction()
                ->label('Cancel')
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }

    /**
     * getSavedNotification :setup notification in edit form
     *
     * @return Notification
     */
    protected function getSavedNotification(): ?Notification
    {
        $recipient = auth()->user();

        return Notification::make()
            ->success()
            ->title('User Updated Successfully!')
            ->body('User : ' . $this->getRecord()->name . ' is successfully updated by ' . $recipient->name . '.')
            ->actions([
                Action::make('view')
                    ->label('view')
                    ->icon('heroicon-m-eye')
                    ->iconButton()
                    ->button()
                    ->url(
                        fn (): string => route(
                            'filament.admin.resources.users.edit',
                            ['record' => $this->getRecord()->id]
                        )
                    )
                    ->markAsRead(),
                Action::make('markAsUnread')
                    ->button()
                    ->icon('heroicon-m-eye-slash')
                    ->color('danger')
                    ->markAsUnread(),
            ])
            ->sendToDatabase($recipient);
    }
}
