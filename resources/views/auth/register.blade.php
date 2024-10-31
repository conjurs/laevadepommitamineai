<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Battleships</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-50">
    <div class="container mx-auto px-4 h-screen flex items-center justify-center">
        <div class="w-full max-w-md">
            <h1 class="text-3xl font-bold text-center mb-8">Register for Battleships</h1>
            
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label for="name" class="block text-sm font-medium text-zinc-300">Name</label>
                    <input type="text" name="name" id="name" required 
                           class="mt-1 block w-full rounded-md bg-zinc-900 border border-zinc-700 text-zinc-100 px-4 py-2">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-300">Email</label>
                    <input type="email" name="email" id="email" required 
                           class="mt-1 block w-full rounded-md bg-zinc-900 border border-zinc-700 text-zinc-100 px-4 py-2">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-zinc-300">Password</label>
                    <input type="password" name="password" id="password" required 
                           class="mt-1 block w-full rounded-md bg-zinc-900 border border-zinc-700 text-zinc-100 px-4 py-2">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-zinc-300">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required 
                           class="mt-1 block w-full rounded-md bg-zinc-900 border border-zinc-700 text-zinc-100 px-4 py-2">
                </div>

                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2 transition-colors">
                        Register
                    </button>
                </div>
            </form>

            <p class="mt-4 text-center text-zinc-400">
                Already have an account? 
                <a href="{{ route('login') }}" class="text-blue-500 hover:text-blue-400">Login</a>
            </p>
        </div>
    </div>
</body>
</html> 