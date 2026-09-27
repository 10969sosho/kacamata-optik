<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Frame;
use App\Models\Lens;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();
        $billable = fn () => Transaction::query()->whereNotIn('status', ['cancelled', 'refunded']);

        $monthQuery = $billable()->whereBetween('transaction_date', [$startOfMonth.' 00:00:00', now()->endOfDay()]);
        $salesToday = (float) $billable()->whereDate('transaction_date', $today)->sum('total_amount');
        $salesMonth = (float) $monthQuery->sum('total_amount');
        $monthCount = $monthQuery->count();

        $repeatIds = Customer::query()
            ->withCount(['transactions as orders_count' => fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded'])])
            ->get()
            ->filter(fn (Customer $c) => $c->orders_count > 1)
            ->pluck('id');

        return view('dashboard', [
            'salesToday' => $salesToday,
            'salesMonth' => $salesMonth,
            'totalTransactions' => $billable()->count(),
            'atv' => $monthCount > 0 ? $salesMonth / $monthCount : 0,
            'totalCustomers' => Customer::count(),
            'newCustomers' => Customer::where('registered_at', '>=', $startOfMonth)->count(),
            'repeatOrders' => $billable()->whereIn('customer_id', $repeatIds)->count(),
            'lowStockFrames' => Frame::whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->limit(6)->get(),
            'lowStockLenses' => Lens::whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->limit(6)->get(),
            'topFrames' => $this->topSellers('frame_id', Frame::class, 'frames'),
            'topLenses' => $this->topSellers('lens_id', Lens::class, 'lenses'),
            'recentTransactions' => Transaction::with(['customer', 'staff'])
                ->latest('transaction_date')
                ->limit(8)
                ->get(),
            'dailyTrend' => $this->dailyTrend($billable),
            'monthlyTrend' => $this->monthlyTrend($billable),
        ]);
    }

    /**
     * Sales per day for the last 14 days.
     *
     * @param  \Closure(): Builder<Transaction>  $billable
     * @return Collection<int, array{label: string, value: float}>
     */
    private function dailyTrend(\Closure $billable): Collection
    {
        $totals = $billable()
            ->where('transaction_date', '>=', now()->subDays(13)->startOfDay())
            ->selectRaw('date(transaction_date) as day, SUM(total_amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(13, 0))->map(function (int $offset) use ($totals) {
            $date = now()->subDays($offset);

            return [
                'label' => $date->format('d/m'),
                'value' => (float) ($totals[$date->toDateString()] ?? 0),
            ];
        })->values();
    }

    /**
     * Sales per month for the last 6 months.
     *
     * @param  \Closure(): Builder<Transaction>  $billable
     * @return Collection<int, array{label: string, value: float}>
     */
    private function monthlyTrend(\Closure $billable): Collection
    {
        $totals = $billable()
            ->where('transaction_date', '>=', now()->subMonths(5)->startOfMonth())
            ->selectRaw('date(transaction_date) as day, SUM(total_amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->groupBy(fn ($value, string $day) => substr($day, 0, 7));

        return collect(range(5, 0))->map(function (int $offset) use ($totals) {
            $month = now()->subMonths($offset);

            return [
                'label' => $month->format('M y'),
                'value' => (float) ($totals->get($month->format('Y-m'))?->sum() ?? 0),
            ];
        })->values();
    }

    /**
     * @param  class-string<Model>  $model
     * @return Collection<int, Model>
     */
    private function topSellers(string $column, string $model, string $table): Collection
    {
        return $model::query()
            ->select("{$table}.*")
            ->selectRaw('COALESCE(SUM(transaction_items.quantity), 0) as sold')
            ->leftJoin('transaction_items', fn ($join) => $join->on("{$table}.id", '=', "transaction_items.{$column}"))
            ->groupBy("{$table}.id")
            ->orderByDesc('sold')
            ->limit(5)
            ->get();
    }
}
