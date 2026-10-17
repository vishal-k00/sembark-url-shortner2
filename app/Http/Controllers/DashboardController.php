<?php
namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\ShortUrl;
use Illuminate\Http\Request;
class DashboardController extends Controller {

public function __invoke(Request $request)
{
    $user = $request->user();

    $filter = $request->query('period', 'all');

    $periods = ['all', 'today', 'last_week', 'last_month'];

    if (!in_array($filter, $periods, true)) {
        $filter = 'all';
    }

    $urlsQuery = ShortUrl::query()
        ->with(['creator', 'company'])
        ->when(!$user->isSuperAdmin(), function ($query) use ($user) {
            $query->where('company_id', $user->company_id);

            if ($user->isMember()) {
                $query->where('created_by', $user->id);
            }
        });

    switch ($filter) {
        case 'today':
            $urlsQuery->whereDate('created_at', today());
            break;

        case 'last_week':
            $urlsQuery->where('created_at', '>=', now()->subWeek());
            break;

        case 'last_month':
            $urlsQuery->where('created_at', '>=', now()->subMonth());
            break;
    }

    $urls = $urlsQuery->latest()->paginate(15)->withQueryString();

    $companies = $user->isSuperAdmin()
        ? Company::query()
            ->withCount([
                'users',
                'shortUrls as total_generated_urls',
            ])
            ->withSum('shortUrls as total_clicks', 'clicks')
            ->latest()
            ->paginate(10, ['*'], 'companies_page')
        : null;

    return view('dashboard.index', compact(
        'urls',
        'companies',
        'user',
        'filter'
    ));
}
}
