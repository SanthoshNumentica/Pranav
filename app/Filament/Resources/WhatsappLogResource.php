<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WhatsappLogResource\Pages;
use App\Models\WhatsappLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WhatsappLogResource extends Resource
{
    protected static ?string $model = WhatsappLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-phone';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]); // No form schema needed
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) =>
                $query->orderbYdESC('updated_at')
            )
            ->columns([
                Tables\Columns\TextColumn::make('row_number')
                    ->label('Id')
                    ->state(function ($record, $rowLoop, $livewire) {
                        return ($livewire->getTablePage() - 1)
                            * $livewire->getTableRecordsPerPage()
                            + $rowLoop->iteration;
                    }),
                Tables\Columns\TextColumn::make('sender_mobile_no')->default('N/A'),
                Tables\Columns\TextColumn::make('recipient_mobile_no')->default('N/A'),
                Tables\Columns\TextColumn::make('message_type')->label('Message Type'),
                Tables\Columns\TextColumn::make('message')->limit(50),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label("Message Dat")
                    ->dateTime('d-m-Y H:i')
                    ->searchable(),
            ])
            ->filters([
                // Add filters if needed
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->icon('heroicon-o-eye'),
                Tables\Actions\Action::make('resend')
                    ->label('Resend')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn(WhatsappLog $record) => $record->update(['status' => 'resent'])),
            ]) // No row actions
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]), // No bulk actions
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
