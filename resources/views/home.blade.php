<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
<nav>
    <p>Welcome, {{ $user->name }}! <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></p>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</nav>

<div class="container">
    <h1>Your Language Tests</h1>

    @if ($tests->isEmpty())
        <p>You haven't taken any tests yet.</p>
    @else
        <table class="table">
            <thead>
            <tr>
                <th>Language</th>
                <th>Level</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($tests as $test)
                <tr>
                    <td>{{ ucfirst($test->language) }}</td>
                    <td>{{ $test->level }}</td>
                    <td>{{ $test->description }}</td>
                    <td>{{ $test->tested_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif

    <h2>Start a New Test</h2>
    <ul>
        @foreach ($languages as $language)
            <li><a href="{{ route('test.show', $language->value) }}">{{ ucfirst($language->value) }}</a></li>
        @endforeach
    </ul>
</div>
</body>
</html>
