<?php
namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class InvitationController extends Controller {



 public function store(Request $request) {
    try{
  $actor=$request->user();
  $rules=['name'=>'required|string|max:120','email'=>'required|email|max:255','role'=>'required|in:admin,member'];
  if ($actor->isSuperAdmin()) $rules['company_name']='required|string|max:150';
  $data=$request->validate($rules);
  if (User::where('email',$data['email'])->exists() || Invitation::where('email',$data['email'])->whereNull('accepted_at')->where('expires_at','>',now())->exists()) return back()->withErrors(['email'=>'This email already belongs to a user or has a pending invitation.'])->withInput();
  if (!$actor->isSuperAdmin()) {
   abort_unless($actor->isAdmin(),403);
   abort_unless($data['role']==='member' || $data['role']==='admin',403);
   $companyId=$actor->company_id;
  } else {
   abort_unless($data['role']==='admin',403,'SuperAdmin may invite only a company Admin.');
   $company=Company::create(['name'=>$data['company_name'],'slug'=>Str::slug($data['company_name']).'-'.Str::lower(Str::random(5))]);
   $companyId=$company->id;
  }
  $invite=Invitation::create(['company_id'=>$companyId,'invited_by'=>$actor->id,'name'=>$data['name'],'email'=>$data['email'],'role'=>$data['role'],'token'=>hash('sha256',Str::random(64)),'expires_at'=>now()->addDays(3)]);
  // For this assignment, expose the one-time acceptance link in the UI; production should send it by email.
  return back()->with('success','Invitation created. Acceptance link: '.route('invitations.accept.form',$invite->token));
    }catch(\Exception $e){
        return back()->withErrors(['error'=>'An error occurred while creating the invitation. Please try again.'])->withInput();
    }
  }



 public function acceptForm(string $token) {
  //  die('This is a placeholder for the invitation acceptance form. In a real application, this would render a view where the invited user can set their password and accept the invitation.');
  try{
 $invite=Invitation::where('token',$token)->firstOrFail();
  abort_unless($invite->isValid(),410,'This invitation has expired or was already accepted.');
  return view('invitations.accept',compact('invite','token'));
  }catch(\Exception $e){
   abort(404,'Invalid invitation token.');
  }
  
 
 }


 public function accept(Request $request,string $token) {
  $data=$request->validate(['password'=>'required|string|min:12|confirmed']);
  return DB::transaction(function () use ($token,$data) {
   $invite =  Invitation::where('token',$token)->lockForUpdate()->firstOrFail();
   abort_unless($invite->isValid(),410,'This invitation has expired or was already accepted.');
   abort_if(User::where('email',$invite->email)->exists(),409,'This email already has an account.');
   User::create(['name'=>$invite->name,'email'=>$invite->email,'password'=>$data['password'],'role'=>$invite->role,'company_id'=>$invite->company_id]);
   $invite->update(['accepted_at'=>now()]);
   return redirect()->route('login')->with('success','Invitation accepted. You can now log in.');
  });
 }
}
