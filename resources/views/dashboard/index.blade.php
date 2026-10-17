@extends('layout') @section('content')
<h1>{{ $user->isSuperAdmin() ? 'Super Admin Dashboard' : ucfirst($user->role).' Dashboard' }}</h1>
<p class="muted">Signed in as {{ $user->email }} · Role: {{ $user->role }} @if($user->company) · Company: {{ $user->company->name }} @endif</p>
@if(!$user->isSuperAdmin())
 <section class="panel">
    <h2>Generate Short URL</h2>
    <form method="post" action="{{ route('short-urls.store') }}">
        @csrf
        <label>Original URL<br><input type="url" name="original_url" placeholder="https://example.com/page" required maxlength="2048" style="width:min(700px,95%)"></label>
        <button type="submit">Generate</button>
    </form>
</section>@endif
<section class="panel">
    <h2>Generated Short URLs</h2>
     <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
     <form method="GET" action="{{ route('dashboard') }}">
            <label for="period">Filter by date:</label>

            <select
                name="period"
                id="period"
                onchange="this.form.submit()"
            >
                <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>
                    All Time
                </option>

                <option value="today" {{ $filter === 'today' ? 'selected' : '' }}>
                    Today
                </option>

                <option value="last_week" {{ $filter === 'last_week' ? 'selected' : '' }}>
                    Last Week
                </option>

                <option value="last_month" {{ $filter === 'last_month' ? 'selected' : '' }}>
                    Last Month
                </option>
            </select>
        </form>
      <a
            href="{{ route('short-urls.export') }}"
            style="display:inline-block; padding:8px 14px; background:#0875d1; color:white; text-decoration:none; border-radius:4px;"
        >
            Download CSV
        </a>
        </div>
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>Short URL</th>
                    <th>Original URL</th>
                    <th>Clicks</th>
                    <th>Created by</th>
                    <th>Created on</th>
                    @if($user->isSuperAdmin())
                        <th>Company</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($urls as $url)
                    <tr>
                        <td><a href="{{ $url->short_url }}" target="_blank" rel="noopener">{{ $url->short_url }}</a></td>
                        <td>{{ \Illuminate\Support\Str::limit($url->original_url,70) }}</td>
                        <td>{{ number_format($url->clicks) }}</td>
                        <td>{{ $url->creator->email }}</td>
                        <td>{{ $url->created_at->format('d M Y') }}</td>
                        @if($user->isSuperAdmin())
                            <td>{{ $url->company->name }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No short URLs yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $urls->links() }}
</section>
@if($user->isSuperAdmin())
    <section class="panel">
        <h2>Companies</h2>
        <table>
            <thead>
                <tr>
                    <th>Company</th>
                    <th>Users</th>
                    <th>Total Generated URLs</th>
                    <th>Total Clicks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $company)
                    <tr>
                        <td>{{ $company->name }}</td>
                        <td>{{ $company->users_count }}</td>
                        <td> {{ number_format($company->total_generated_urls) }}</td>
                        <td> {{ number_format($company->total_clicks ?? 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{ $companies->links() }}
    </section>

    <section class="panel">
        <h2>Invite Admin / Create Company</h2>
        <form method="post" action="{{ route('invitations.store') }}">
            @csrf
            <input name="company_name" placeholder="Company name" required />
            <input name="name" placeholder="Admin name" required />
            <input type="email" name="email" placeholder="Admin email" required />
            <input type="hidden" name="role" value="admin" />
            <button>Generate Invitation</button>
        </form>
    </section>

@elseif($user->isAdmin())
    <section class="panel">
        <h2>Invite Team Member</h2>
        <form method="post" action="{{ route('invitations.store') }}">
            @csrf
            <input name="name" placeholder="Name" required />
            <input type="email" name="email" placeholder="Email" required />
            <select name="role">
                <option value="member">Member</option>
                <option value="admin">Admin</option>
            </select />
            <button>Generate Invitation</button />
        </form>

       <p class="muted">
            Invitation links are displayed after creation. Email delivery is intentionally left as a production integration.
        </p>

    </section>

@endif
@endsection
