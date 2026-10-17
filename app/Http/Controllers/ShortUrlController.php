<?php
namespace App\Http\Controllers;
use App\Models\ShortUrl;
use App\Services\ShortUrlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ShortUrlController extends Controller {


 public function store(Request $request, ShortUrlService $service) {
  abort_if($request->user()->isSuperAdmin(),403,'SuperAdmin cannot create short URLs.');
  $data=$request->validate(['original_url'=>['required','url','max:2048']]);
  $short=$service->create($request->user(),$data['original_url']);
  return redirect()->route('dashboard')->with('success','Short URL created: '. $short->short_url);
 }

   public function export(Request $request)
    {
        $user = $request->user();

        $query = DB::table('short_urls')
            ->join('users', 'users.id', '=', 'short_urls.created_by')
            ->join('companies', 'companies.id', '=', 'short_urls.company_id')
            ->select([
                'companies.name as company_name',
                'users.name as creator_name',
                'users.email as creator_email',
                'short_urls.code',
                'short_urls.original_url',
                'short_urls.clicks',
                'short_urls.created_at',
            ])
            ->orderBy('short_urls.id');

        // Admin: URLs belonging to their company.
        // Member: only URLs they personally created.
        // SuperAdmin: URLs from every company.
        if (!$user->isSuperAdmin()) {
            $query->where(
                'short_urls.company_id',
                $user->company_id
            );

            if ($user->isMember()) {
                $query->where(
                    'short_urls.created_by',
                    $user->id
                );
            }
        }

        $filename = 'generated-urls-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');

            // Excel-compatible UTF-8 CSV.
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Company',
                'Created By',
                'Email',
                'Short URL',
                'Original URL',
                'Total Clicks',
                'Created At',
            ]);

            // Process in chunks instead of loading all URLs into memory.
            $query->chunk(1000, function ($rows) use ($output) {
                foreach ($rows as $row) {
                    fputcsv($output, [
                        $row->company_name,
                        $row->creator_name,
                        $row->creator_email,
                        url('/' . $row->code),
                        $row->original_url,
                        $row->clicks,
                        date('d M Y', strtotime($row->created_at)),
                    ]);
                }
            });

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

 public function redirect(string $code) {

  $short=ShortUrl::where('code',$code)->firstOrFail();
  ShortUrl::whereKey($short->id)->increment('clicks');
  return redirect()->away($short->original_url,302);
 }
}
