<?php

namespace App\Filament\Resources\Shop\OrderResource\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\Shop\OrderResource;
use App\Models\Shop\Order;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        /** @var Order $order */
        $order = $this->getRecord();

        if (true) {
            $this->defaultAction = 'update_status';
        }
    }

    protected function getActions(): array
    {
        return [
            Actions\Action::make('update_status')
                ->modalCancelActionLabel(fn (self $livewire) => $livewire->defaultAction === 'update_status' ? 'Skip' : null)
                ->mountUsing(function (Order $record, Forms\Form $form) {
                    $form->fill([
                        'test' => 'Initial value',
                        'status' => OrderStatus::New->value,
                    ]);
                })
                ->form([
                    Forms\Components\TextInput::make('test')
                        ->default('Initial from field'),
                    Forms\Components\Radio::make('status')
                        ->options(OrderStatus::class)
                        ->enum(OrderStatus::class)
                        ->inline()
                        ->helperText('The status of the order.')
                        ->live(),
                ])
                ->action(function (Order $record, array $data, self $livewire) {
                    $record->update($data);

                    Notification::make()
                        ->title('Source updated')
                        ->success()
                        ->send();

                    $livewire->form->fill([
                        ...$livewire->form->getRawState(),
                        ...$data,
                    ]);
                }),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
