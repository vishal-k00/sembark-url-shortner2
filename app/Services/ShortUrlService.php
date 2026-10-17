<?php
namespace App\Services;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class ShortUrlService {
 public function create(User $user, string $originalUrl): ShortUrl {
  if ($user->isSuperAdmin() || !$user->company_id) { throw new \DomainException('Only company Admins and Members can create short URLs.'); }
  return DB::transaction(function () use ($user,$originalUrl) {
   do { $code=Str::random(8); } while (ShortUrl::where('code',$code)->exists());
   return ShortUrl::create(['company_id'=>$user->company_id,'created_by'=>$user->id,'code'=>$code,'original_url'=>$originalUrl,'clicks'=>0]);
  });
 }
}
