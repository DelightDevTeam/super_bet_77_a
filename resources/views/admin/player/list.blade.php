@extends('admin_layouts.app')

@section('content')
    <div class="container">
        <h1>Players for Agent ID: {{ $agentId }}</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Balance</th>
                    <!-- Add other relevant columns -->
                </tr>
            </thead>
            <tbody>
                @forelse ($players as $player)
                    <tr>
                        <td>{{ $player->id }}</td>
                        <td>{{ $player->user_name }}</td>
                        <td>{{ $player->name }}</td>
                        <td>{{ $player->email }}</td>
                        <td>{{ $player->phone }}</td>
                        <td>{{ $player->balance }}</td>
                        <!-- Add other relevant columns -->
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">No players found for this agent.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
