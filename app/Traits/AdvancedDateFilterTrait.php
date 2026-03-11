<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use InvalidArgumentException;

trait AdvancedDateFilterTrait
{
    /**
     * Apply advanced date filters (day, week, month, year with options like today, last_week, etc.)
     *
     * @param Builder|QueryBuilder $query
     * @param array $filters
     * @param string $dateColumn
     * @return void
     */
    protected function applyDateFilters($query, array $filters, string $dateColumn = 'created_at'): void
    {
        $type = $filters['filter_type'] ?? null;
        $option = $filters['filter_option'] ?? null;
        $startDate = $filters['from_date'] ?? $filters['start_date'] ?? null;
        $endDate = $filters['to_date'] ?? $filters['end_date'] ?? null;

        if (!$type || !$option) {
            if ($startDate && $endDate) {
                $query->whereBetween($dateColumn, [
                    Carbon::parse($startDate)->startOfDay(),
                    Carbon::parse($endDate)->endOfDay()
                ]);
            } elseif ($startDate) {
                $query->where($dateColumn, '>=', Carbon::parse($startDate)->startOfDay());
            }
            return;
        }

        $start = null;
        $end = null;
        $now = Carbon::now();

        switch ($type) {
            case 'day':
                switch ($option) {
                    case 'today':
                        $start = $now->copy()->startOfDay();
                        $end = $now->copy()->endOfDay();
                        break;
                    case 'yesterday':
                        $start = $now->copy()->subDay()->startOfDay();
                        $end = $now->copy()->subDay()->endOfDay();
                        break;
                    case 'before_yesterday':
                        $start = $now->copy()->subDays(2)->startOfDay();
                        $end = $now->copy()->subDays(2)->endOfDay();
                        break;
                    case 'custom':
                        if ($startDate) {
                            $start = Carbon::parse($startDate)->startOfDay();
                            $end = Carbon::parse($startDate)->endOfDay();
                        }
                        break;
                }
                break;

            case 'week':
                switch ($option) {
                    case 'this_week':
                        $start = $now->copy()->startOfWeek(Carbon::MONDAY);
                        $end = $now->copy()->endOfWeek(Carbon::SUNDAY);
                        break;
                    case 'last_week':
                        $start = $now->copy()->subWeek()->startOfWeek(Carbon::MONDAY);
                        $end = $now->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);
                        break;
                    case 'last_2_weeks':
                        $start = $now->copy()->subWeeks(2)->startOfWeek(Carbon::MONDAY);
                        $end = $now->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);
                        break;
                    case 'custom':
                        if ($startDate && $endDate) {
                            $start = Carbon::parse($startDate)->startOfDay();
                            $end = Carbon::parse($endDate)->endOfDay();
                        }
                        break;
                }
                break;

            case 'month':
                switch ($option) {
                    case 'this_month':
                        $start = $now->copy()->startOfMonth();
                        $end = $now->copy()->endOfMonth();
                        break;
                    case 'last_month':
                        $start = $now->copy()->subMonth()->startOfMonth();
                        $end = $now->copy()->subMonth()->endOfMonth();
                        break;
                    case 'last_3_months':
                        $start = $now->copy()->subMonths(2)->startOfMonth();
                        $end = $now->copy()->endOfMonth();
                        break;
                    case 'custom':
                        if ($startDate && $endDate) {
                            if ($startDate > $endDate) {
                                throw new InvalidArgumentException('Start month cannot be after end month.');
                            }
                            $start = Carbon::parse($startDate . "-01")->startOfMonth();
                            $end = Carbon::parse($endDate . "-01")->endOfMonth();
                        }
                        break;
                }
                break;

            case 'year':
                switch ($option) {
                    case 'this_year':
                        $start = $now->copy()->startOfYear();
                        $end = $now->copy()->endOfYear();
                        break;
                    case 'last_year':
                        $start = $now->copy()->subYear()->startOfYear();
                        $end = $now->copy()->subYear()->endOfYear();
                        break;
                    case 'last_3_years':
                        $start = $now->copy()->subYears(2)->startOfYear();
                        $end = $now->copy()->endOfYear();
                        break;
                    case 'custom':
                        if ($startDate && $endDate) {
                            if ($startDate > $endDate) {
                                throw new InvalidArgumentException('Start year cannot be after end year.');
                            }
                            $start = Carbon::createFromDate($startDate, 1, 1)->startOfDay();
                            $end = Carbon::createFromDate($endDate, 12, 31)->endOfDay();
                        }
                        break;
                }
                break;
        }

        if ($start && $end) {
            $query->whereBetween($dateColumn, [$start, $end]);
        }
    }
}
