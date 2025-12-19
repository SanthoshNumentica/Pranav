<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CaseReportResource\Pages;
use App\Models\CaseReport;
use Exception;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Validation\ValidationException;


class CaseReportResource extends Resource
{
    protected static ?string $model = CaseReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('case_id')->visibleOn('view')->disabled(),

                            Select::make('patient_fk_id')->label('Patient')->relationship('patient', 'name')
                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}-{$record->patient_id} ({$record->mobile_no})")
                                ->required()->searchable()->preload(),

                            Select::make('doc_ref_fk_id')->label('Referred Doctor')->relationship('doctor', 'name')
                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->name}-{$record->doctor_id} ({$record->mobile_no})")
                                ->required()->searchable()->preload(),

                            Textarea::make('description')->maxLength(255),
                            Textarea::make('remarks')->maxLength(255),

                            FileUpload::make('documents')->label('Documents')->preserveFilenames()
                                ->directory('case-report-documents')->enableDownload(),

                            Forms\Components\Hidden::make('status')->default('pending'),
                        ]),

                        Repeater::make('items')->relationship('items')->label('Scan Reports')
                            ->schema([
                                Select::make('scan_type_id')->relationship('scanType', 'name')->required()->searchable()->preload(),
                                Select::make('scan_id')->relationship('scan', 'name')->required()->searchable()->preload(),
                                Textarea::make('remarks')->maxLength(255),
                                FileUpload::make('documents')->multiple()->reorderable()->label('Reports')->required()->preserveFilenames()->directory('case-report-documents')
                                    ->enableDownload()->maxSize(102400)
                                    ->dehydrateStateUsing(function ($state) {
                                        foreach ($state as $filePath) {
                                            if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) !== 'dcm') {
                                                throw \Illuminate\Validation\ValidationException::withMessages([
                                                    'documents' => 'Only DICOM (.dcm) files are allowed.',
                                                ]);
                                            }
                                        }
                                        return $state;
                                    })
                            ])
                            ->columns(3)
                            ->createItemButtonLabel('Add Scan')
                            ->saveRelationshipsUsing(function ($state, $record) {
                                $existingItemIds = $record->items()->pluck('id')->toArray();
                                $incomingItemIds = [];
                                $hasDocuments = false;

                                foreach ($state as $itemData) {
                                    if (!empty($itemData['id'])) {
                                        $item = $record->items()->find($itemData['id']);
                                        if ($item) {
                                            $item->update($itemData);
                                            $incomingItemIds[] = $item->id;
                                        }
                                    } else {
                                        $item = $record->items()->create($itemData);
                                        $incomingItemIds[] = $item->id;
                                    }

                                    if (!empty($itemData['documents']) && is_array($itemData['documents']) && count(array_filter($itemData['documents'])) > 0) {
                                        $hasDocuments = true;
                                    }

                                    Log::info('Scan Report Item Synced', [
                                        'case_report_id' => $record->id,
                                        'item' => $itemData,
                                        'user_id' => auth()->id(),
                                    ]);
                                }

                                // Delete removed items
                                $itemsToDelete = array_diff($existingItemIds, $incomingItemIds);
                                if (!empty($itemsToDelete)) {
                                    $record->items()->whereIn('id', $itemsToDelete)->delete();
                                }

                                $record->status = $hasDocuments ? 'closed' : 'pending';
                                $record->save();

                                // Upload report to Orthanc
                                try {
                                    app(\App\Services\OrthancService::class)->uploadCaseReport($record->id);
                                    // $orthancUrl = rtrim(config('services.ohif.url'), '/');
                                    // $shareLink = !empty($record->study_instance_uid)
                                    //     ? $orthancUrl . "/ohif/viewer?hangingprotocolId=mprAnd3DVolumeViewport&StudyInstanceUIDs={$record->study_instance_uid}"
                                    //     : 'Report not uploaded properly in OHIF';

                                    // $whatsAppData = [
                                    //     'reportId'    => $record->case_id,
                                    //     'patientName' => $record->patient->name,
                                    //     'doctorName'  => $record->doctor->name,
                                    //     'reportDate'  => $record->created_at->format('D, d M Y'),
                                    //     'mobile_no'   => $record->doctor->mobile_no,
                                    //     'shareLink'   => $shareLink,
                                    // ];
                                    // $result = app(WhatsAppService::class)->send($whatsAppData, true, 1);
                                } catch (Exception $e) {
                                    Log::error('Report Upload failed', [
                                        'error' => $e->getMessage(),
                                        'record_id' => $record->id,
                                    ]);
                                }

                                Log::info('Updating Scan Report status', [
                                    'case_report_id' => $record->id,
                                    'status_to_set' => $record->status,
                                ]);
                            }),
                    ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('Id')
                    ->state(
                        fn($record, $livewire) => ($livewire->getTablePage() - 1) * $livewire->getTableRecordsPerPage()
                            + $livewire->getTableRecords()->search($record) + 1
                    ),
                Tables\Columns\TextColumn::make('case_id')->label('Scan Report ID'),
                Tables\Columns\TextColumn::make('patient.name')->label('Patient'),
                Tables\Columns\TextColumn::make('patient.mobile_no')->label('Mobile No'),
                Tables\Columns\TextColumn::make('doctor.name')->label('Doctor'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->color(fn($state) => match ($state) {
                        'closed' => 'success',   // Green
                        'pending' => 'danger',   // Red
                        default => 'secondary', // Default gray
                    })
                    ->formatStateUsing(fn($state) => $state === 'closed' ? 'Completed' : ucfirst($state)),

                Tables\Columns\TextColumn::make('created_at')->label('Created At')->date(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->icon('heroicon-o-eye')->label('')->tooltip('view')->color('primary'),
                Tables\Actions\EditAction::make()->icon('heroicon-o-pencil-square')->label('')->tooltip('edit')->color('secondary'),
                Tables\Actions\DeleteAction::make()->icon('heroicon-o-trash')->label('')->tooltip('delete')->color('danger'),
                Tables\Actions\Action::make('sendWhatsApp')->icon('heroicon-o-paper-airplane')->label('')->tooltip('Send Whatsapp')
                    ->requiresConfirmation()->modalHeading('Send WhatsApp')
                    ->modalIcon('heroicon-o-exclamation-triangle')->color('success')
                    ->action(function ($record) {
                        try {
                            if (empty($record->study_instance_uid)) {
                                Notification::make()->danger()->title('Report not uploaded to OHIF yet.')->send();
                                return;
                            }
                            $orthancUrl = rtrim(config('services.ohif.url'), '/');
                            $whatsAppData = [
                                'reportId'    => $record->case_id,
                                'patientName' => $record->patient->name,
                                'doctorName'  => $record->doctor->name,
                                'reportDate'  => $record->created_at->format('D, d M Y'),
                                'mobile_no'   => $record->doctor->mobile_no,
                                'shareLink'   => $orthancUrl . "/ohif/viewer?hangingprotocolId=mprAnd3DVolumeViewport&StudyInstanceUIDs={$record->study_instance_uid}",
                            ];

                            // Call the WhatsApp service to send the message
                            $result = app(WhatsAppService::class)->send($whatsAppData, true, 1);

                            // Decode the API response JSON
                            $apiResponse = json_decode($result['response']['response'] ?? '{}', true);
                            $toNumber = $apiResponse['data']['to'] ?? null;
                            $statusCode   = $apiResponse['data']['status_code'] ?? null;

                            if ($statusCode === 200) {
                                // Show success notification
                                Notification::make()->success()->title('WhatsApp message sent successfully')->body("Message sent to $toNumber")->send();
                            } else {
                                // Show failure notification with API error message
                                Notification::make()->danger()->title('Failed to send WhatsApp message')->body($apiResponse['message_status'] ?? 'Unknown error')->send();
                            }
                        } catch (Exception $e) {
                            // Log the error and show failure notification
                            Log::error('WhatsApp send failed', ['error' => $e->getMessage(), 'record_id' => $record->id]);
                            Notification::make()->danger()->title('Failed to send WhatsApp message')->body($e->getMessage())->send();
                        }
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCaseReports::route('/'),
            'create' => Pages\CreateCaseReport::route('/create'),
            'edit' => Pages\EditCaseReport::route('/{record}/edit'),
            'view' => Pages\ViewCaseReport::route('/{record}/view'),
        ];
    }
}
