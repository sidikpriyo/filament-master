<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
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
                ->icon('heroicon-o-trash'),
            Actions\ForceDeleteAction::make()
                ->icon('heroicon-o-trash'),
            Actions\RestoreAction::make()
                ->icon('heroicon-o-arrow-left-start-on-rectangle')->color('info'),
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
            ->title('Update User Successfully!')
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
