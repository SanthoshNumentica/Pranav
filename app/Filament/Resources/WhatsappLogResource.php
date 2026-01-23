<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappLogResource\Pages;
use App\Models\WhatsappLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Services\WhatsAppService;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;



class WhatsappLogResource extends Resource
{
    protected static ?string $model = WhatsappLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('sender_mobile_no')
                                ->label('From')
                                ->disabled(),

                            TextInput::make('recipient_mobile_no')
                                ->label('To')
                                ->disabled(),

                            TextInput::make('message_type')
                                ->label('Message Type')
                                ->disabled(),

                            TextInput::make('status')
                                ->label('Status')
                                ->disabled(),

                            TextInput::make('created_at')
                                ->label('Message Date')
                                ->formatStateUsing(
                                    fn($state) =>
                                    \Carbon\Carbon::parse($state)->format('D, d M Y')
                                )
                                ->disabled(),
                        ]),

                        Textarea::make('message')
                            ->label('Message')
                            ->rows(6)
                            ->columnSpanFull()
                            ->disabled(),
                    ]),
            ]); // No form schema needed
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('row_number')
                    ->label('S.No')
                    ->state(fn($record, $rowLoop) => $rowLoop->iteration),
                Tables\Columns\TextColumn::make('sender_mobile_no')
                    ->label('From'),

                Tables\Columns\TextColumn::make('recipient_mobile_no')
                    ->label('To'),

                Tables\Columns\TextColumn::make('message_type')
                    ->label('Message Type'),

                Tables\Columns\TextColumn::make('message')
                    ->limit(50),

                Tables\Columns\TextColumn::make('status'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Message Date')
                    ->formatStateUsing(
                        fn($state) =>
                        \Carbon\Carbon::parse($state)->format('D, d M Y')
                    ),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->icon('heroicon-o-eye')->label('')->tooltip('view')->color('primary'),
                Tables\Actions\Action::make('resend')
                    ->label('')
                    ->icon('heroicon-o-paper-airplane')
                    ->tooltip('resend')
                    ->requiresConfirmation()->modalHeading('Resend WhatsApp')
                    ->modalIcon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (WhatsappLog $record) {

                        $result = app(WhatsAppService::class)->send([
                            'id' => $record->id, // Resend
                        ]);

                        if ($result['status']) {
                            Notification::make()
                                ->success()
                                ->title('WhatsApp resent successfully')
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('WhatsApp resend failed')
                                ->send();
                        }
                    }),
            ]) // No row actions
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]), // No bulk actions
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWhatsappLogs::route('/'),
            'create' => Pages\CreateWhatsappLog::route('/create'),
            'view' => Pages\ViewWhatsappLogs::route('/{record}'), //  ADD THIS

        ];
    }
}
