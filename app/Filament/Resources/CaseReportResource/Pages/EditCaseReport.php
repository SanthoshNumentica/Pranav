<?php

namespace App\Filament\Resources\CaseReportResource\Pages;

use App\Filament\Resources\CaseReportResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use App\Services\OrthancService;
use Exception;
use Illuminate\Support\Facades\Log;

class EditCaseReport extends EditRecord
{
    protected static string $resource = CaseReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),

            // Re Upload Case Report
            Action::make('uploadReport')->label('OHIF Re-Upload')
                ->icon('heroicon-o-paper-airplane')->color('info')->requiresConfirmation()
                ->modalHeading('Reupload to OHIF Viewer')->modalWidth('xl')
                ->modalContent(fn() => view(
                    'filament.case-reports.upload-report-modal',
                    ['record' => $this->record]
                ))->action(function () {
                    try {
                        $caseId = $this->record->id;

                        $studyUidRes = app(OrthancService::class)->uploadCaseReport($caseId);

                        if ($studyUidRes) {
                            // Show success notification
                            Notification::make()->success()->title('Report upload successfully')->send();
                        } else {
                            // Show failure notification with API error message
                            Notification::make()->danger()->title('Failed to upload report')->body('Unknown error')->send();
                        }
                    } catch (Exception $e) {
                        // Log the error and show failure notification
                        Log::error('Report Upload failed', ['error' => $e->getMessage(), 'record_id' => $this->record->id]);
                        Notification::make()->danger()->title('Failed to upload report')->body($e->getMessage())->send();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $this->record->load('items');
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $hasDocuments = $this->record->items->some(function ($item) {
            return $item->documents && collect($item->documents)->isNotEmpty();
        });

        $data['status'] = $hasDocuments ? 'closed' : 'pending';
        return $data;
    }



    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Case Report has been updated successfully';
    }
}
