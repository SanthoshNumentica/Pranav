<?php

namespace App\Filament\Resources\CaseReportResource\Pages;

use App\Filament\Resources\CaseReportResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;

class ViewCaseReport extends ViewRecord
{
    protected static string $resource = CaseReportResource::class;
    protected string $orthancUrl;

    public function __construct()
    {
        $this->orthancUrl = rtrim(config('services.ohif.url'), '/');
    }

    protected function getHeaderActions(): array
    {
        return [
            // View Reports in OHIF
            Action::make('viewReports')
                ->label('OHIF View')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->modalHeading('Report Viewers')
                ->modalWidth('xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalContent(fn() => view(
                    'filament.case-reports.report-viewers',
                    ['record' => $this->record]
                )),

            // Edit button
            Action::make('editRecord')
                ->label('Edit')
                ->icon('heroicon-o-pencil-square')
                ->color('primary')
                ->url(fn() => CaseReportResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
