<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected static ?string $breadcrumb = "Create User";

    protected static ?string $title = 'Create New User';
    /**
     * getFormActions : custom button action in create form
     *
     * @return array
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Save User')
                ->color('success')
                ->icon('heroicon-o-document-check'),
            $this->getCreateAnotherFormAction()
                ->label('Save & Create Another User')
                ->color('primary')
                ->icon('heroicon-o-document-plus'),
            $this->getCancelFormAction()
                ->label('Cancel')
                ->color('danger')
                ->icon('heroicon-o-x-circle'),
        ];
    }

    /**
     * getRedirectUrl : return index after create new user
     *
     * @return string
     */
    protected function getRedirectUrl(): string
    {
        return $this->previousUrl ?? $this->getResource()::getUrl('index');
    }

    /**
     * getCreatedNotification : setup notification in create form
     *
     * @return Notification
     */
    protected function getCreatedNotification(): ?Notification
    {
        $recipient = auth()->user();
        $record = $this->getRecord();
        // $url = $this->getResource()::getUrl('edit', ['record' => $record->id]);

        $notification = Notification::make()
            ->success()
            ->title('User Created Successfully!')
            ->body('User: ' . $record->name . ' is successfully created by ' . $recipient->name . '.')
            ->actions([
                Action::make('markAsRead')
                    ->icon('heroicon-m-eye')
                    ->iconButton()
                    ->button()
                    // ->url($url) // Provide the correct URL here
                    ->markAsRead(),
                Action::make('markAsUnread')
                    ->button()
                    ->icon('heroicon-m-eye-slash')
                    ->color('danger')
                    ->markAsUnread(),
            ])
            ->sendToDatabase($recipient);

        return $notification;
    }
}
